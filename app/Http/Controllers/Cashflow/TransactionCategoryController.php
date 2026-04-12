<?php

    namespace App\Http\Controllers\Cashflow;

    use App\Http\Requests\Cashflow\TransactionCategoryRequest;
    use App\Http\Resources\Cashflow\TransactionCategoryResource;
    use App\Models\Cashflow\TransactionCategory;
    use App\Traits\HasAdvancedFilter;
    use Illuminate\Http\Request;

    class TransactionCategoryController extends Controller
    {
        use HasAdvancedFilter;

        public function index(Request $request)
        {
            return TransactionCategoryResource::collection( $this->filter( new TransactionCategory() , $request ) );
        }

        public function store(TransactionCategoryRequest $request)
        {
            try {
                $transactionCategory = TransactionCategory::create( $request->validated() );
                activityLog( "Created Transaction Category {$transactionCategory->name}" , $request->header( 'X-App-Id' ) , $transactionCategory );
                return response()->json();
            } catch ( \Exception $e ) {
                throw new \Exception( $e->getMessage() );
            }
        }

        public function update(TransactionCategoryRequest $request , TransactionCategory $transactionCategory)
        {
            try {
                $transactionCategory->update( $request->validated() );
                activityLog( "Updated Transaction Category {$transactionCategory->name}" , $request->header( 'X-App-Id' ) , $transactionCategory );
                return response()->json();
            } catch ( \Exception $e ) {
                throw new \Exception( $e->getMessage() );
            }
        }

        public function destroy(Request $request)
        {
            try {
                $ids = $request->input( 'ids' , [] );
                foreach ( $ids as $id ) {
                    $transactionCategory = TransactionCategory::find( $id );
                    activityLog( "Deleted Transaction Category {$transactionCategory->name}" , $request->header( 'X-App-Id' ) , $transactionCategory );
                    $transactionCategory->delete();
                }
                return response()->json();
            } catch ( \Exception $e ) {
                throw new \Exception( $e->getMessage() );
            }
        }
    }
