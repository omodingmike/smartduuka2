<?php

namespace App\Http\Controllers\Admin;

use Exception;
use App\Services\ThemeService;
use App\Http\Requests\ThemeRequest;
use App\Http\Resources\ThemeResource;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Foundation\Application;
use Illuminate\Http\Response;

class ThemeController extends AdminController
{
    public ThemeService $themeService;

    public function __construct(ThemeService $themeService)
    {
        parent::__construct();
        $this->themeService = $themeService;
        $this->middleware(['permission:settings'])->only('update');
    }

    public function index(): Application| Response|ThemeResource|\Illuminate\Contracts\Foundation\Application| ResponseFactory
    {
        try {
            return new ThemeResource($this->themeService->list());
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function update(ThemeRequest $request): Application| Response|ThemeResource|\Illuminate\Contracts\Foundation\Application| ResponseFactory
    {
        try {
            return new ThemeResource($this->themeService->update($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}
