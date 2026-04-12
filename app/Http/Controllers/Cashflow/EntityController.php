<?php

    namespace App\Http\Controllers\Cashflow;

    use App\Http\Requests\Cashflow\EntityRequest;
    use App\Http\Resources\Cashflow\EntityResource;
    use App\Models\Cashflow\Entity;
    use App\Traits\HasAdvancedFilter;
    use Illuminate\Http\Request;

    class EntityController extends Controller
    {
        use HasAdvancedFilter;

        public function index(Request $request)
        {
            return EntityResource::collection( $this->filter( Entity::withCashSummary() , $request ) );
        }

        public function store(EntityRequest $request)
        {
            $entity = Entity::create( $request->validated() );
            activityLog( "Created Entity {$entity->name}" , $request->header( 'X-App-Id' ) , $entity );
            return response()->json();
        }

        public function update(EntityRequest $request , Entity $entity)
        {
            $entity->update( $request->validated() );
            activityLog( "Updated Entity {$entity->name}" , $request->header( 'X-App-Id' ) , $entity );

            return response()->json();
        }

        public function destroy(Request $request)
        {
            $ids = $request->input( 'ids' , [] );
            foreach ( $ids as $id ) {
                $entity = Entity::find( $id );
                activityLog( "Deleted Entity {$entity->name}" , $request->header( 'X-App-Id' ) , $entity );
                $entity->delete();
            }

            return response()->json();
        }
    }
