<?php

    namespace App\Exports;

    use App\Http\Requests\PaginateRequest;
    use App\Libraries\AppLibrary;
    use App\Services\StockService;
    use Maatwebsite\Excel\Concerns\FromCollection;
    use Maatwebsite\Excel\Concerns\WithHeadings;

    class StockExpiryExport implements FromCollection , WithHeadings
    {

        public StockService    $stockService;
        public PaginateRequest $request;

        public function __construct(StockService $stockService , $request)
        {
            $this->stockService = $stockService;
            $this->request      = $request;
        }

        /**
         * @return \Illuminate\Support\Collection
         * @throws \Exception
         */
        public function collection()
        {
            $stockArray  = [];
            $stocksArray = collect($this->stockService->expiryList($this->request));
            foreach ( $stocksArray as $stock ) {
                $days         = now()->diffInDays($stock->expiry_date , false);
                $text         = ( $days <= 30 && $days > 0 ) ? 'Expiring Soon' : ( $days > 30 ? 'Ok' : 'Expired' );
                $stockArray[] = [
                    'product_name' => $stock->product->name ,
                    'expiry_date'  => AppLibrary::datetime2($stock->expiry_date) ,
                    'quantity'     => number_format($stock->quantity) . ' ' . $stock->product->unit->code ,
                    'location'     => $stock->warehouse->name ,
                    'days_left'    => "$days days" ,
                    'text'         => $text ,
                ];
            }
            return collect($stockArray);
        }

        public function headings() : array
        {
            return [
                "Product Name" , "Expiry Date" , "Quantity" , "Location" , "Days Left" , "Status"
            ];
        }
    }
