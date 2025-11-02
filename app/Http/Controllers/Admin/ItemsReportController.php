<?php

namespace App\Http\Controllers\Admin;

use Exception;
use App\Services\ItemService;
use App\Exports\ItemsReportExport;
use App\Http\Resources\ItemResource;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\PaginateRequest;

class ItemsReportController extends AdminController
{

    private ItemService $itemService;

    public function __construct(ItemService $itemService)
    {
        parent::__construct();
        $this->itemService = $itemService;
        $this->middleware(['permission:items-report'])->only('index', 'export');
    }

    public function index(PaginateRequest $request) : Response | AnonymousResourceCollection | Application | ResponseFactory
//    public function index(PaginateRequest $request)
    {
        try {
//            return $this->itemService->itemReport($request);
            return ItemResource::collection($this->itemService->itemReport($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function export(PaginateRequest $request) : Response | \Symfony\Component\HttpFoundation\BinaryFileResponse | Application | ResponseFactory
    {
        try {
            return Excel::download(new ItemsReportExport($this->itemService, $request), 'Item-Report.xlsx');
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}
