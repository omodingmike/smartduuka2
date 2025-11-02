<?php

    namespace App\Models;

    use App\Enums\EnumDistributionStockStatusEnum;
    use App\Filters\DistributionRouteFilters;
    use Essa\APIToolKit\Filters\Filterable;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;
    use Illuminate\Database\Eloquent\Relations\HasMany;


    class DistributionRoute extends Model
    {
        use  Filterable;

        protected $table    = 'distributionRoutes';
        protected $fillable = [ 'user_id' , 'route_value' , 'actual_sales' , 'status' , 'stock_batch' ];

        protected string $default_filters = DistributionRouteFilters::class;

        protected $casts = [
            'status' => EnumDistributionStockStatusEnum::class
        ];

        public function distributor() : BelongsTo
        {
            return $this->belongsTo( User::class , 'user_id' , 'id' );
        }

        public function stocks() : HasMany
        {
            return $this->hasMany( Stock::class , 'batch' , 'stock_batch' )
                        ->where( 'quantity' , '>' , 0 )
                ;
        }
        public function stockSold() : HasMany
        {
            return $this->hasMany( Stock::class , 'batch' , 'stock_batch' )
                        ->where( 'sold' , '>' , 0 );
        }
        public function stockReturned() : HasMany
        {
            return $this->hasMany( Stock::class , 'batch' , 'stock_batch' )
                        ->where( 'returned' , '>' , 0 );
        }
    }
