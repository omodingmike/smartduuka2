<?php

namespace App\Http\Controllers\Table;


use App\Http\Controllers\Controller;
use App\Http\Requests\TableOrderRequest;
use App\Http\Resources\OrderDetailsResource;
use App\Models\FrontendOrder;
use App\Services\OrderService;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Response;


class OrderController extends Controller
{
    private OrderService $orderService;

    public function __construct(OrderService $order)
    {
        $this->orderService = $order;
    }

    public function store(TableOrderRequest $request): Response|OrderDetailsResource| Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return new OrderDetailsResource($this->orderService->tableOrderStore($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function show(FrontendOrder $frontendOrder): Response|OrderDetailsResource| Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return new OrderDetailsResource($frontendOrder);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}