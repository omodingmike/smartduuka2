<?php

    namespace App\Http\Controllers;

    use App\Enums\Enabled;
    use App\Enums\ProductionProcessStatus;
    use App\Enums\Status;
    use App\Http\Requests\StoreProductionProcessRequest;
    use App\Http\Requests\UpdateProductionProcessRequest;
    use App\Http\Resources\ProductionProcessResource;
    use App\Http\Resources\ProductionReportResource;
    use App\Models\Ingredient;
    use App\Models\Product;
    use App\Models\ProductionProcess;
    use App\Models\Stock;
    use Illuminate\Http\Request;
    use Smartisan\Settings\Facades\Settings;

    class ProductionProcessController extends Controller
    {
        public function index(Request $request)
        {
            $method      = $request->get('paginate' , 0) == 1 ? 'paginate' : 'get';
            $methodValue = $request->get('paginate' , 0) == 1 ? $request->get('per_page' , 10) : '*';
            $orderColumn = $request->get('order_column') ?? 'id';
            $orderType   = $request->get('order_type') ?? 'desc';
            return ProductionProcessResource::collection(ProductionProcess::with('setup')->orderBy($orderColumn , $orderType)->$method(
                $methodValue
            ));
        }

        public function report()
        {
            return ProductionReportResource::collection(ProductionProcess::with('setup')->get());
        }

        public function completed(Request $request)
        {
            $method      = $request->get('paginate' , 0) == 1 ? 'paginate' : 'get';
            $methodValue = $request->get('paginate' , 0) == 1 ? $request->get('per_page' , 10) : '*';
            $orderColumn = $request->get('order_column') ?? 'id';
            $orderType   = $request->get('order_type') ?? 'desc';

            return ProductionProcessResource::collection(ProductionProcess::with('setup')
                                                                          ->where('status' , ProductionProcessStatus::COMPLETED)
                                                                          ->orderBy($orderColumn , $orderType)->$method(
                    $methodValue
                ));
        }

        public function store(StoreProductionProcessRequest $request)
        {
            $production_process = ProductionProcess::create($request->validated() + [ 'warehouse_id' => $request->warehouse_id ]);
            activityLog('Created Production process for : ' . $production_process->setup->name);
        }

        public function show(ProductionProcess $productionProcess)
        {
            return new ProductionProcessResource($productionProcess);
        }

        public function update(UpdateProductionProcessRequest $request , ProductionProcess $productionProcess)
        {
            if ( $request->status == ProductionProcessStatus::COMPLETED ) {
                $productionProcess->update($request->validated() + [ 'end_date' => now() ]);
                activityLog('Updated Production process for : ' . $productionProcess->setup->name);
                $stock                 = Stock::create([
                    'model_type' => Product::class ,
                    'model_id'   => $productionProcess->setup->product->id ,
                    'item_type'  => Product::class ,
                    'product_id' => $productionProcess->setup->product->id ,
                    'price'      => $productionProcess->setup->product->buying_price ,
                    'quantity'   => $productionProcess->actual_quantity ,
                    'discount'   => 0 ,
                    'tax'        => 0 ,
                    'subtotal'   => 0 ,
                    'total'      => $productionProcess->setup->product->buying_price * $productionProcess->actual_quantity ,
                    'status'     => Status::ACTIVE
                ]);
                activityLog('Added stock through production : ' . $productionProcess->setup->name);
                $module_warehouse = Settings::group('module')->get('module_warehouse');
                if ( $module_warehouse == Enabled::YES ) {
                    $stock->warehouse_id = $productionProcess->warehouse_id;
                    $stock->save();
                }

                $total_cost = $productionProcess->setup->product->rawMaterials()->sum('total');

                Product::find($productionProcess->setup->product->id)->update([ 'buying_price' => $total_cost ]);

                if ( $stock ) {
                    activityLog('Created Stock for Item : ' . $productionProcess->setup->product->name);
                }

                foreach ( $productionProcess->setup->product->rawMaterials as $ingredient ) {
                    $stock = Stock::where('item_type' , Ingredient::class)
                                  ->where('product_id' , $ingredient->id)->first();
                    $stock?->decrement('quantity' , $ingredient->pivot->quantity * $productionProcess->actual_quantity);
                }
            } else {
                $productionProcess->update($request->validated());
                activityLog('Updated Production process for : ' . $productionProcess->setup->name);
            }
        }

        public function cancel(ProductionProcess $process)
        {
            $process->update([ 'status' => ProductionProcessStatus::CANCELLED ]);
            activityLog('Cancelled Production process for : ' . $process->setup->name);
        }

        public function destroy(ProductionProcess $productionProcess)
        {
            $productionProcess->delete();
            activityLog('Deleted Production process for : ' . $productionProcess->setup->name);
        }
    }
