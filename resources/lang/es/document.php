<?php

use App\Models\Document;

return [
    Document::KIND_CREDIT_INVOICE => 'Factura a crédito',
    Document::KIND_CASH_BILL => 'Factura de contado',
    Document::KIND_PAYMENT_RECEIPT => 'Recibo de pago',
    Document::KIND_DEPOSIT => 'Depósito',
    Document::KIND_INVOICE_BUDGET => 'Cotización',
    Document::KIND_DOCTOR_EVALUATION => 'Evaluación de doctor'
];
