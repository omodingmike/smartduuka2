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
        return PrinterResource::collection( Printer::with('templates')->get() );
    }

    public function store(PrinterRequest $request)
    {
        return new PrinterResource( Printer::create( $request->validated() ) );
    }

    public function show(Printer $printer)
    {
        $printer->load('templates');
        return new PrinterResource( $printer );
    }

    public function update(PrinterRequest $request , Printer $printer)
    {
        $printer->update( $request->validated() );
        $printer->load('templates');
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
        // FIXED: Validate that templates is an array. No json_decode needed.
        $validated = $request->validate([
            'templates' => 'required|array',
            'templates.*' => 'integer|exists:printer_templates,id'
        ]);

        $printer->templates()->sync( $validated['templates'] );
        
        // Eager load the relationship so React gets the updated data immediately
        $printer->load('templates');
        
        return new PrinterResource( $printer );
    }
}