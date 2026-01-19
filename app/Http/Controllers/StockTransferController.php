<?php

    namespace App\Http\Controllers;

    use App\Enums\Ask;
    use App\Http\Requests\PaginateRequest;
    use App\Http\Requests\StoreStockTransferRequest;
    use App\Http\Requests\UpdateStockTransferRequest;
    use App\Models\Ingredient;
    use App\Models\Stock;
    use App\Models\StockTransfer;
    use Exception;
    use Illuminate\Support\Facades\Log;
    use Illuminate\Support\Facades\URL;

    class StockTransferController extends Controller
    {
        protected $stockFilter = [
            'product_name' ,
            'status' ,
        ];

        public function index(PaginateRequest $request)
        {
            return $this->list($request);
        }

        public function list(PaginateRequest $request)
        {
            try {
                $requests    = $request->all();
                $method      = $request->get('paginate' , 0) == 1 ? 'paginate' : 'get';
                $methodValue = $request->get('paginate' , 0) == 1 ? $request->get('per_page' , 10) : '*';
                $orderColumn = $request->get('order_column') ?? 'id';
                $orderType   = $request->get('order_type') ?? 'desc';
                $stocks      = Stock::with([ 'product.sellingUnits:id,code' , 'product.unit:id,code' ])->where(function ($query) use ($requests) {
                    $query->where('model_type' , '<>' , Ingredient::class);
                    foreach ( $requests as $key => $request ) {
                        if ( in_array($key , $this->stockFilter) ) {
                            if ( $key == 'product_name' ) {
                                $query->whereHas('product' , function ($query) use ($request) {
                                    $query->where('name' , 'like' , '%' . $request . '%');
                                })->get();
                            } else {
                                $query->where($key , 'like' , '%' . $request . '%');
                            }
                        }
                    }
                })->orderBy($orderColumn , $orderType)->get();

                if ( ! blank($stocks) ) {
                    $stocks->groupBy('product_id')?->map(function ($product) {
                        $product->groupBy('product_id')?->map(function ($item) {
                            if ( $item->first()['product'] ) {
                                $this->items[] = [
                                    'product_id'      => $item->first()['product_id'] ,
                                    'product_name'    => $item->first()['product']['name'] ,
                                    'unit'            => $item->first()['product']['unit'] ,
                                    'other_unit'      => $item->first()->product->otherUnit ,
                                    'units_nature'    => $item->first()->product->units_nature ,
                                    'variation_names' => $item->first()['variation_names'] ,
                                    'status'          => Ask::YES ,
                                    'stock'           => $item->first()['product']['can_purchasable'] === Ask::NO ? 'N/C' : $item->sum('quantity') ,
                                    'other_stock'     => $item->first()['product']['can_purchasable'] === Ask::NO ? 'N/C' : $item->sum('other_quantity') ,
                                ];
                            }
                        });
                    });
                } else {
                    $this->items = [];
                }

                if ( $method == 'paginate' ) {
                    return $this->paginate($this->items , $methodValue , null , URL::to('/') . '/api/admin/stock');
                }

                return $this->items;
            } catch ( Exception $exception ) {
                Log::info($exception->getMessage());
                throw new Exception($exception->getMessage() , 422);
            }
        }
    }
