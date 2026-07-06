<?php

return [
    /*
    |--------------------------------------------------------------------------
    | PDF Text Extraction
    |--------------------------------------------------------------------------
    |
    | This path is used by the PDF text extraction package to locate the
    | pdftotext binary on the local machine. The value can be changed from
    | the .env file without modifying the application code.
    |
    */

    'pdftotext_path' => env('PDFTOTEXT_PATH', '/opt/homebrew/bin/pdftotext'),
];