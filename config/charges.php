<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Tipo de Cambio
    |--------------------------------------------------------------------------
    |
    | Tipo de cambio USD a BOB para conversión de montos.
    |
    */
    'exchange_rate' => env('EXCHANGE_RATE', 6.96),

    /*
    |--------------------------------------------------------------------------
    | Tamaño máximo de ZIP
    |--------------------------------------------------------------------------
    |
    | Tamaño máximo en KB para archivos ZIP (default 100MB).
    |
    */
    'max_zip_size' => env('MAX_ZIP_SIZE', 102400),
];
