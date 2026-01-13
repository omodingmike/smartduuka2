<?php

namespace App\Http\Controllers\Admin;

use App\Http\Resources\NotificationResource;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use App\Services\NotificationAlertService;
use App\Http\Resources\NotificationAlertResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class NotificationAlertController extends AdminController
{
    private NotificationAlertService $notificationAlertService;

    public function __construct(NotificationAlertService $notificationAlertService)
    {
        parent::__construct();
        $this->notificationAlertService = $notificationAlertService;
        $this->middleware(['permission:settings'])->only('update');
    }

    public function index(
    ) : \Illuminate\Http\Response | NotificationResource | Application | ResponseFactory
    {
        try {
            return new NotificationResource($this->notificationAlertService->list());
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function update(Request $request)  {
        try {
            return new NotificationResource($this->notificationAlertService->update($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}
