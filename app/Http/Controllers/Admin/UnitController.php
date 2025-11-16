<?php

namespace App\Http\Controllers\Admin;

use Exception;
use App\Models\Unit;
use App\Services\UnitService;
use App\Http\Requests\UnitRequest;
use App\Http\Requests\PaginateRequest;
use App\Http\Resources\UnitResource;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class UnitController extends AdminController
{

    public UnitService $unitService;

    public function __construct(UnitService $unitService)
    {
        parent::__construct();
        $this->unitService = $unitService;
        $this->middleware(['permission:settings'])->only('show', 'store', 'update', 'destroy');
    }

    public function index(PaginateRequest $request): Response| AnonymousResourceCollection| Application| ResponseFactory
    {
        try {
            return UnitResource::collection($this->filter( new Unit() , $request,['name','code','status']));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }


    public function show(Unit $unit): Response|UnitResource| Application| ResponseFactory
    {
        try {
            return new UnitResource($this->unitService->show($unit));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function store(UnitRequest $request): Response|UnitResource| Application| ResponseFactory
    {
        try {
            return new UnitResource($this->unitService->store($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }


    public function update(UnitRequest $request, Unit $unit): Response|UnitResource| Application| ResponseFactory
    {
        try {
            return new UnitResource($this->unitService->update($request, $unit));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function destroy(Request $request): Response| Application| ResponseFactory
    {
        try {
            Unit::destroy($request->ids);
            return response('', 202);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}
