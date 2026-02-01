<?php

    namespace App\Models;

    use App\Enums\Department;
    use App\Enums\Priority;
    use App\Enums\PurchaseRequestStatus;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\MorphMany;

    class StockPurchaseRequest extends Model
    {
        protected $fillable = [
            'requester_name' ,
            'department' ,
            'priority' ,
            'date' ,
            'reason' ,
            'reference' ,
            'status' ,
            'supplier_id'
        ];
        protected $table    = 'stock_purchase_requests';

        protected function casts() : array
        {
            return [
                'date'       => 'datetime' ,
                'department' => Department::class ,
                'priority'   => Priority::class ,
                'status'     => PurchaseRequestStatus::class ,
            ];
        }

        public function stocks() : morphMany
        {
            return $this->morphMany( Stock::class , 'model' );
        }
    }
