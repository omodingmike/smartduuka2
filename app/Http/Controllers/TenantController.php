<?php

    namespace App\Http\Controllers;

    use App\Models\Tenant;
    use Illuminate\Http\Request;

    class TenantController extends Controller
    {
        public function index()
        {
            return Tenant::all();
        }

        public function store(Request $request)
        {
            $name    = $request->name;
            $domain  = config( 'session.domain' );
            $tenant1 = Tenant::create( [ 'id' => $name ] );
            $tenant1->domains()->create( [ 'domain' => "$name-api$domain" ] );
        }

        public function destroy(Tenant $tenant)
        {
            $tenant->delete();
        }
    }
