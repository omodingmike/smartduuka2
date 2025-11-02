<?php

namespace App\Http\Controllers\Admin;

use Exception;
use App\Models\ThemeSetting;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use App\Services\OrderService;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\CompanyService;
use App\Exports\SalesReportExport;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\PaginateRequest;
use Smartisan\Settings\Facades\Settings;
use App\Http\Resources\SimpleOrderResource;
use App\Http\Resources\SalesReportOverviewResource;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SalesReportController extends AdminController
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */

    private OrderService $orderService;
    private CompanyService $companyService;

    public function __construct(OrderService $orderService, CompanyService $companyService)
    {
        parent::__construct();
        $this->orderService = $orderService;
        $this->companyService = $companyService;
        $this->middleware(['permission:sales-report'])->only('index', 'salesReportOverview', 'export', 'exportPdf');
    }

    public function index(PaginateRequest $request): Response | \Illuminate\Http\Resources\Json\AnonymousResourceCollection | Application | ResponseFactory
    {
        try {
            return SimpleOrderResource::collection($this->orderService->list($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function export(PaginateRequest $request): Response | BinaryFileResponse | Application | ResponseFactory
    {
        try {
            return Excel::download(new SalesReportExport($this->orderService, $request), 'Sales-Report.xlsx');
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function salesReportOverview(Request $request): \Illuminate\Foundation\Application| Response|SalesReportOverviewResource| Application| ResponseFactory
    {
        try {
            return new SalesReportOverviewResource($this->orderService->salesReportOverview($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function exportPdf(PaginateRequest $request): mixed
    {
        try {
            $company = $this->companyService->list();
            $copyright   = Settings::group('site')->get('site_copyright');
            $orders = $this->orderService->list($request);

            $imagePath = ThemeSetting::where(['key' => 'theme_logo'])->first()?->logo;
            $response = Http::withOptions(['verify' => false])->get($imagePath);
            $data = $response->body();
            $theme_logo = 'data:image/png;base64,' . base64_encode($data);

            $pdf = Pdf::loadView('reports.salesReport', compact('company', 'theme_logo', 'orders', 'copyright'))
                ->setPaper('a4')->setOption(['defaultFont' => 'Urbanist']);;
            return response()->stream(
                fn() => print($pdf->output()),
                200,
                [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="online_order_report.pdf"',
                ]
            );
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}
