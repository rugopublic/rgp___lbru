<?php

return [
    'operator_name' => env('LEGAL_OPERATOR_NAME', 'Укажите полное наименование оператора'),
    'operator_address' => env('LEGAL_OPERATOR_ADDRESS', 'Укажите адрес оператора'),
    'operator_email' => env('LEGAL_OPERATOR_EMAIL', 'admin@likablog.ru'),
    'operator_site' => env('LEGAL_OPERATOR_SITE', 'https://likablog.ru'),
    'operator_phone' => env('LEGAL_OPERATOR_PHONE'),
    'privacy_version' => env('LEGAL_PRIVACY_VERSION', '2026.08'),
    'agreement_version' => env('LEGAL_EULA_VERSION', '2026.08'),
];
