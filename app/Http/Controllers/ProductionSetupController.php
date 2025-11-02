<?php

    namespace App\Http\Controllers;

    use App\Http\Requests\StoreProductionSetupRequest;
    use App\Http\Requests\UpdateProductionSetupRequest;
    use App\Http\Resources\ProductionSetupResource;
    use App\Models\Ingredient;
    use App\Models\ProductionSetup;
    use App\Models\Stock;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\DB;

    class ProductionSetupController extends Controller
    {
        public function index(Request $request)
        {
            $method      = $request->get('paginate' , 0) == 1 ? 'paginate' : 'get';
            $methodValue = $request->get('paginate' , 0) == 1 ? $request->get('per_page' , 10) : '*';
            $orderColumn = $request->get('order_column') ?? 'id';
            $orderType   = $request->get('order_type') ?? 'desc';
            return ProductionSetupResource::collection(ProductionSetup::with('product.rawMaterials')->orderBy($orderColumn , $orderType)->$method(
                $methodValue
            ));
        }

        public function store(StoreProductionSetupRequest $request)
        {
            try {
                DB::transaction(function () use ($request) {
                    $productionSetup = ProductionSetup::create([
                        'product_id'   => $request->validated()['product_id'] ,
                        'name'         => $request->validated()['name'] ,
                        'overall_cost' => $request->validated()['overall_cost'] ,
                    ]);
                    activityLog('Created Production setup: ' . $productionSetup->name);
                    if ( $request->ingredients ) {
                        $syncData = [];
                        foreach ( $request->ingredients as $ingredient ) {
                            $stock = Stock::where('item_type' , Ingredient::class)
                                          ->where('item_id' , $ingredient['ingredient_id'])
                                          ->first();

                            $ingredient_name = Ingredient::find($ingredient['ingredient_id'])?->name;

                            if ( ! $stock ) {
                                throw new \Exception('Raw material stock not found' , 422);
                            }

                            if ( $stock->quantity < $ingredient['quantity'] ) {
                                throw new \Exception("Raw material $ingredient_name quantity is not enough" , 422);
                            }

                            $syncData[$ingredient['ingredient_id']] = [
                                'quantity'     => $ingredient['quantity'] ,
                                'buying_price' => $ingredient['buying_price'] ,
                                'total'        => $ingredient['total'] ,
                                'setup_id'     => $productionSetup->id ,
                            ];
                        }

                        $productionSetup->product->rawMaterials()->syncWithoutDetaching($syncData);
                        activityLog('Created Raw materials for Production process: ' . $productionSetup->name);
                    }
                } , 5);
                return response([ 'status' => true , 'message' => 'Production setup created successfully' ]);
            } catch ( \Exception $e ) {
                return response([ 'status' => false , 'message' => $e->getMessage() ] , 422);
            }
        }

        public function update(UpdateProductionSetupRequest $request , ProductionSetup $productionSetup)
        {
            try {
                $productionSetup->update([
                    'product_id' => $request->validated()['product_id'] ,
                    'name'       => $request->validated()['name'] ,
                ]);
                activityLog('Updated Production setup: ' . $productionSetup->name);
                if ( $request->ingredients ) {
                    $syncData = [];
                    foreach ( $request->ingredients as $ingredient ) {
                        $stock           = Stock::where('item_type' , Ingredient::class)
                                                ->where('item_id' , $ingredient['ingredient_id'])
                                                ->first();
                        $ingredient_name = Ingredient::find($ingredient['ingredient_id'])?->name;
                        if ( ! $stock ) {
                            throw new \Exception('Raw material stock not found' , 422);
                        }
                        if ( $stock->quantity < $ingredient['quantity'] ) {
                            throw new \Exception("Raw material $ingredient_name quantity is not enough" , 422);
                        }
                        $syncData[$ingredient['ingredient_id']] = [
                            'quantity'     => $ingredient['quantity'] ,
                            'buying_price' => $ingredient['buying_price'] ,
                            'total'        => $ingredient['total'] ,
                            'setup_id'     => $productionSetup->id ,
                        ];
                    }
                    $productionSetup->product->rawMaterials()->syncWithoutDetaching($syncData);
                    activityLog('Created Raw materials for Production process: ' . $productionSetup->name);
                    return $productionSetup;
                }
            } catch ( \Exception $e ) {
                return response([ 'status' => false , 'message' => $e->getMessage() ] , 422);
            }
        }

        public function destroy(ProductionSetup $productionSetup)
        {
            $productionSetup->delete();
            activityLog('Deleted Production setup: ' . $productionSetup->name);
        }
    }
