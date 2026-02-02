<?php

    namespace App\Http\Controllers;

    use App\Http\Requests\PrinterRequest;
    use App\Http\Resources\PrinterResource;
    use App\Models\Printer;
    use Illuminate\Http\Request;

    class PrinterController extends Controller
    {
        public function index()
        {
            return PrinterResource::collection( Printer::all() );
        }

        public function store(PrinterRequest $request)
        {
            return new PrinterResource( Printer::create( $request->validated() ) );
        }

        public function show(Printer $printer)
        {
            return new PrinterResource( $printer );
        }

        public function update(PrinterRequest $request , Printer $printer)
        {
            $printer->update( $request->validated() );

            return new PrinterResource( $printer );
        }

        public function destroy(Request $request)
        {
            Printer::destroy( $request->ids );

            return response()->json();
        }
    }
