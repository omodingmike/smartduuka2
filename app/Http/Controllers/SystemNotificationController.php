<?php

    namespace App\Http\Controllers;

    use Illuminate\Http\Request;

    class SystemNotificationController extends Controller
    {
        /**
         * Fetch all notifications for the authenticated user
         */
        public function index(Request $request)
        {
            try {
                $notifications = $request->user()->notifications()->latest()->get()->map( function ($notification) {
                    // Map Laravel's standard DatabaseNotification structure to your frontend interface
                    return [
                        'id'       => $notification->id ,
                        'category' => $notification->data[ 'category' ] ?? 'System' ,
                        'title'    => $notification->data[ 'title' ] ?? 'Notification' ,
                        'message'  => $notification->data[ 'message' ] ?? '' ,
                        'time'     => $notification->created_at->diffForHumans() , // e.g., "2 mins ago"
                        'date'     => $notification->created_at->format( 'Y-m-d' ) ,
                        'unread'   => is_null( $notification->read_at ) ,
                        'icon'     => $notification->data[ 'icon' ] ?? '🔔' ,
                        'color'    => $notification->data[ 'color' ] ?? 'text-blue-500 bg-blue-50 dark:bg-blue-500/10' ,
                    ];
                } );

                return response()->json( [
                    'status' => TRUE ,
                    'data'   => $notifications
                ] );
            } catch ( \Exception $exception ) {
                return response()->json( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        /**
         * Mark all notifications as read for the user
         */
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

        /**
         * Toggle read status for a specific notification
         */
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

        /**
         * Delete selected notifications (supports single or bulk delete)
         */
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