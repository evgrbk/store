<?php

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\JsonResponse as BaseJsonResponse;
use Symfony\Component\HttpFoundation\Response;

class HttpExceptionCodes extends Response
{
    public const NOT_ENOUGH_GOOD = 'GOOD_LEFT_ERROR';
    public const NO_GOOD_FILE = 'NO_GOOD_FILE';
    public const GOOD_NOT_FOUND = 'GOOD_NOT_FOUND';
    public const SERVER_ERROR = 'SERVER_ERROR';
    public const EXCHANGE_ERROR = 'EXCHANGE_ERROR';
}
