<?php

    namespace App\Http\Controllers;

    use App\Http\Requests\PaginateRequest;
    use App\Http\Resources\PurchaseReturnResource;
    use App\Models\PurchaseReturn;
    use Exception;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
    use Illuminate\Support\Facades\Log;

    class PurchaseReturnController extends Controller
    {
 
        public function index(PaginateRequest $request) : AnonymousResourceCollection
        {
            try {
                $requests    = $request->all();
                $method      = $request->get( 'paginate' , 0 ) == 1 ? 'paginate' : 'get';
                $methodValue = $request->get( 'paginate' , 0 ) == 1 ? $request->get( 'per_page' , 10 ) : '*';
                $orderColumn = $request->get( 'order_column' ) ?? 'id';
                $orderType   = $request->get( 'order_type' ) ?? 'desc';

                $query = PurchaseReturn::with( [ 'supplier' , 'purchase' ] );

                if ( isset( $requests[ 'supplier_id' ] ) ) {
                    $query->where( 'supplier_id' , $requests[ 'supplier_id' ] );
                }
                if ( isset( $requests[ 'purchase_id' ] ) ) {
                    $query->where( 'purchase_id' , $requests[ 'purchase_id' ] );
                }
                if ( isset( $requests[ 'date' ] ) ) {
                    $date_start = date( 'Y-m-d 00:00:00' , strtotime( $requests[ 'date' ] ) );
                    $date_end   = date( 'Y-m-d 23:59:59' , strtotime( $requests[ 'date' ] ) );
                    $query->where( 'date' , '>=' , $date_start )->where( 'date' , '<=' , $date_end );
                }
                if ( isset( $requests[ 'debit_note' ] ) ) {
                    $query->where( 'debit_note' , 'like' , '%' . $requests[ 'debit_note' ] . '%' );
                }

                return PurchaseReturnResource::collection( $query->orderBy( $orderColumn , $orderType )->$method( $methodValue ) );
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }
        
        public function store(Request $request) : PurchaseReturnResource
        {
            $validated = $request->validate( [
                'supplier_id' => 'required|exists:suppliers,id' ,
                'purchase_id' => 'required|exists:purchases,id' ,
                'date'        => 'required|date' ,
                'debit_note'  => 'nullable|string' ,
                'notes'       => 'nullable|string' ,
            ] );

            $purchaseReturn = PurchaseReturn::create( $validated );

            return new PurchaseReturnResource( $purchaseReturn );
        }

        public function show(PurchaseReturn $return) : PurchaseReturnResource
        {
            return new PurchaseReturnResource( $return->load( [ 'supplier' , 'purchase' ] ) );
        }
        
        public function update(Request $request , PurchaseReturn $return) : PurchaseReturnResource
        {
            $validated = $request->validate( [
                'supplier_id' => 'sometimes|exists:suppliers,id' ,
                'purchase_id' => 'sometimes|exists:purchases,id' ,
                'date'        => 'sometimes|date' ,
                'debit_note'  => 'nullable|string' ,
                'notes'       => 'nullable|string' ,
            ] );

            $return->update( $validated );

            return new PurchaseReturnResource( $return );
        }
        
        public function destroy(PurchaseReturn $return)
        {
            $return->delete();

            return response()->noContent();
        }
    }
