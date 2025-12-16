<?php

return [
    'mode' => env('DOMPDF_MODE', 'utf-8'),
    'defines' => [
        'DOMPDF_TEMP_DIR' => sys_get_temp_dir(),
        'DOMPDF_FONT_CACHE' => storage_path('fonts'),
        'DOMPDF_FONT_DIR' => storage_path('fonts'),
        'DOMPDF_FONT_FAMILY' => 'sans-serif',
        'DOMPDF_ENABLE_CSS_FLOAT' => true,
        'DOMPDF_ENABLE_JAVASCRIPT' => false,
        'DOMPDF_CHROOT' => public_path(),
        'DOMPDF_ENABLE_REMOTE' => true,
    ],
    'show_warnings' => false,
    'orientation' => 'portrait',
    'logOutputFile' => storage_path('logs/dompdf.log'),
    'defaultFont' => 'Arial',
    'pdf_version' => '1.7',
];
