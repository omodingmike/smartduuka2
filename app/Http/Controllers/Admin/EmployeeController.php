<?php

namespace App\Http\Controllers\Admin;

use Exception;
use App\Models\User;
use App\Services\OrderService;
use App\Exports\EmployeeExport;
use App\Services\EmployeeService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Resources\OrderResource;
use App\Http\Requests\EmployeeRequest;
use App\Http\Requests\PaginateRequest;
use App\Http\Resources\EmployeeResource;
use App\Http\Requests\ChangeImageRequest;
use App\Http\Requests\UserChangePasswordRequest;
use App\Jobs\SendUserCredentialsJob;

class EmployeeController extends AdminController
{
    private EmployeeService $employeeService;
    private OrderService $orderService;

    public function __construct(EmployeeService $employeeService, OrderService $orderService)
    {
        parent::__construct();
        $this->employeeService = $employeeService;
        $this->orderService = $orderService;
//        $this->middleware(['permission:employees'])->only('export', 'changePassword', 'changeImage', 'myOrder');
//        $this->middleware(['permission:employees_create'])->only('store');
//        $this->middleware(['permission:employees_edit'])->only('update');
//        $this->middleware(['permission:employees_delete'])->only('destroy');
//        $this->middleware(['permission:employees_show'])->only('show');
    }

    public function index(PaginateRequest $request): Response | AnonymousResourceCollection | Application | ResponseFactory
    {
        try {
            return EmployeeResource::collection($this->employeeService->list($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function store(EmployeeRequest $request): Response | EmployeeResource | Application | ResponseFactory
    {
        try {
            $user = $this->employeeService->store($request);
            if ($request->boolean('emailCredentials')) {
                SendUserCredentialsJob::dispatch($user, $request->password, $request->pin);
            }
            return new EmployeeResource($user);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function update(EmployeeRequest $request, User $employee): Response | EmployeeResource | Application | ResponseFactory
    {
        try {
            $user = $this->employeeService->update($request, $employee);
            if ($request->boolean('emailCredentials') && $request->filled('password')) {
                SendUserCredentialsJob::dispatch($user, $request->password, $request->pin);
            }
            return new EmployeeResource($user);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function destroy(Request $request): Response | Application | ResponseFactory
    {
        try {
            User::destroy( $request->ids);
            return response('', 202);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function show(User $employee): Response | EmployeeResource | Application | ResponseFactory
    {
        try {
            return new EmployeeResource($this->employeeService->show($employee));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function export(PaginateRequest $request): Response | \Symfony\Component\HttpFoundation\BinaryFileResponse | Application | ResponseFactory
    {
        try {
            return Excel::download(new EmployeeExport($this->employeeService, $request), 'Employee.xlsx');
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function changePassword(UserChangePasswordRequest $request, User $employee): Response | EmployeeResource | Application | ResponseFactory
    {
        try {
            return new EmployeeResource($this->employeeService->changePassword($request, $employee));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function changeImage(ChangeImageRequest $request, User $employee): Response | EmployeeResource | Application | ResponseFactory
    {
        try {
            return new EmployeeResource($this->employeeService->changeImage($request, $employee));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function myOrder(PaginateRequest $request, User $employee) : Response | AnonymousResourceCollection | Application | ResponseFactory
    {
        try {
            return OrderResource::collection($this->orderService->userOrder($request, $employee));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}
