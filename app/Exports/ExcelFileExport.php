<?php

    namespace App\Exports;

    use App\Http\Requests\PaginateRequest;
    use Illuminate\Support\Collection;
    use Maatwebsite\Excel\Concerns\FromCollection;
    use Maatwebsite\Excel\Concerns\WithHeadings;

    class ExcelFileExport implements FromCollection, WithHeadings
    {
        public PaginateRequest $request;

        public function __construct(public Collection $collection, public array $headings) {}

        public function collection() : Collection
        {
            return $this -> collection;
        }

        public function headings() : array
        {
            return $this -> headings;
        }
    }
