<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PurchaseStatus;
use App\Http\Resources\PurchaseResource;
use App\Models\Purchase;
use Exception;
use App\Services\SupplierService;
use App\Http\Requests\SupplierRequest;
use App\Http\Requests\PaginateRequest;
use App\Http\Resources\SupplierResource;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends AdminController
{
    private SupplierService $supplierService;

    public function __construct(SupplierService $supplierService)
    {
        parent::__construct();
        $this->supplierService = $supplierService;
//        $this->middleware(['permission:settings'])->only('store', 'update', 'destroy', 'show');
    }

    public function index(PaginateRequest $request): \Illuminate\Http\Response | \Illuminate\Http\Resources\Json\AnonymousResourceCollection | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return SupplierResource::collection($this->supplierService->list($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function store(SupplierRequest $request): \Illuminate\Http\Response | SupplierResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return new SupplierResource($this->supplierService->store($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function update(SupplierRequest $request, Supplier $supplier): \Illuminate\Http\Response | SupplierResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return new SupplierResource($this->supplierService->update($request, $supplier));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function destroy(Request $request): \Illuminate\Http\Response | \Illuminate\Contracts\Foundation\Application |
    \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            Supplier::destroy( $request->ids);
            return response('', 202);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
    

    public function show(Supplier $supplier): \Illuminate\Http\Response | SupplierResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return new SupplierResource($this->supplierService->show($supplier));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function purchases(Supplier $supplier)
    {
        try {
            return PurchaseResource::collection(Purchase::where('supplier_id', $supplier->id)
//                                                        ->where('status', PurchaseStatus::ORDERED)
                                                        ->get());
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}
