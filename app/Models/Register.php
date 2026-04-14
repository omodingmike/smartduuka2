<?php

    namespace App\Models;

    use App\Enums\CustomerWalletTransactionType;
    use App\Enums\DefaultPaymentMethods;
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

        public function posPayments() : HasMany
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
            return $this->opening_float + $this->posPayments()
                                               ->whereHas( 'paymentMethod' , function ($query) {
                                                   $query->where( 'name' , '<>' , DefaultPaymentMethods::WALLET->value );
                                               } )->sum( 'amount' );
        }

        public function orders() : HasMany
        {
            return $this->hasMany( Order::class , 'register_id' , 'id' )->active();

//                        ->where( function ($query) {
//                            $query->where( 'status' , '<>' , OrderStatus::CANCELED )
//                                  ->orWhereNull( 'status' );
//                        } );
//                        ->where( function ($query) {
//                    $query->whereNotIn( 'pre_order_status' , [ PreOrderStatus::REFUNDED , PreOrderStatus::CANCELED ] )
//                          ->orWhereNull( 'pre_order_status' );
//                    $query->where( 'is_returned' , FALSE );
//                    $query->whereNotIn( 'return_status' , [ ReturnStatus::CANCELED->value , ReturnStatus::REJECTED->value ] );
//                } );
        }

        public function expensesPayments() : HasMany
        {
            return $this->hasMany( ExpensePayment::class , 'register_id' , 'id' );
        }

        public function walletTransactions() : HasMany
        {
            return $this->hasMany( CustomerWalletTransaction::class , 'register_id' , 'id' )
                        ->where( 'type' , CustomerWalletTransactionType::DEPOSIT );
        }

        public function expenses() : HasMany
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
