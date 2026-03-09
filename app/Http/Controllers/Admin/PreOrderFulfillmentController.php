<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PreOrderFulfillmentRequest;
use App\Models\Order;
use App\Services\OrderService;
use Exception;
use Illuminate\Http\Response;

class PreOrderFulfillmentController extends Controller
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function fulfill(PreOrderFulfillmentRequest $request, Order $order): Response
    {
        try {
            $this->orderService->fulfillPreOrder($order, $request->validated());
            return response(['status' => true, 'message' => 'Pre-order fulfilled successfully'], 200);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}
