<?php

namespace Modules\Billing\Services;

use Modules\Billing\Repositories\PaymentRepository;
use Modules\Billing\Repositories\InvoiceRepository;
use Modules\Core\Entities\AuditLog;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PaymentService
{
    /**
     * @var PaymentRepository
     */
    protected $paymentRepository;

    /**
     * @var InvoiceRepository
     */
    protected $invoiceRepository;

    /**
     * PaymentService constructor.
     *
     * @param PaymentRepository $paymentRepository
     * @param InvoiceRepository $invoiceRepository
     */
    public function __construct(
        PaymentRepository $paymentRepository,
        InvoiceRepository $invoiceRepository
    ) {
        $this->paymentRepository = $paymentRepository;
        $this->invoiceRepository = $invoiceRepository;
    }

    /**
     * Registrar un pago
     *
     * @param array $data
     * @return array
     */
    public function registerPayment(array $data)
    {
        try {
            DB::beginTransaction();

            // Verificar que la factura exista
            $invoice = $this->invoiceRepository->find($data['invoice_id']);
            if (!$invoice) {
                return [
                    'success' => false,
                    'message' => 'Factura no encontrada'
                ];
            }

            // Verificar que la factura no esté anulada o completamente pagada
            if (in_array($invoice->status, ['void', 'paid'])) {
                return [
                    'success' => false,
                    'message' => 'No se puede registrar pago para una factura anulada o completamente pagada'
                ];
            }

            // Establecer usuario que registra el pago
            if (!isset($data['user_id'])) {
                $data['user_id'] = auth()->id();
            }

            // Establecer estado del pago
            if (!isset($data['status'])) {
                $data['status'] = 'completed';
            }

            // Crear el pago y actualizar estado de la factura
            $payment = $this->paymentRepository->createPaymentAndUpdateInvoice($data);

            // Registrar en log de auditoría
            AuditLog::register(
                auth()->id(),
                'payment_registered',
                'payments',
                "Pago registrado para factura: {$invoice->invoice_number}",
                request()->ip(),
                null,
                $payment->toArray()
            );

            DB::commit();

            return [
                'success' => true,
                'message' => 'Pago registrado correctamente',
                'payment' => $payment
            ];
        } catch (\Exception $e) {
            DB::rollback();

            return [
                'success' => false,
                'message' => 'Error al registrar el pago: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Anular un pago
     *
     * @param int $id
     * @param string $reason
     * @return array
     */
    public function voidPayment($id, $reason)
    {
        try {
            DB::beginTransaction();

            $payment = $this->paymentRepository->find($id);

            // Verificar que el pago no esté ya anulado
            if ($payment->status === 'refunded') {
                return [
                    'success' => false,
                    'message' => 'El pago ya está anulado'
                ];
            }

            // Guardar datos anteriores para auditoría
            $oldData = $payment->toArray();

            // Anular el pago
            $payment->update([
                'status' => 'refunded',
                'notes' => ($payment->notes ? $payment->notes . "\n" : '') .
                           "[" . now() . "] Pago anulado. Motivo: " . $reason
            ]);

            // Actualizar el estado de la factura
            $invoice = $payment->invoice;
            $totalPaid = $invoice->payments()->where('status', 'completed')->sum('amount');
            $creditNoteAmount = $invoice->creditNotes()->where('status', 'applied')->sum('amount');

            if ($totalPaid + $creditNoteAmount <= 0) {
                $invoice->update(['status' => 'pending']);
            } else if ($totalPaid + $creditNoteAmount < $invoice->total_amount) {
                $invoice->update(['status' => 'partial']);
            }

            // Registrar en log de auditoría
            AuditLog::register(
                auth()->id(),
                'payment_voided',
                'payments',
                "Pago anulado para factura: {$invoice->invoice_number}. Motivo: {$reason}",
                request()->ip(),
                $oldData,
                $payment->toArray()
            );

            DB::commit();

            return [
                'success' => true,
                'message' => 'Pago anulado correctamente',
                'payment' => $payment
            ];
        } catch (\Exception $e) {
            DB::rollback();

            return [
                'success' => false,
                'message' => 'Error al anular el pago: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Generar reporte de pagos por período
     *
     * @param string $startDate
     * @param string $endDate
     * @param array $filters
     * @return array
     */
    public function getPaymentReport($startDate, $endDate, $filters = [])
    {
        try {
            $query = $this->paymentRepository->query();

            // Aplicar filtro de fechas
            $query->whereBetween('payment_date', [$startDate, $endDate]);

            // Filtro por método de pago
            if (isset($filters['payment_method']) && $filters['payment_method']) {
                $query->where('payment_method', $filters['payment_method']);
            }

            // Filtro por estado
            if (isset($filters['status']) && $filters['status']) {
                $query->where('status', $filters['status']);
            }

            // Filtro por cliente
            if (isset($filters['customer_id']) && $filters['customer_id']) {
                $query->whereHas('invoice.contract', function ($q) use ($filters) {
                    $q->where('customer_id', $filters['customer_id']);
                });
            }

            $payments = $query->get();

            $totalAmount = $payments->where('status', 'completed')->sum('amount');

            // Agrupar por método de pago
            $byMethod = $payments->where('status', 'completed')
                ->groupBy('payment_method')
                ->map(function ($items, $key) {
                    return [
                        'method' => $key,
                        'count' => $items->count(),
                        'amount' => $items->sum('amount')
                    ];
                })->values();

            // Agrupar por día
            $byDate = $payments->where('status', 'completed')
                ->groupBy(function ($item) {
                    return Carbon::parse($item->payment_date)->format('Y-m-d');
                })
                ->map(function ($items, $key) {
                    return [
                        'date' => $key,
                        'count' => $items->count(),
                        'amount' => $items->sum('amount')
                    ];
                })->values();

            return [
                'success' => true,
                'total_payments' => $payments->where('status', 'completed')->count(),
                'total_amount' => $totalAmount,
                'by_method' => $byMethod,
                'by_date' => $byDate,
                'payments' => $payments
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al generar el reporte: ' . $e->getMessage()
            ];
        }
    }
}
