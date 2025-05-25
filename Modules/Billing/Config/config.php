<?php

return [
    'name' => 'Billing',
    'invoice' => [
        'auto_generation' => true,
        'prefix' => 'INV-',
        'digits' => 6,
        'due_days' => 15,
        'payment_reminder_days' => [3, 7, 1],
        'late_fee_percentage' => 5
    ],
    'payment' => [
        'methods' => [
            'cash' => 'Efectivo',
            'credit_card' => 'Tarjeta de Crédito',
            'debit_card' => 'Tarjeta de Débito',
            'bank_transfer' => 'Transferencia Bancaria',
            'check' => 'Cheque',
            'online_payment' => 'Pago en Línea',
            'direct_debit' => 'Débito Automático'
        ]
    ],
    'credit_note' => [
        'prefix' => 'CN-',
        'digits' => 6
    ],
    'tax' => [
        'default_rate' => 18 // Porcentaje de impuesto (ajustar según normativa local)
    ]
];
