<?php

    namespace App\Traits;

    use Carbon\Carbon;
    use Illuminate\Contracts\Routing\ResponseFactory;
    use Illuminate\Database\Eloquent\Builder;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;
    use Illuminate\Http\Exceptions\HttpResponseException;
    use Illuminate\Http\Request;
    use Illuminate\Http\Response;
    use Illuminate\Pagination\LengthAwarePaginator;
    use Illuminate\Support\Facades\Schema;
    use Illuminate\Support\Str;

    trait HasAdvancedFilter
    {
        public function filter(
            Model | Builder $queryOrModel ,
            Request $request ,
            array $searchFields = []
        ) : Response | LengthAwarePaginator | ResponseFactory
        {
            try {
                $filters      = $request->input( 'filters' ) ? json_decode( $request->input( 'filters' ) , TRUE ) : [];
                $sorts        = $request->input( 'sort' ) ? json_decode( $request->input( 'sort' ) , TRUE ) : [];
                $page         = $request->input( 'page' , 1 );
                $perPage      = $request->input( 'perPage' , 10 );
                $joinOperator = $request->input( 'joinOperator' , 'and' );

                $query = $queryOrModel instanceof Model
                    ? $queryOrModel::query()
                    : $queryOrModel;

                /**
                 * SIMPLE SEARCH FIELDS
                 */
                foreach ( $searchFields as $field ) {
                    if ( $request->filled( $field ) ) {
                        $query->where( $field , 'ILIKE' , '%' . $request->input( $field ) . '%' );
                    }
                }

                /**
                 * ADVANCED FILTERS — FIXED FOR RELATIONS
                 */
                foreach ( $filters as $filter ) {
                    $field    = $filter[ 'id' ];
                    $value    = $filter[ 'value' ] ?? NULL;
                    $operator = $filter[ 'operator' ] ?? 'eq';
                    $variant  = $filter[ 'variant' ] ?? NULL;

                    if ( $value === '' || $value === NULL || ( is_array( $value ) && empty( array_filter( $value ) ) ) ) {
                        continue;
                    }

                    $method = $joinOperator === 'or' ? 'orWhere' : 'where';

                    // relation filter
                    if ( Str::contains( $field , '.' ) ) {
                        [ $relation , $column ] = explode( '.' , $field , 2 );

                        $query->$method( function ($q) use ($relation , $column , $operator , $variant , $value) {
                            $q->whereHas( $relation , function ($relQ) use ($column , $operator , $variant , $value) {
                                $this->applyFilterOperator( $relQ , $column , $operator , $variant , $value );
                            } );
                        } );

                        continue;
                    }

                    // normal field
                    $this->applyFilterOperator( $query , $field , $operator , $variant , $value );
                }

                /**
                 * SORTING (Supports relations)
                 */

                if ( empty( $sorts ) ) {
                    $query->orderBy( 'created_at' , 'desc' );
                }
                else {
                    foreach ( $sorts as $s ) {
                        $direction = ( ! empty( $s[ 'desc' ] ) && ( $s[ 'desc' ] === TRUE || $s[ 'desc' ] === 'true' ) )
                            ? 'desc'
                            : 'asc';

                        $field = $s[ 'id' ];

                        if ( Str::contains( $field , '.' ) ) {
                            [ $relation , $column ] = explode( '.' , $field , 2 );
                            $this->applyRelationSort( $query , $relation , $column , $direction );
                        }
                        else {
                            if ( $this->isColumnNumeric( $query->getModel()->getTable() , $field ) ) {
                                $query->orderBy( $field , $direction );
                            }
                            else {
                                $query->orderByRaw( "LOWER($field) $direction" );
                            }
                        }
                    }
                }
                return $query->paginate( $perPage , [ '*' ] , 'page' , $page );

            } catch ( \Exception $exception ) {
                throw new HttpResponseException(
                    response()->json( [
                        'status'  => FALSE ,
                        'message' => $exception->getMessage()
                    ] , 422 )
                );
            }
        }


        function isColumnNumeric(string $table , string $column) : bool
        {
            $type         = Schema::getColumnType( $table , $column );
            $numericTypes = [
                'integer' , 'bigint' , 'smallint' ,
                'decimal' , 'float' , 'double' , 'real' ,
                'numeric' ,'int', 'int2', 'int4', 'int8',
                'date' , 'datetime' , 'timestamp' , 'time'
            ];
            return in_array( $type , $numericTypes , TRUE );
        }

        private function applyFilterOperator($q , $field , $operator , $variant , $value) : void
        {
            if ( in_array( $variant , [ 'dateRange' , 'date' ] ) ) {
                $q->where( function ($subQ) use ($field , $operator , $value) {
                    $handleDate = fn($v) => is_array( $v )
                        ? Carbon::createFromTimestampMs( $v[ 0 ] )
                        : Carbon::createFromTimestampMs( $v );

                    $date = $handleDate( $value );

                    switch ( $operator ) {
                        case 'eq':
                            $subQ->where( $field , '>=' , $date->copy()->startOfDay() )
                                 ->where( $field , '<=' , $date->copy()->endOfDay() );
                            break;

                        case 'ne':
                            $subQ->where( $field , '<' , $date->copy()->startOfDay() )
                                 ->orWhere( $field , '>' , $date->copy()->endOfDay() );
                            break;

                        case 'isBetween':
                            if ( is_array( $value ) && count( $value ) === 2 ) {
                                $start = Carbon::createFromTimestampMs( $value[ 0 ] )->copy()->startOfDay();
                                $end   = Carbon::createFromTimestampMs( $value[ 1 ] )->copy()->endOfDay();
                                $subQ->where( $field , '>=' , $start )
                                     ->where( $field , '<=' , $end );
                            }
                            break;
                    }
                } );
                return;
            }

            switch ( $operator ) {
                case 'iLike':
                    $q->where( $field , 'ILIKE' , '%' . $value . '%' );
                    break;
                case 'notILike':
                    $q->where( $field , 'NOT ILIKE' , '%' . $value . '%' );
                    break;
                case 'eq':
                    $q->where( $field , '=' , $value );
                    break;
                case 'ne':
                    $q->where( $field , '!=' , $value );
                    break;
                case 'lt':
                    $q->where( $field , '<' , $value );
                    break;
                case 'lte':
                    $q->where( $field , '<=' , $value );
                    break;
                case 'gt':
                    $q->where( $field , '>' , $value );
                    break;
                case 'gte':
                    $q->where( $field , '>=' , $value );
                    break;
                case 'inArray':
                    $q->whereIn( $field , (array) $value );
                    break;
                case 'notInArray':
                    $q->whereNotIn( $field , (array) $value );
                    break;
                case 'isEmpty':
                    $q->whereNull( $field );
                    break;
                case 'isNotEmpty':
                    $q->whereNotNull( $field );
                    break;
            }
        }

        private function applyRelationSort(&$query , string $relation , string $column , string $direction) : void
        {
            $relationInstance = $query->getModel()->$relation();
            $relatedTable     = $relationInstance->getRelated()->getTable();

            // Determine the key names based on the relationship type
            if ( $relationInstance instanceof BelongsTo ) {
                // For a BelongsTo relationship (e.g., Order belongs to Customer):
                // 1. The key on the *local* table (the current model) is the Foreign Key.
                $localKeyName = $relationInstance->getForeignKeyName();  // e.g., 'customer_id' on 'orders' table

                // 2. The key on the *related* table (the parent) is the Owner/Primary Key.
                $foreignKeyName = $relationInstance->getOwnerKeyName();  // e.g., 'id' on 'customers' table

                $localTable = $relationInstance->getParent()->getTable();
            }
            else {
                // For HasOne/HasMany relationships (the original logic):
                // 1. The key on the *local* table (the current model) is the Local Key (usually 'id').
                $localKeyName = $relationInstance->getLocalKeyName();     // e.g., 'id' on 'users' table

                // 2. The key on the *related* table (the child) is the Foreign Key.
                $foreignKeyName = $relationInstance->getForeignKeyName(); // e.g., 'user_id' on 'posts' table

                $localTable = $relationInstance->getParent()->getTable();
            }

            // Perform the LEFT JOIN using the correct keys
            $query->leftJoin(
                $relatedTable ,
                $localTable . '.' . $localKeyName , // e.g., orders.customer_id
                '=' ,
                $relatedTable . '.' . $foreignKeyName // e.g., customers.id
            )
                  ->orderBy( $relatedTable . '.' . $column , $direction )
                  ->select( $query->getModel()->getTable() . '.*' ); // This select prevents column ambiguity
        }
    }

