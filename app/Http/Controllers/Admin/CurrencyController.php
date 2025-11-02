<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\CurrencyRequest;
use App\Http\Requests\PaginateRequest;
use App\Http\Resources\CurrencyResource;
use App\Models\Currency;
use App\Services\CurrencyService;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class CurrencyController extends AdminController
{
    private CurrencyService $currencyService;

    public function __construct(CurrencyService $currencyService)
    {
        parent::__construct();
        $this->currencyService = $currencyService;
         $this->middleware(['permission:settings'])->only('store', 'update', 'destroy', 'show');
    }

    public function index(PaginateRequest $request): Response | AnonymousResourceCollection | Application | ResponseFactory
    {
        try {
            return CurrencyResource::collection($this->currencyService->list($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function store(CurrencyRequest $request): CurrencyResource | Response | Application | ResponseFactory
    {
        try {
            return new CurrencyResource($this->currencyService->store($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function update(CurrencyRequest $request, Currency $currency): CurrencyResource | Response | Application | ResponseFactory
    {
        try {
            return new CurrencyResource($this->currencyService->update($request, $currency));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function destroy(Currency $currency): Response | Application | ResponseFactory
    {
        try {
            $this->currencyService->destroy($currency);
            return response('', 202);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function show(Currency $currency): CurrencyResource | Response | Application | ResponseFactory
    {
        try {
            return new CurrencyResource($currency);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}
