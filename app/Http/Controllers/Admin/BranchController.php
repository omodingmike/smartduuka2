<?php

namespace App\Http\Controllers\Admin;

use Exception;
use App\Models\Branch;
use App\Services\BranchService;
use App\Http\Requests\BranchRequest;
use App\Http\Requests\PaginateRequest;
use App\Http\Resources\BranchResource;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class BranchController extends AdminController
{
    public BranchService $branchService;

    public function __construct(BranchService $branch)
    {
        parent::__construct();
        $this->branchService = $branch;
//        $this->middleware(['permission:settings'])->only('store', 'update', 'destroy');
    }

    public function index(PaginateRequest $request
    ) : Response | AnonymousResourceCollection | Application | ResponseFactory {
        try {
            return BranchResource::collection($this->branchService->list($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
    public function branches(PaginateRequest $request
    ) : Response | AnonymousResourceCollection | Application | ResponseFactory {
        try {
            return BranchResource::collection(Branch::all());
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }


    public function show(Branch $branch
    ) : BranchResource | Response | Application | ResponseFactory {
        try {
            return new BranchResource($this->branchService->show($branch));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function store(BranchRequest $request
    ) : BranchResource | Response | Application | ResponseFactory {
        try {
            return new BranchResource($this->branchService->store($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }


    public function update(
        BranchRequest $request,
        Branch $branch
    ) : BranchResource | Response | Application | ResponseFactory {
        try {
            return new BranchResource($this->branchService->update($request, $branch));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function destroy(Request $request) {
        try {
            Branch::destroy($request->ids);
            return response('', 202);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}
