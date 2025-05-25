<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $invoice->invoice_number }} - NorETEL</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: white;
        }
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border: 1px solid #ddd;
        }
        .invoice-header {
            background-color: #2c5282;
            color: white;
            padding: 20px;
        }
        .company-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .company-logo {
            font-size: 24px;
            font-weight: bold;
        }
        .invoice-title {
            text-align: center;
            margin: 20px 0;
        }
        .invoice-details {
            display: flex;
            justify-content: space-between;
            padding: 20px;
            border-bottom: 1px solid #ddd;
        }
        .invoice-to {
            flex: 1;
        }
        .invoice-info {
            flex: 1;
            text-align: right;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .items-table th,
        .items-table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        .items-table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .items-table .text-right {
            text-align: right;
        }
        .items-table .text-center {
            text-align: center;
        }
        .totals {
            margin-top: 20px;
            padding: 0 20px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            margin: 5px 0;
            padding: 5px 0;
        }
        .total-row.grand-total {
            border-top: 2px solid #2c5282;
            font-weight: bold;
            font-size: 1.2em;
            color: #2c5282;
        }
        .footer {
            margin-top: 40px;
            padding: 20px;
            border-top: 1px solid #ddd;
            background-color: #f8f9fa;
        }
        .notes {
            margin: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-left: 4px solid #2c5282;
        }
        .payment-info {
            margin: 20px;
            padding: 15px;
            background-color: #e8f4fd;
            border: 1px solid #bee5eb;
            border-radius: 4px;
        }
        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            .no-print {
                display: none;
            }
        }
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-paid {
            background-color: #d4edda;
            color: #155724;
        }
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-overdue {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Header -->
        <div class="invoice-header">
            <div class="company-info">
                <div class="company-logo">NorETEL</div>
                <div>
                    <div>Internet Service Provider</div>
                    <div>Arequipa, Perú</div>
                </div>
            </div>
        </div>

        <!-- Invoice Title -->
        <div class="invoice-title">
            <h1>FACTURA</h1>
            <h2>{{ $invoice->invoice_number }}</h2>
            <span class="status-badge {{ $invoice->status == 'paid' ? 'status-paid' : ($invoice->status == 'pending' && $invoice->isOverdue() ? 'status-overdue' : 'status-pending') }}">
                @if($invoice->status == 'paid')
                    PAGADA
                @elseif($invoice->status == 'pending' && $invoice->isOverdue())
                    VENCIDA
                @elseif($invoice->status == 'pending')
                    PENDIENTE
                @elseif($invoice->status == 'partial')
                    PAGO PARCIAL
                @else
                    {{ strtoupper($invoice->status) }}
                @endif
            </span>
        </div>

        <!-- Invoice Details -->
        <div class="invoice-details">
            <div class="invoice-to">
                <h3>Facturar a:</h3>
                <strong>{{ $invoice->billing_name }}</strong><br>
                {{ $invoice->billing_document }}<br>
                {{ $invoice->billing_address }}<br>
                {{ $invoice->billing_email }}
            </div>
            <div class="invoice-info">
                <p><strong>Fecha de Emisión:</strong> {{ $invoice->issue_date->format('d/m/Y') }}</p>
                <p><strong>Fecha de Vencimiento:</strong> {{ $invoice->due_date->format('d/m/Y') }}</p>
                @if($invoice->billing_period)
                <p><strong>Período:</strong> {{ $invoice->billing_period }}</p>
                @endif
                <p><strong>Contrato:</strong> #{{ $invoice->contract->id }}</p>
            </div>
        </div>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th>Descripción</th>
                    <th class="text-center">Cantidad</th>
                    <th class="text-right">Precio Unitario</th>
                    <th class="text-right">Impuesto</th>
                    <th class="text-right">Importe</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-right">${{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-right">${{ number_format($item->tax_amount, 2) }} ({{ $item->tax_rate }}%)</td>
                    <td class="text-right">${{ number_format($item->amount, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals -->
        <div class="totals">
            <div class="total-row">
                <span>Subtotal:</span>
                <span>${{ number_format($invoice->amount, 2) }}</span>
            </div>
            <div class="total-row">
                <span>Impuestos:</span>
                <span>${{ number_format($invoice->taxes, 2) }}</span>
            </div>
            <div class="total-row grand-total">
                <span>TOTAL:</span>
                <span>${{ number_format($invoice->total_amount, 2) }}</span>
            </div>
            @if(in_array($invoice->status, ['partial', 'paid']))
            <div class="total-row">
                <span>Pagado:</span>
                <span>${{ number_format($invoice->paid_amount, 2) }}</span>
            </div>
            <div class="total-row">
                <span>Pendiente:</span>
                <span>${{ number_format($invoice->pending_amount, 2) }}</span>
            </div>
            @endif
        </div>

        <!-- Payment Information -->
        @if($invoice->status != 'paid')
        <div class="payment-info">
            <h4>Información de Pago</h4>
            <p><strong>Fecha de Vencimiento:</strong> {{ $invoice->due_date->format('d/m/Y') }}</p>
            @if($invoice->isOverdue())
            <p style="color: #721c24;"><strong>⚠️ FACTURA VENCIDA - {{ $invoice->daysOverdue() }} días de retraso</strong></p>
            @endif
            <p>Para realizar el pago, contacte con nuestro departamento de cobranza o visite nuestras oficinas.</p>
        </div>
        @endif

        <!-- Notes -->
        @if($invoice->notes)
        <div class="notes">
            <h4>Notas:</h4>
            <p>{{ $invoice->notes }}</p>
        </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <div style="text-align: center;">
                <p><strong>NorETEL - Internet Service Provider</strong></p>
                <p>Arequipa, Perú | Tel: (054) 123-4567 | Email: info@noretel.com</p>
                <p>Gracias por confiar en nuestros servicios</p>
            </div>
        </div>
    </div>

    <!-- Print Controls -->
    <div class="no-print" style="text-align: center; margin: 20px;">
        <button onclick="window.print()" class="btn btn-primary" style="padding: 10px 20px; background-color: #2c5282; color: white; border: none; border-radius: 4px; cursor: pointer;">
            Imprimir Factura
        </button>
        <button onclick="window.close()" class="btn btn-secondary" style="padding: 10px 20px; background-color: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer; margin-left: 10px;">
            Cerrar
        </button>
    </div>

    <script>
        // Auto-print cuando se abre en nueva ventana
        window.onload = function() {
            // Pequeño delay para asegurar que el contenido se cargue completamente
            setTimeout(function() {
                if (window.location.search.includes('auto_print=1')) {
                    window.print();
                }
            }, 1000);
        };
    </script>
</body>
</html>
