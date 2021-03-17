<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminPanel\Currency\CurrencyStoreRequest;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Auth;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Check permission available
     *
     * @param string $permission
     */
    public function checkPermission(string $permission)
    {
        abort_if(!Auth::check() || !Auth::user()->hasPermission($permission), 403, 'Нет доступа');
    }
}
