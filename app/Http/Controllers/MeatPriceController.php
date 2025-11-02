<?php

    namespace App\Http\Controllers;

    use App\Http\Requests\StoreMeatPriceRequest;
    use App\Http\Requests\UpdateMeatPriceRequest;
    use App\Http\Resources\MeatPriceResource;
    use App\Models\MeatPrice;
    use Exception;
    use Illuminate\Support\Facades\Log;
    use Smartisan\Settings\Facades\Settings;

    class MeatPriceController extends Controller
    {
        public function index()
        {
            try {
                return new MeatPriceResource(MeatPrice::first());
            } catch ( Exception $exception ) {
                return response([ 'status' => false , 'message' => $exception->getMessage() ] , 422);
            }
        }

        public function store(StoreMeatPriceRequest $request)
        {
            //
        }

        public function show(MeatPrice $meatPrice)
        {
            //
        }

//    public function update(UpdateMeatPriceRequest $request, MeatPrice $meatPrice)
//    {
//        //
//    }
        public function update(UpdateMeatPriceRequest $request)
        {
            try {
                $meatPrice = MeatPrice::find(1);
                if ( ! $meatPrice ) {
                    MeatPrice::create($request->validated());
                } else {
                    $meatPrice->update($request->validated());
                }
                return new MeatPriceResource(MeatPrice::first());
            } catch ( Exception $exception ) {
                return response([ 'status' => false , 'message' => $exception->getMessage() ] , 422);
            }
        }

        /**
         * @throws Exception
         */
        public function list()
        {
            try {
                return Settings::group('meatPrices')->all();
            } catch ( Exception $exception ) {
                Log::info($exception->getMessage());
                throw new Exception($exception->getMessage() , 422);
            }
        }

        public function destroy(MeatPrice $meatPrice)
        {
            //
        }
    }
