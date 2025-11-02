<?php

namespace App\Http\Controllers\Admin;

use App\Services\UserAddressService;
use Exception;
use App\Models\User;
use App\Models\Address;
use App\Http\Requests\CustomerAddressRequest;
use App\Http\Requests\PaginateRequest;
use App\Http\Resources\AddressResource;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Response;

class CustomerAddressController extends AdminController
{

    private UserAddressService $userAddressService;

    public function __construct(UserAddressService $userAddressService)
    {
        parent::__construct();
        $this->userAddressService = $userAddressService;
        $this->middleware(['permission:customers_show'])->only('index', 'store', 'update', 'destroy', 'show');
    }

    public function index(PaginateRequest $request, User $customer): Response | \Illuminate\Http\Resources\Json\AnonymousResourceCollection | Application | ResponseFactory
    {
        try {
            return AddressResource::collection($this->userAddressService->list($request, $customer));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function store(CustomerAddressRequest $request, User $customer): Response | AddressResource | Application | ResponseFactory
    {
        try {
            return new AddressResource($this->userAddressService->store($request, $customer));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function update(CustomerAddressRequest $request, User $customer, Address $address): Response | AddressResource | Application | ResponseFactory
    {
        try {
            return new AddressResource($this->userAddressService->update($request, $customer, $address));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function destroy(User $customer, Address $address): Response | Application | ResponseFactory
    {
        try {
            $this->userAddressService->destroy($customer, $address);
            return response('', 202);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function show(User $customer, Address $address): Response | AddressResource | Application | ResponseFactory
    {
        try {
            return new AddressResource($this->userAddressService->show($customer, $address));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}
