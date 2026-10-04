<?php

namespace App\Enums\Ui;

enum FormMethod: string
{
    case GET    = 'GET';
    case POST   = 'POST';
    case PATCH  = 'PATCH';
    case PUT    = 'PUT';
    case DELETE = 'DELETE';
}
