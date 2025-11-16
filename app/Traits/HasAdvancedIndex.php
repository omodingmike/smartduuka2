<?php

    namespace App\Traits;

    use Carbon\Carbon;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Http\Request;

    trait HasAdvancedIndex
    {

        public function filter(
            Model $model ,
            Request $request ,
            array $searchFields = []
        )
        {
            try {
                $filters      = $request->input( 'filters' ) ? json_decode( $request->input( 'filters' ) , TRUE ) : [];
                $sorts        = $request->input( 'sort' ) ? json_decode( $request->input( 'sort' ) , TRUE ) : [];
                $page         = $request->input( 'page' , 1 );
                $perPage      = $request->input( 'perPage' , 10 );
                $joinOperator = $request->input( 'joinOperator' , 'and' );

                $query = $model::query();

                foreach ( $searchFields as $field ) {
                    if ( $request->filled( $field ) ) {
                        $query->where( $field , 'ILIKE' , '%' . $request->input( $field ) . '%' );
                    }
                }

                $query->where( function ($q) use ($filters , $joinOperator) {
                    foreach ( $filters as $filter ) {
                        $field    = $filter[ 'id' ];
                        $value    = $filter[ 'value' ] ?? NULL;
                        $operator = $filter[ 'operator' ] ?? 'eq';
                        $variant  = $filter[ 'variant' ] ?? NULL;

                        if ( $value === '' || $value === NULL || ( is_array( $value ) && empty( array_filter( $value ) ) ) ) {
                            continue;
                        }

                        $method = $joinOperator === 'or' ? 'orWhere' : 'where';

                        if ( $variant === 'dateRange' ) {
                            $q->$method( function ($subQ) use ($field , $operator , $value) {
                                $handleDate = fn($v) => is_array( $v )
                                    ? Carbon::createFromTimestampMs( $v[ 0 ] )
                                    : Carbon::createFromTimestampMs( $v );
                                $date       = $handleDate( $value );

                                switch ( $operator ) {
                                    case 'eq':
                                        $subQ->where( $field , '>=' , $date->startOfDay() )
                                             ->where( $field , '<=' , $date->endOfDay() );
                                        break;
                                    case 'ne':
                                        $subQ->where( $field , '<' , $date->startOfDay() )
                                             ->orWhere( $field , '>' , $date->endOfDay() );
                                        break;
                                    case 'isBetween':
                                        if ( is_array( $value ) && count( $value ) === 2 ) {
                                            $start = Carbon::createFromTimestampMs( $value[ 0 ] )->startOfDay();
                                            $end   = Carbon::createFromTimestampMs( $value[ 1 ] )->endOfDay();
                                            $subQ->where( $field , '>=' , $start )
                                                 ->where( $field , '<=' , $end );
                                        }
                                        break;
                                }
                            } );
                            continue;
                        }

                        switch ( $operator ) {
                            case 'iLike':
                                $q->$method( $field , 'ILIKE' , '%' . $value . '%' );
                                break;
                            case 'notILike':
                                $q->$method( $field , 'NOT ILIKE' , '%' . $value . '%' );
                                break;
                            case 'eq':
                                $q->$method( $field , '=' , $value );
                                break;
                            case 'ne':
                                $q->$method( $field , '!=' , $value );
                                break;
                            case 'lt':
                                $q->$method( $field , '<' , $value );
                                break;
                            case 'lte':
                                $q->$method( $field , '<=' , $value );
                                break;
                            case 'gt':
                                $q->$method( $field , '>' , $value );
                                break;
                            case 'gte':
                                $q->$method( $field , '>=' , $value );
                                break;
                            case 'inArray':
                                $q->$method( fn($subQ) => $subQ->whereIn( $field , (array) $value ) );
                                break;
                            case 'notInArray':
                                $q->$method( fn($subQ) => $subQ->whereNotIn( $field , (array) $value ) );
                                break;
                            case 'isEmpty':
                                $q->$method( $field , '=' , NULL );
                                break;
                            case 'isNotEmpty':
                                $q->$method( $field , '!=' , NULL );
                                break;
                        }
                    }
                } );

                foreach ( $sorts as $s ) {
                    $direction = ( ! empty( $s[ 'desc' ] ) && ( $s[ 'desc' ] === TRUE || $s[ 'desc' ] === 'true' ) ) ? 'desc' : 'asc';
                    $query->orderByRaw( 'LOWER("' . $s[ 'id' ] . '") ' . $direction );
                }

                if ( empty( $sorts ) && ! empty( $defaultSorts ) ) {
                    foreach ( $defaultSorts as $s ) {
                        $query->orderByRaw( 'LOWER("' . $s[ 'id' ] . '") ' . ( $s[ 'desc' ] ?? 'asc' ) );
                    }
                }
                elseif ( empty( $sorts ) ) {
                    $query->latest();
                }

                return $query->paginate( $perPage , [ '*' ] , 'page' , $page );

            } catch ( \Exception $exception ) {
                return response( [
                    'status'  => FALSE ,
                    'message' => $exception->getMessage() ,
                ] , 422 );
            }
        }
    }