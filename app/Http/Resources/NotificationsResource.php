<?php

    namespace App\Http\Resources;

    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    class NotificationsResource extends JsonResource
    {
        /**
         * Transform the resource into an array.
         *
         * @return array<string, mixed>
         */
        public function toArray(Request $request) : array
        {
            return [
                'id'       => $this->id ,
                'category' => $this->data[ 'category' ] ?? 'System' ,
                'title'    => $this->data[ 'title' ] ?? 'Notification' ,
                'message'  => $this->data[ 'message' ] ?? '' ,
                'time'     => $this->created_at->diffForHumans() ,
                'date'     => $this->created_at->format( 'Y-m-d' ) ,
                'unread'   => is_null( $this->read_at ) ,
                'icon'     => $this->data[ 'icon' ] ?? '🔔' ,
                'color'    => $this->data[ 'color' ] ?? 'text-blue-500 bg-blue-50 dark:bg-blue-500/10' ,
            ];
        }
    }
