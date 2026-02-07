<?php

    namespace App\Services;


    use App\Http\Requests\BranchRequest;
    use App\Http\Requests\PaginateRequest;
    use App\Libraries\QueryExceptionLibrary;
    use App\Models\Branch;
    use Exception;
    use Illuminate\Support\Facades\Log;
    use Smartisan\Settings\Facades\Settings;

    class BranchService
    {
        protected array $branchFilter = [
            'name' ,
            'email' ,
            'phone' ,
            'latitude' ,
            'longitude' ,
            'city' ,
            'state' ,
            'zip_code' ,
            'address' ,
            'status'
        ];

        public function list(PaginateRequest $request)
        {
            try {
                $requests    = $request->all();
                $method      = $request->get( 'paginate' , 0 ) == 1 ? 'paginate' : 'get';
                $methodValue = $request->get( 'paginate' , 0 ) == 1 ? $request->get( 'per_page' , 10 ) : '*';
                $orderColumn = $request->get( 'order_column' ) ?? 'id';
                $orderType   = $request->get( 'order_type' ) ?? 'desc';

                return Branch::where( function ($query) use ($requests) {
                    foreach ( $requests as $key => $request ) {
                        if ( in_array( $key , $this->branchFilter ) ) {
                            $query->where( $key , 'like' , '%' . $request . '%' );
                        }
                    }
                } )->orderBy( $orderColumn , $orderType )->$method(
                    $methodValue
                );
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        /**
         * @throws Exception
         */
        public function store(BranchRequest $request)
        {
            try {
                $branch = Branch::create( [
                    'name'    => $request->input( 'name' ) ,
                    'code'    => $request->input( 'code' ) ,
                    'manager' => $request->input( 'manager' ) ,
                    'phone'   => $request->input( 'phone' ) ,
                    'email'   => $request->input( 'email' ) ,
                    'status'  => $request->input( 'status' ) ,
                    'address' => $request->input( 'address' ) ,
                ] );
                activityLog( "Created Branch: $branch->name" );
                return $branch;
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        /**
         * @throws Exception
         */
        public function update(BranchRequest $request , Branch $branch)
        {
            try {
                $branch = tap( $branch )->update( $request->validated() );
                activityLog( "Updated Branch: $branch->name" );
                return $branch;
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        /**
         * @throws Exception
         */
        public function destroy(Branch $branch) : void
        {
            try {
                if ( Settings::group( 'site' )->get( "site_default_branch" ) != $branch->id ) {
                    $branch->delete();
                    activityLog( "Deleted Branch: $branch->name" );
                }
                else {
                    throw new Exception( "Default branch not deletable" , 422 );
                }
            } catch ( Exception $exception ) {
                // Log::info($exception->getMessage());
                // throw new Exception($exception->getMessage(), 422);
                Log::info( QueryExceptionLibrary::message( $exception ) );
                throw new Exception( QueryExceptionLibrary::message( $exception ) , 422 );
            }
        }

        /**
         * @throws Exception
         */
        public function show(Branch $branch) : Branch
        {
            try {
                return $branch;
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }
    }
