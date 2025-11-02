<?php

namespace App\Services;

use Exception;
use App\Models\User;
use App\Models\Order;
use App\Enums\OrderStatus;
use App\Libraries\AppLibrary;
use App\Enums\ReturnOrderStatus;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class OverviewService
{
    public function totalOrders()
    {
        try {
            return Order::where('user_id', Auth::user()->id)->count();
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function totalCompletedOrders()
    {
        try {
            return Order::where('user_id', Auth::user()->id)->where('status', OrderStatus::DELIVERED)->count();
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function walletBalance()
    {
        try {
            return AppLibrary::currencyAmountFormat(Auth::user()->balance);
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }
}
