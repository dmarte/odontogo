<?php

use App\Models\Document;

return [
    'default_type'=> 'CG',
    'default_fiscal'=> 'B02',
    /*
     | -----------------------------------------------------------
     | Type of contributors according to the country law
     | -----------------------------------------------------------
     */
    'types'  => [
        'PF' => 'Persona física',
        'PJ' => 'Persona jurídica',
        'CG' => 'Cliente General',
    ],
    /*
     | ----------------------------------------------------------
     |  Fiscal settings
     | ----------------------------------------------------------
     | This collection should store the type of sequence
     | the government expect the company to use on each document
     | type emitted.
     */
    'fiscal' => [
        'B01' => [
            'enabled'     => true,
            'title'       => 'Factura de Crédito Fiscal',
            'length'      => 8,
            'description' => 'Registran las transacciones comerciales de compra y venta de bienes y/o los que prestan algún servicio.
                              Permiten al comprador o usuario que lo solicite sustentar gastos y costos del ISR o créditos del ITBIS',
            'documents'   => [
                Document::KIND_CASH_BILL,
                Document::KIND_CREDIT_INVOICE,
            ],
        ],
        'B02' => [
            'enabled'     => true,
            'title'       => 'Factura de consumo',
            'length'      => 8,
            'description' => 'Acreditan la transferencia de bienes, la entrega en uso o la prestación de servicios a consumidores finales.
                             No poseen efectos tributarios, es decir, que no podrán ser utilizados para créditos en el ITBIS y/o reducir gastos y costos del ISR.',
            'documents'   => [
                Document::KIND_CASH_BILL,
                Document::KIND_CREDIT_INVOICE,
            ],
        ],
        'B03' => [
            'enabled'     => true,
            'title'       => 'Nota de débito',
            'length'      => 8,
            'description' => 'Documentos que emiten los vendedores de bienes y/o los que prestan servicios para recuperar costos y gastos, como:
                              intereses por mora, fletes u otros, después de emitido el comprobante fiscal. Sólo podrán ser emitidas al mismo adquiriente
                              o usuario para modificar comprobantes emitidos con anterioridad.',
            'documents'   => [
                Document::KIND_DEBIT_NOTE,
            ],
        ],
        'B04' => [
            'enabled'     => true,
            'title'       => 'Nota de crédito',
            'length'      => 8,
            'description' => 'Documentos que emiten los vendedores de bienes y/ o prestadores de servicios por modificaciones posteriores en las condiciones de
                              venta originalmente pactadas, es decir, para anular operaciones, efectuar devoluciones, conceder descuentos y bonificaciones,
                              corregir errores o casos similares',
            'documents'   => [
                Document::KIND_CREDIT_NOTE,
            ],
        ],
        'B11' => [
            'enabled'     => true,
            'title'       => 'Comprobante de compras',
            'length'      => 8,
            'description' => 'Este tipo de comprobante especial deberá ser emitido por las personas físicas o jurídicas cuando adquieran bienes o servicios de
                              personas no registradas como contribuyentes.',
            'documents'   => [
                Document::KIND_EXPENSE,
            ],
        ],
        'B12' => [
            'enabled'     => true,
            'title'       => 'Registro único de ingreso',
            'length'      => 8,
            'description' => '',
            'documents'   => [],
        ],
        'B13' => [
            'enabled'     => true,
            'title'       => 'Registro de Gastos Menores',
            'length'      => 8,
            'description' => '',
            'documents'   => [
                Document::KIND_EXPENSE,
            ],
        ],
        'B14' => [
            'enabled'     => true,
            'title'       => 'Régimen Especial de Tributación',
            'length'      => 8,
            'description' => '',
            'documents'   => [
                Document::KIND_CREDIT_INVOICE,
                Document::KIND_CASH_BILL,
            ],
        ],
        'B15' => [
            'enabled'     => true,
            'title'       => 'Comprobante Gubernamental',
            'length'      => 8,
            'description' => '',
            'documents'   => [
                Document::KIND_CREDIT_INVOICE,
                Document::KIND_CASH_BILL,
            ],
        ],
        'B16' => [
            'enabled'     => true,
            'title'       => 'Comprobante de exportación',
            'length'      => 8,
            'description' => '',
            'documents'   => [
                Document::KIND_CREDIT_INVOICE,
                Document::KIND_CASH_BILL,
            ],
        ],
        'B17' => [
            'enabled'     => true,
            'title'       => 'Comprobante para pagos al exterior',
            'length'      => 8,
            'description' => '',
            'documents'   => [
                Document::KIND_CREDIT_INVOICE,
                Document::KIND_CASH_BILL,
            ],
        ],
    ],
];
