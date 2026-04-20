<?php

    namespace App\Http\Controllers;
    use App\Http\Resources\TenantResource;
    use App\Models\Tenant;
    use App\Traits\HasAdvancedFilter;
    use Illuminate\Http\Request;

    class BusinessController extends Controller
    {
        use HasAdvancedFilter;

        public function index(Request $request)
        {
            $query = Tenant::with( [ 'domains' , 'subscriptions' ] );
            return TenantResource::collection( $this->filter( $query , $request ) );
        }

        public function store(Request $request)
        {
            //
        }

        public function show(string $id)
        {
            //
        }

        public function update(Request $request , string $id)
        {
            //
        }

        public function destroy(string $id)
        {
            //
        }
    }
