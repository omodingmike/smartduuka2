<?php

namespace App\Libraries;

use App\Models\ProductVariation;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;
use Smartisan\Settings\Facades\Settings;

class QueryExceptionLibrary
{

    /**
     * @param Exception $e
     * @return string
     */
    public static function message(Exception $e): string
    {
        if (isset($e->errorInfo[1]) && $e->errorInfo[1] === 1451) {
            return trans('all.message.resource_already_used');
        } else {
            return $e->getMessage();
        }
    }
}
