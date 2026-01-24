<?php

    namespace App\Models;

    use App\Http\Requests\PaginateRequest;
    use App\Services\StockService;
    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;

    class Warehouse extends Model
    {
        use HasFactory;

        protected $appends = [ 'stocks' ];

        protected $fillable = [
            'name' ,
            'deletable' ,
            'email' ,
            'location' ,
            'phone' ,
            'manager' ,
            'capacity' ,
            'status' ,
            'id'
        ];
        protected $casts    = [
            'deletable' => 'boolean'
        ];

        public function getStocksAttribute()
        {
            $stockService = new StockService();
            $request      = new PaginateRequest();
            $request->merge( [ 'warehouse_id' => $this->id ] );
            return $stockService->list( $request );
        }
    }
