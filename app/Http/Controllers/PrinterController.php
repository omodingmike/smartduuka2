<?php

    namespace App\Http\Controllers;

    use App\Http\Requests\PrinterRequest;
    use App\Http\Resources\PrinterResource;
    use App\Http\Resources\PrinterTemplateResource;
    use App\Models\Printer;
    use App\Models\PrinterTemplate;
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

        public function templates()
        {
            return PrinterTemplateResource::collection( PrinterTemplate::all() );
        }

        public function assign(Request $request , Printer $printer)
        {
            $templates = json_decode( $request->templates , TRUE );
            $printer->templates()->sync( $templates );
            return new PrinterResource( $printer );
        }
    }
