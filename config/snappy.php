<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Snappy PDF / Image Configuration
    |--------------------------------------------------------------------------
    |
    | This option contains settings for PDF generation.
    |
    | Enabled:
    |    
    |    Whether to load PDF / Image generation.
    |
    | Binary:
    |    
    |    The file path of the wkhtmltopdf / wkhtmltoimage executable.
    |
    | Timeout:
    |    
    |    The amount of time to wait (in seconds) before PDF / Image generation is stopped.
    |    Setting this to false disables the timeout (unlimited processing time).
    |
    | Options:
    |
    |    The wkhtmltopdf command options. These are passed directly to wkhtmltopdf.
    |    See https://wkhtmltopdf.org/usage/wkhtmltopdf.txt for all options.
    |
    | Env:
    |
    |    The environment variables to set while running the wkhtmltopdf process.
    |
    */
    
    'pdf' => [
        'enabled' => true,
        // For Windows, download wkhtmltopdf from: https://wkhtmltopdf.org/downloads.html
        // Then set the path in .env file: WKHTML_PDF_BINARY=C:\path\to\wkhtmltopdf.exe
        // For Linux/Mac: /usr/local/bin/wkhtmltopdf or /usr/bin/wkhtmltopdf
        'binary'  => env('WKHTML_PDF_BINARY', PHP_OS_FAMILY === 'Windows' ? 'C:\Program Files\wkhtmltopdf\bin\wkhtmltopdf.exe' : '/usr/local/bin/wkhtmltopdf'),
        'timeout' => false,
        'options' => [],
        'env'     => [],
    ],
    
    'image' => [
        'enabled' => true,
        'binary'  => env('WKHTML_IMG_BINARY', '/usr/local/bin/wkhtmltoimage'),
        'timeout' => false,
        'options' => [],
        'env'     => [],
    ],

];
