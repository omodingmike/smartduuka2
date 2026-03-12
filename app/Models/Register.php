<?php

    namespace App\Models;

    use App\Enums\OrderStatus;
    use App\Enums\PreOrderStatus;
    use App\Enums\RegisterStatus;
    use Illuminate\Database\Eloquent\Builder;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;
    use Illuminate\Database\Eloquent\Relations\HasMany;

    class Register extends Model
    {
        protected $fillable = [
            'user_id' ,
            'opening_float' ,
            'status' ,
            'opened_at' ,
            'closed_at' ,
            'expected_float' ,
            'closing_float' ,
            'difference' ,
            'notes' ,
        ];

        public function posPayments() : HasMany | Register
        {
            return $this->hasMany( PosPayment::class , 'register_id' , 'id' )
                        ->whereHas( 'order' , function (Builder $q) {
                            $q->where( function ($query) {
                                $query->where( 'status' , '<>' , OrderStatus::CANCELED )
                                      ->orWhereNull( 'status' );
                            } )->where( function ($query) {
                                $query->whereNotIn( 'pre_order_status' , [ PreOrderStatus::REFUNDED , PreOrderStatus::CANCELED ] )
                                      ->orWhereNull( 'pre_order_status' );
                            } );
                        } );
        }

        public function getExpectedFloatAttribute()
        {
            return $this->opening_float + $this->posPayments()->sum( 'amount' );
        }

        public function orders() : HasMany | Register
        {
            return $this->hasMany( Order::class , 'register_id' , 'id' )
                        ->where( function ($query) {
                            $query->where( 'status' , '<>' , OrderStatus::CANCELED )
                                  ->orWhereNull( 'status' );
                        } )->where( function ($query) {
                    $query->whereNotIn( 'pre_order_status' , [ PreOrderStatus::REFUNDED , PreOrderStatus::CANCELED ] )
                          ->orWhereNull( 'pre_order_status' );
                } );
        }

        public function expensesPayments() : HasMany | ExpensePayment
        {
            return $this->hasMany( ExpensePayment::class , 'register_id' , 'id' );
        }

        public function expenses() : HasMany | ExpensePayment
        {
            return $this->hasMany( Expense::class , 'register_id' , 'id' );
        }

        public function user() : BelongsTo
        {
            return $this->belongsTo( User::class );
        }

        protected function casts() : array
        {
            return [
                'closed_at' => 'datetime' ,
                'status'    => RegisterStatus::class
            ];
        }
    }
