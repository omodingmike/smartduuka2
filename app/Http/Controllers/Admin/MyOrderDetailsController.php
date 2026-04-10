<?php

namespace App\Http\Controllers\Admin;

use App\Http\Resources\OrderDetailsResource;
use App\Models\Order;
use App\Models\User;
use App\Services\OrderService;
use Exception;

class MyOrderDetailsController extends AdminController
{

    private OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        parent::__construct();
        $this->orderService = $orderService;
    }

    public function orderDetails(User $user, Order $order) : \Illuminate\Http\Response | OrderDetailsResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return new OrderDetailsResource($this->orderService->orderDetails($user, $order));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}
