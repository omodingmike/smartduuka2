<?php

    namespace App\Http\Controllers;

    use App\Http\Resources\NotificationsResource;
    use Illuminate\Http\Request;

    class SystemNotificationController extends Controller
    {
        public function index(Request $request)
        {
            try {
                $query = $request->user()->notifications()->latest();

                $category = $request->input('category');
                $status = $request->input('status');

//                if (!empty($category) && strtolower($category) !== 'all') {
//                    $query->where('data->category', $category);
//                }

                if (!empty($status) && strtolower($status) !== 'all') {
                    if ($status === 'unread') {
                        $query->whereNull('read_at');
                    } elseif ($status === 'read') {
                        $query->whereNotNull('read_at');
                    }
                }

                $notifications = $query->paginate(
                    $request->integer('per_page', 10)
                );

                return NotificationsResource::collection($notifications);
            } catch (\Exception $exception) {
                return response()->json([
                    'status'  => false,
                    'message' => $exception->getMessage()
                ], 422);
            }
        }

        public function markAllAsRead(Request $request)
        {
            try {
                $request->user()->unreadNotifications->markAsRead();

                return response()->json( [
                    'status'  => TRUE ,
                    'message' => 'All notifications marked as read.'
                ] );
            } catch ( \Exception $exception ) {
                return response()->json( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function toggleReadStatus(Request $request , $id)
        {
            try {
                $notification = $request->user()->notifications()->findOrFail( $id );

                if ( $notification->read_at ) {
                    $notification->markAsUnread();
                }
                else {
                    $notification->markAsRead();
                }

                return response()->json( [
                    'status'  => TRUE ,
                    'message' => 'Notification status updated.'
                ] );
            } catch ( \Exception $exception ) {
                return response()->json( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function destroy(Request $request)
        {
            try {
                $ids = $request->input( 'ids' , [] ); // Assuming the payload is { ids: [1, 2, 3] }

                if ( ! empty( $ids ) ) {
                    $request->user()->notifications()->whereIn( 'id' , $ids )->delete();
                }

                return response()->json( [
                    'status'  => TRUE ,
                    'message' => 'Notifications deleted successfully.'
                ] );
            } catch ( \Exception $exception ) {
                return response()->json( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }
    }