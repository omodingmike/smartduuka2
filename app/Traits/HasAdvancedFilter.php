<?php

    namespace App\Traits;

    use Carbon\Carbon;
    use Illuminate\Contracts\Routing\ResponseFactory;
    use Illuminate\Database\Eloquent\Builder;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Http\Request;
    use Illuminate\Http\Response;
    use Illuminate\Pagination\LengthAwarePaginator;
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
                        $query->orderByRaw( 'LOWER("' . $field . '") ' . $direction );
                    }
                }
                info( $query->toRawSql() );
                return $query->paginate( $perPage , [ '*' ] , 'page' , $page );

            } catch ( \Exception $exception ) {
                return response( [
                    'status'  => FALSE ,
                    'message' => $exception->getMessage() ,
                ] , 422 );
            }
        }

        private function applyFilterOperator($q , $field , $operator , $variant , $value)
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
            $localKey         = $relationInstance->getLocalKeyName();
            $foreignKey       = $relationInstance->getForeignKeyName();

            $query->leftJoin(
                $relatedTable ,
                $relationInstance->getParent()->getTable() . '.' . $localKey ,
                '=' ,
                $relatedTable . '.' . $foreignKey
            )
                  ->orderBy( $relatedTable . '.' . $column , $direction )
                  ->select( $query->getModel()->getTable() . '.*' );
        }
    }

