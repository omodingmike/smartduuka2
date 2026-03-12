<?php

    namespace App\Helpers\printing;

    use App\Enums\PrintJobType;
    use Illuminate\Support\Str;

    class PrintJobPayload
    {
        public function __construct(
            public readonly string $jobId ,
            public readonly PrintJobType $type ,
            public readonly string $printerName ,
            public readonly ?string $data = NULL ,
            public readonly ?string $htmlContent = NULL ,
        ) {}

        public static function createRaw(string $printerName , string $base64Data) : self
        {
            return new self(
                jobId: 'job_' . Str::ulid() ,
                type: PrintJobType::RAW ,
                printerName: $printerName ,
                data: $base64Data
            );
        }

        public static function createHtml(string $printerName , string $htmlContent) : self
        {
            return new self(
                jobId: 'job_' . Str::ulid() ,
                type: PrintJobType::HTML ,
                printerName: $printerName ,
                htmlContent: $htmlContent
            );
        }

        public function toArray() : array
        {
            return array_filter( [
                'jobId'       => $this->jobId ,
                'type'        => $this->type->value ,
                'printerName' => $this->printerName ,
                'data'        => $this->data ,
                'htmlContent' => $this->htmlContent ,
                'timestamp'   => now()->toIso8601String() ,
            ] );
        }
    }