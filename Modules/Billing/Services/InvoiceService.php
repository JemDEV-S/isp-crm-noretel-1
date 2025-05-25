<?php

namespace Modules\Billing\Services;

use Modules\Billing\Repositories\InvoiceRepository;
use Modules\Contract\Repositories\ContractRepository;
use Modules\Customer\Repositories\CustomerRepository;
use Modules\Billing\Entities\InvoiceItem;
use Modules\Core\Entities\AuditLog;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InvoiceService
{
    /**
     * @var InvoiceRepository
     */
    protected $invoiceRepository;

    /**
     * @var ContractRepository
     */
    protected $contractRepository;

    /**
     * @var CustomerRepository
     */
    protected $customerRepository;

    /**
     * InvoiceService constructor.
     *
     * @param InvoiceRepository $invoiceRepository
     * @param ContractRepository $contractRepository
     * @param CustomerRepository $customerRepository
     */
    public function __construct(
        InvoiceRepository $invoiceRepository,
        ContractRepository $contractRepository,
        CustomerRepository $customerRepository
    ) {
        $this->invoiceRepository = $invoiceRepository;
        $this->contractRepository = $contractRepository;
        $this->customerRepository = $customerRepository;
    }

    /**
     * Crear factura con sus items
     *
     * @param array $data
     * @return array
     */
    public function createInvoice(array $data)
    {
        try {
            DB::beginTransaction();

            // Generar número de factura si no se proporcionó
            if (!isset($data['invoice_number']) || empty($data['invoice_number'])) {
                $data['invoice_number'] = $this->invoiceRepository->generateInvoiceNumber();
            }

            // Calcular fecha de vencimiento si no se proporcionó
            if (!isset($data['due_date']) || empty($data['due_date'])) {
                $dueDays = config('billing.invoice.due_days', 15);
                $data['due_date'] = Carbon::parse($data['issue_date'])->addDays($dueDays);
            }

            // Extraer los items de la factura DESPUÉS de obtener los totales
            $items = $data['items'] ?? [];

            // Asegurar que los totales estén presentes
            if (!isset($data['amount']) || !isset($data['taxes']) || !isset($data['total_amount'])) {
                // Recalcular totales si no están presentes
                $totals = $this->calculateTotals($items);
                $data = array_merge($data, $totals);
            }

            // Ahora remover los items para crear la factura
            unset($data['items']);

            // Establecer valores por defecto para campos requeridos
            $data['status'] = $data['status'] ?? 'pending';
            $data['document_type'] = $data['document_type'] ?? 'invoice';
            $data['sent'] = $data['sent'] ?? false;

            // Crear la factura
            $invoice = $this->invoiceRepository->create($data);

            // Crear los items
            foreach ($items as $item) {
                $item['invoice_id'] = $invoice->id;

                // Asegurar que los campos calculados estén presentes
                if (!isset($item['tax_amount']) || !isset($item['amount'])) {
                    $itemCalculations = $this->calculateItemTotals($item);
                    $item = array_merge($item, $itemCalculations);
                }

                InvoiceItem::create($item);
            }

            // Registrar en log de auditoría
            AuditLog::register(
                auth()->id(),
                'invoice_created',
                'invoices',
                "Factura creada: {$invoice->invoice_number}",
                request()->ip(),
                null,
                $invoice->toArray()
            );

            DB::commit();

            return [
                'success' => true,
                'message' => 'Factura creada correctamente',
                'invoice' => $invoice
            ];
        } catch (\Exception $e) {
            DB::rollback();

            return [
                'success' => false,
                'message' => 'Error al crear la factura: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Calcular totales de la factura
     *
     * @param array $items
     * @return array
     */
    private function calculateTotals(array $items)
    {
        $amount = 0;
        $taxes = 0;
        $total_amount = 0;

        foreach ($items as $item) {
            $quantity = floatval($item['quantity'] ?? 0);
            $unitPrice = floatval($item['unit_price'] ?? 0);
            $taxRate = floatval($item['tax_rate'] ?? 0);
            $discount = floatval($item['discount'] ?? 0);

            // Calcular subtotal (cantidad * precio unitario)
            $subtotal = $quantity * $unitPrice;

            // Aplicar descuento si existe
            $afterDiscount = $subtotal - $discount;

            // Calcular impuesto
            $taxAmount = ($afterDiscount * $taxRate) / 100;

            // Sumar a totales
            $amount += $afterDiscount;
            $taxes += $taxAmount;
            $total_amount += $afterDiscount + $taxAmount;
        }

        return [
            'amount' => round($amount, 2),
            'taxes' => round($taxes, 2),
            'total_amount' => round($total_amount, 2)
        ];
    }

    /**
     * Calcular totales de un item individual
     *
     * @param array $item
     * @return array
     */
    private function calculateItemTotals(array $item)
    {
        $quantity = floatval($item['quantity'] ?? 0);
        $unitPrice = floatval($item['unit_price'] ?? 0);
        $taxRate = floatval($item['tax_rate'] ?? 0);
        $discount = floatval($item['discount'] ?? 0);

        // Calcular subtotal (cantidad * precio unitario)
        $subtotal = $quantity * $unitPrice;

        // Aplicar descuento si existe
        $afterDiscount = $subtotal - $discount;

        // Calcular impuesto
        $taxAmount = ($afterDiscount * $taxRate) / 100;

        // Calcular monto total del ítem
        $amount = $afterDiscount + $taxAmount;

        return [
            'tax_amount' => round($taxAmount, 2),
            'amount' => round($amount, 2)
        ];
    }

    /**
     * Actualizar factura con sus items
     *
     * @param int $invoiceId
     * @param array $data
     * @return array
     */
    public function updateInvoice(int $invoiceId, array $data)
    {
        try {
            DB::beginTransaction();

            $invoice = $this->invoiceRepository->find($invoiceId);

            if (!$invoice) {
                return [
                    'success' => false,
                    'message' => 'Factura no encontrada'
                ];
            }

            // Extraer los items de la factura DESPUÉS de obtener los totales
            $items = $data['items'] ?? [];

            // Asegurar que los totales estén presentes
            if (!isset($data['amount']) || !isset($data['taxes']) || !isset($data['total_amount'])) {
                $totals = $this->calculateTotals($items);
                $data = array_merge($data, $totals);
            }

            // Remover los items para actualizar la factura
            unset($data['items']);

            // Actualizar la factura
            $this->invoiceRepository->update($invoiceId, $data);

            // Eliminar items existentes
            $invoice->items()->delete();

            // Crear los nuevos items
            foreach ($items as $item) {
                $item['invoice_id'] = $invoiceId;

                // Asegurar que los campos calculados estén presentes
                if (!isset($item['tax_amount']) || !isset($item['amount'])) {
                    $itemCalculations = $this->calculateItemTotals($item);
                    $item = array_merge($item, $itemCalculations);
                }

                InvoiceItem::create($item);
            }

            // Recargar la factura actualizada
            $invoice = $this->invoiceRepository->find($invoiceId);

            // Registrar en log de auditoría
            AuditLog::register(
                auth()->id(),
                'invoice_updated',
                'invoices',
                "Factura actualizada: {$invoice->invoice_number}",
                request()->ip(),
                null,
                $invoice->toArray()
            );

            DB::commit();

            return [
                'success' => true,
                'message' => 'Factura actualizada correctamente',
                'invoice' => $invoice
            ];
        } catch (\Exception $e) {
            DB::rollback();

            return [
                'success' => false,
                'message' => 'Error al actualizar la factura: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Generar factura automática para un contrato
     *
     * @param int $contractId
     * @param string $billingPeriod
     * @param Carbon|null $issueDate
     * @return array
     */
    public function generateInvoiceForContract($contractId, $billingPeriod, $issueDate = null)
    {
        try {
            DB::beginTransaction();

            // Obtener el contrato
            $contract = $this->contractRepository->find($contractId);
            if (!$contract) {
                return [
                    'success' => false,
                    'message' => 'Contrato no encontrado'
                ];
            }

            // Verificar si el contrato está activo
            if ($contract->status !== 'active') {
                return [
                    'success' => false,
                    'message' => 'No se puede generar factura para un contrato inactivo'
                ];
            }

            // Establecer fecha de emisión
            if (!$issueDate) {
                $issueDate = Carbon::now();
            }

            // Calcular fecha de vencimiento
            $dueDays = config('billing.invoice.due_days', 15);
            $dueDate = Carbon::parse($issueDate)->addDays($dueDays);

            // Obtener el cliente para datos de facturación
            $customer = $contract->customer;

            // Crear la factura
            $invoiceData = [
                'contract_id' => $contractId,
                'invoice_number' => $this->invoiceRepository->generateInvoiceNumber(),
                'issue_date' => $issueDate->format('Y-m-d'),
                'due_date' => $dueDate->format('Y-m-d'),
                'status' => 'pending',
                'billing_name' => $customer->full_name,
                'billing_address' => $customer->primary_address,
                'billing_document' => $customer->identity_document,
                'billing_email' => $customer->email,
                'generation_type' => 'automatic',
                'billing_period' => $billingPeriod,
                'amount' => 0, // Se calculará con los items
                'taxes' => 0, // Se calculará con los items
                'total_amount' => 0 // Se calculará con los items
            ];

            // Crear la factura
            $invoice = $this->invoiceRepository->create($invoiceData);

            // Preparar los items según el plan y servicios adicionales
            $items = [];
            $subtotal = 0;
            $taxTotal = 0;

            // Agregar el plan base
            $plan = $contract->plan;
            $defaultTaxRate = config('billing.tax.default_rate', 18);

            if ($plan) {
                $planAmount = $plan->price;
                $taxAmount = ($planAmount * $defaultTaxRate) / 100;

                $items[] = [
                    'invoice_id' => $invoice->id,
                    'description' => "Plan {$plan->name}",
                    'quantity' => 1,
                    'unit_price' => $planAmount,
                    'tax_rate' => $defaultTaxRate,
                    'tax_amount' => $taxAmount,
                    'discount' => 0,
                    'amount' => $planAmount + $taxAmount,
                    'item_type' => 'plan',
                    'service_id' => $plan->id,
                    'period_start' => null, // Se puede calcular según el período
                    'period_end' => null // Se puede calcular según el período
                ];

                $subtotal += $planAmount;
                $taxTotal += $taxAmount;
            }

            // Agregar servicios adicionales
            foreach ($contract->contracted_services as $service) {
                $serviceAmount = $service->price;
                $taxAmount = ($serviceAmount * $defaultTaxRate) / 100;

                $additionalService = $service->additional_service;
                $description = $additionalService ? $additionalService->name : 'Servicio adicional';

                $items[] = [
                    'invoice_id' => $invoice->id,
                    'description' => $description,
                    'quantity' => 1,
                    'unit_price' => $serviceAmount,
                    'tax_rate' => $defaultTaxRate,
                    'tax_amount' => $taxAmount,
                    'discount' => 0,
                    'amount' => $serviceAmount + $taxAmount,
                    'item_type' => 'additional_service',
                    'service_id' => $service->additional_service_id,
                    'period_start' => null,
                    'period_end' => null
                ];

                $subtotal += $serviceAmount;
                $taxTotal += $taxAmount;
            }

            // Crear los items
            foreach ($items as $item) {
                InvoiceItem::create($item);
            }

            // Actualizar los totales de la factura
            $invoice->update([
                'amount' => $subtotal,
                'taxes' => $taxTotal,
                'total_amount' => $subtotal + $taxTotal
            ]);

            // Registrar en log de auditoría
            AuditLog::register(
                auth()->id() ?: 1, // Si es automático, usar usuario administrador
                'invoice_generated',
                'invoices',
                "Factura generada automáticamente: {$invoice->invoice_number}",
                request()->ip(),
                null,
                $invoice->toArray()
            );

            DB::commit();

            return [
                'success' => true,
                'message' => 'Factura generada correctamente',
                'invoice' => $invoice
            ];
        } catch (\Exception $e) {
            DB::rollback();

            return [
                'success' => false,
                'message' => 'Error al generar la factura: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Anular una factura
     *
     * @param int $id
     * @param string $reason
     * @return array
     */
    public function voidInvoice($id, $reason)
    {
        try {
            DB::beginTransaction();

            $invoice = $this->invoiceRepository->find($id);

            // Verificar si la factura puede anularse
            if (in_array($invoice->status, ['paid', 'void'])) {
                return [
                    'success' => false,
                    'message' => 'No se puede anular una factura pagada o ya anulada'
                ];
            }

            // Guardar datos anteriores para auditoría
            $oldData = $invoice->toArray();

            // Anular la factura
            $invoice->update([
                'status' => 'void',
                'notes' => $invoice->notes . "\n[" . now() . "] Factura anulada. Motivo: " . $reason
            ]);

            // Registrar en log de auditoría
            AuditLog::register(
                auth()->id(),
                'invoice_voided',
                'invoices',
                "Factura anulada: {$invoice->invoice_number}. Motivo: {$reason}",
                request()->ip(),
                $oldData,
                $invoice->toArray()
            );

            DB::commit();

            return [
                'success' => true,
                'message' => 'Factura anulada correctamente',
                'invoice' => $invoice
            ];
        } catch (\Exception $e) {
            DB::rollback();

            return [
                'success' => false,
                'message' => 'Error al anular la factura: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Marcar una factura como enviada
     *
     * @param int $id
     * @return array
     */
    public function markAsSent($id)
    {
        try {
            $invoice = $this->invoiceRepository->find($id);

            // Verificar si la factura ya está marcada como enviada
            if ($invoice->sent) {
                return [
                    'success' => false,
                    'message' => 'La factura ya está marcada como enviada'
                ];
            }

            // Actualizar la factura
            $invoice->update([
                'sent' => true,
                'sent_at' => now()
            ]);

            return [
                'success' => true,
                'message' => 'Factura marcada como enviada',
                'invoice' => $invoice
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al marcar la factura como enviada: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Generar facturas para contratos que deben facturarse hoy
     *
     * @return array
     */
    public function generateScheduledInvoices()
    {
        // Este método será llamado por un cron job
        $results = [
            'success' => true,
            'generated' => 0,
            'failed' => 0,
            'details' => []
        ];

        // Obtener contratos activos que deben facturarse hoy
        // Esto dependerá de cómo manejes los ciclos de facturación
        $contracts = $this->contractRepository->getContractsForBilling();

        foreach ($contracts as $contract) {
            // Determinar período de facturación
            $month = Carbon::now()->format('F');
            $year = Carbon::now()->format('Y');
            $billingPeriod = "{$month} {$year}";

            // Generar factura
            $result = $this->generateInvoiceForContract(
                $contract->id,
                $billingPeriod
            );

            if ($result['success']) {
                $results['generated']++;
            } else {
                $results['failed']++;
            }

            $results['details'][] = [
                'contract_id' => $contract->id,
                'customer' => $contract->customer->full_name,
                'result' => $result
            ];
        }

        return $results;
    }
}
