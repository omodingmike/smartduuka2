<?php

    namespace App\Services;


    use App\Enums\Status;
    use App\Http\Requests\ChangeImageRequest;
    use App\Http\Requests\OfferRequest;
    use App\Http\Requests\PaginateRequest;
    use App\Models\Offer;
    use Carbon\Carbon;
    use Exception;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Log;
    use Illuminate\Support\Str;

    class OfferService
    {
        public    $offer;
        protected $offerFilter = [
            'name' ,
            'amount' ,
            'status' ,
            'start_date' ,
            'end_date' ,
        ];

        protected $exceptFilter = [
            'excepts'
        ];

        /**
         * @throws Exception
         */
        public function list(PaginateRequest $request)
        {
            try {
                $requests    = $request->all();
                $method      = $request->get('paginate' , 0) == 1 ? 'paginate' : 'get';
                $methodValue = $request->get('paginate' , 0) == 1 ? $request->get('per_page' , 10) : '*';
                $orderColumn = $request->get('order_column') ?? 'id';
                $orderType   = $request->get('order_type') ?? 'desc';
                $limit       = $request->get('limit') ? $request->get('limit') : '';

                return Offer::with('items')->where(function ($query) use ($requests) {
                    foreach ( $requests as $key => $request ) {
                        if ( in_array($key , $this->offerFilter) ) {
                            if ( $key == "start_date" ) {
                                $start_date = Date('Y-m-d' , strtotime($request));
                                $query->whereDate($key , '>=' , $start_date);
                            } else if ( $key == "end_date" ) {
                                $end_date = Date('Y-m-d' , strtotime($request));
                                $query->whereDate($key , '<=' , $end_date);
                            } else {
                                $query->where($key , 'like' , '%' . $request . '%');
                            }
                        }

                        if ( in_array($key , $this->exceptFilter) ) {
                            $explodes = explode('|' , $request);
                            if ( is_array($explodes) ) {
                                foreach ( $explodes as $explode ) {
                                    $query->where('id' , '!=' , $explode);
                                }
                            }
                        }
                    }
                })->limit($limit)->orderBy($orderColumn , $orderType)->$method(
                    $methodValue
                );
            } catch ( Exception $exception ) {
                Log::info($exception->getMessage());
                throw new Exception($exception->getMessage() , 422);
            }
        }

        public function activeWise(PaginateRequest $request)
        {
            try {
                $requests    = $request->all();
                $method      = $request->get('paginate' , 0) == 1 ? 'paginate' : 'get';
                $methodValue = $request->get('paginate' , 0) == 1 ? $request->get('per_page' , 10) : '*';
                $orderColumn = $request->get('order_column') ?? 'id';
                $orderType   = $request->get('order_type') ?? 'desc';
                $limit       = $request->get('limit') ? $request->get('limit') : '';

                return Offer::with('items')->where('end_date' , '>=' , now()->toDateTimeString())->where(function ($query) use ($requests) {
                    foreach ( $requests as $key => $request ) {
                        if ( in_array($key , $this->offerFilter) ) {
                            $query->where($key , 'like' , '%' . $request . '%');
                        }

                        if ( in_array($key , $this->exceptFilter) ) {
                            $explodes = explode('|' , $request);
                            if ( is_array($explodes) ) {
                                foreach ( $explodes as $explode ) {
                                    $query->where('id' , '!=' , $explode);
                                }
                            }
                        }
                    }
                })->limit($limit)->orderBy($orderColumn , $orderType)->$method(
                    $methodValue
                );
            } catch ( Exception $exception ) {
                Log::info($exception->getMessage());
                throw new Exception($exception->getMessage() , 422);
            }
        }

        /**
         * @throws Exception
         */
        public function store(OfferRequest $request)
        {
            try {
                DB::transaction(function () use ($request) {
                    $this->offer = Offer::create([
                        'name'       => $request->name ,
                        'slug'       => Str::slug($request->name) ,
                        'amount'     => $request->amount ,
                        'start_date' => date('Y-m-d H:i:s' , strtotime($request->start_date)) ,
                        'end_date'   => date('Y-m-d H:i:s' , strtotime($request->end_date)) ,
                        'status'     => $request->status ,
                    ]);
                    activityLog("Created Offer: " . $request->name);
                    if ( $request->image ) {
                        $this->offer->addMedia($request->image)->toMediaCollection('offer');
                    }
                });
                return $this->offer;
            } catch ( Exception $exception ) {
                Log::info($exception->getMessage());
                DB::rollBack();
                throw new Exception($exception->getMessage() , 422);
            }
        }

        /**
         * @throws Exception
         */
        public function update(OfferRequest $request , Offer $offer)
        {
            try {
                DB::transaction(function () use ($request , $offer) {
                    $this->offer       = $offer;
                    $offer->name       = $request->name;
                    $offer->slug       = Str::slug($request->name);
                    $offer->amount     = $request->amount;
                    $offer->start_date = date('Y-m-d H:i:s' , strtotime($request->start_date));
                    $offer->end_date   = date('Y-m-d H:i:s' , strtotime($request->end_date));
                    $offer->status     = $request->status;
                    $offer->save();
                    activityLog("Updated Offer: " . $request->name);
                });

                if ( $request->image ) {
                    $this->offer->media()->delete();
                    $this->offer->addMedia($request->image)->toMediaCollection('offer');
                }
                return $this->offer;
            } catch ( Exception $exception ) {
                Log::info($exception->getMessage());
                DB::rollBack();
                throw new Exception($exception->getMessage() , 422);
            }
        }

        /**
         * @throws Exception
         */
        public function destroy(Offer $offer)
        {
            try {
                $offer->offerItems()->delete();
                $offer->delete();
                activityLog("Deleted Offer: " . $offer->name);
            } catch ( Exception $exception ) {
                Log::info($exception->getMessage());
                throw new Exception($exception->getMessage() , 422);
            }
        }

        /**
         * @throws Exception
         */
        public function show(Offer $offer) : Offer
        {
            try {
                return $offer;
            } catch ( Exception $exception ) {
                Log::info($exception->getMessage());
                throw new Exception($exception->getMessage() , 422);
            }
        }

        /**
         * @throws Exception
         */
        public function changeImage(ChangeImageRequest $request , Offer $offer) : Offer
        {
            try {
                if ( $request->image ) {
                    $offer->clearMediaCollection('offer');
                    $offer->addMedia($request->image)->toMediaCollection('offer');
                }
                activityLog("Changed Image for Offer: " . $offer->name);
                return $offer;
            } catch ( Exception $exception ) {
                Log::info($exception->getMessage());
                throw new Exception($exception->getMessage() , 422);
            }
        }

        /**
         * @throws Exception
         */
        public function offerItemByDate(Request $request) : \Illuminate\Database\Eloquent\Collection
        {
            try {
                return Offer::with('offerItems')->where([ 'status' => Status::ACTIVE ])->get()->filter(function ($offer) {
                    if ( Carbon::now()->between($offer->start_date , $offer->end_date) ) {
                        return $offer;
                    }
                });
            } catch ( Exception $exception ) {
                Log::info($exception->getMessage());
                throw new Exception($exception->getMessage() , 422);
            }
        }
    }
