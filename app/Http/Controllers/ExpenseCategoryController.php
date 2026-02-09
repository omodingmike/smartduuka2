<?php

    namespace App\Http\Controllers;

    use App\Exports\ExcelFileExport;
    use App\Http\Requests\ExpenseCategoryRequest;
    use App\Http\Resources\ExpenseCategoryResource;
    use App\Http\Resources\ProductCategoryDepthTreeResource;
    use App\Models\ExpenseCategory;
    use App\Traits\ApiResponse;
    use App\Traits\AuthUser;
    use Exception;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Log;
    use Illuminate\Support\Facades\Validator;
    use Maatwebsite\Excel\Facades\Excel;

    class ExpenseCategoryController extends Controller
    {
        use ApiResponse , AuthUser;

        public function index(Request $request)
        {
            $name = $request->name;
            $data = ExpenseCategory::tree()->depthFirst()->when( $name , function ($query) use ($name) {
                $query->where( 'name' , 'like' , "%$name%" );
            } )->with( 'parent_category' , 'expenses' )->get();
            return ExpenseCategoryResource::collection( $data );
        }

        public function depthTree()
        {
            try {
                return ProductCategoryDepthTreeResource::collection( ExpenseCategory::tree()->depthFirst()->with( 'parent_category' )->get() );
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        public function export()
        {
            try {
                $data = ExpenseCategory::where( 'user_id' , $this->id() )->get( 'name' );
                return Excel::download( new ExcelFileExport( $data , [ 'name' ] ) , 'Expense_categories.xlsx' );
            } catch ( Exception $exception ) {
                return $this->response( message: $exception->getMessage() );
            }
        }

        public function store(ExpenseCategoryRequest $request)
        {
            $request->merge( [ 'parent_id' => $request->parent_id == '0' ? NULL : $request->parent_id ] );
            $expense_category = ExpenseCategory::create( $request->all() );
            return $this->response( success: TRUE , message: 'success' , data: $expense_category );
        }


        public function update(Request $request , ExpenseCategory $expenseCategory)
        {
            $validation = Validator::make( $request->all() , [ 'name' => 'required' ] );
            if ( $validation->fails() ) {
                return $this->response( message: $validation->errors()->first() );
            }
            $updated = $expenseCategory->update( $validation->validated() );
            if ( $updated ) {
                return $this->response( success: TRUE , message: 'success' , data: $updated );
            }
            else {
                return $this->response( message: 'Updated failed' );
            }
        }

        public function destroy(Request $request)
        {
            $ids = $request->ids;
            foreach ( $ids as $id ) {
                $expenseCategory = ExpenseCategory::find( $id );
                if ( $expenseCategory->children()->exists() ) {
                    return $this->response( message: 'Cannot delete category with sub-categories.' );
                }
                $expenseCategory->delete();
            }
            return $this->response( success: TRUE , message: 'success' );
        }
    }
