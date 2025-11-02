@php use App\Libraries\AppLibrary; @endphp
    <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>{{$label}}</title>

    <!-- TailwindCSS CDN -->
    <script src="{{asset('js/pdf/tailwind.js')}}"></script>
    <style>
        :root {
            --primary-color: {{$primaryColor}};
            --primary-light: {{$primaryLight}};
            --secondary-color: {{$secondaryColor}};
            --secondary-light: {{$secondaryLight}};
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f9fafb;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        /* A4 Dimensions: 210mm × 297mm */
        .invoice-container {
            width: 210mm;
            min-height: 297mm;
            background: white;
            padding: 15mm;
            box-sizing: border-box;
            margin: 0 auto;
            position: relative;
            border: 1px solid #e2e8f0;
        }

        .table-header-rounded {
            border-top-left-radius: 0.25rem;
            border-top-right-radius: 0.25rem;
        }

        .color-preview {
            width: 30px;
            height: 30px;
            border-radius: 4px;
            display: inline-block;
            margin: 0 5px;
            border: 1px solid #ddd;
        }

        .color-controls {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            padding: 20px;
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 100;
            width: 300px;
            border: 2px solid var(--secondary-color);
        }

        .preset-colors {
            display: flex;
            gap: 10px;
            margin-top: 10px;
            flex-wrap: wrap;
        }

        .preset-color {
            width: 25px;
            height: 25px;
            border-radius: 4px;
            cursor: pointer;
            border: 2px solid transparent;
            transition: transform 0.2s;
        }

        .preset-color:hover {
            transform: scale(1.1);
        }

        .preset-color.active {
            border-color: #000;
            transform: scale(1.1);
        }

        .primary-text {
            color: var(--primary-color);
        }

        .primary-bg {
            background-color: var(--primary-color);
        }

        .primary-light-bg {
            background-color: var(--primary-light);
        }

        .secondary-text {
            color: var(--secondary-color);
        }

        .secondary-bg {
            background-color: var(--secondary-color);
        }

        .btn {
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .color-input {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }

        .color-input input[type="color"] {
            width: 60px;
            height: 40px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            padding: 0;
        }

        .color-option {
            margin-bottom: 20px;
        }

        .action-buttons {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 15px;
            margin-top: 30px;
        }

        .action-btn {
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .print-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10pt;
        }

        .print-table th {
            background-color: var(--primary-color);
            color: white;
            text-align: left;
            padding: 8px 10px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            /* Ensure background prints in all browsers */
            -webkit-print-color-adjust: exact;
            color-adjust: exact;
        }

        .print-table td {
            padding: 8px 10px;
            border: 1px solid #e2e8f0;
        }

        .summary-box {
            border: 1px solid #e2e8f0;
            background-color: #f8fafc;
            padding: 15px;
            border-radius: 8px;
        }

        .signature-area {
            border-bottom: 1px dashed #cbd5e1;
            height: 60px;
            margin-top: 10px;
        }

        /* Print-specific styles - FIXED TO MATCH PREVIEW */
        @media print {
            @page {
                size: A4;
                margin: 0;
            }

            body {
                padding: 0;
                margin: 0;
                background: white;
                font-size: 10pt;
                zoom: 1; /* Prevent scaling issues */
                transform: scale(1); /* Ensure no transformation */
            }

            .no-print, .color-controls {
                display: none !important;
            }

            .invoice-container {
                width: 210mm;
                min-height: 297mm;
                padding: 15mm;
                margin: 0;
                box-shadow: none;
                border: none;
                page-break-after: always;
                /* Prevent any layout shifts */
                display: block !important;
                position: static !important;
            }

            /* Force fixed layout for all elements */
            * {
                float: none !important;
                position: static !important;
                overflow: visible !important;
            }

            .action-buttons {
                display: none;
            }

            .print-table {
                width: 100%;
                /* Prevent table from breaking across pages */
                page-break-inside: avoid;
            }

            /* Force background colors to print */
            .print-table th {
                -webkit-print-color-adjust: exact !important;
                color-adjust: exact !important;
                background-color: var(--primary-color) !important;
                color: white !important;
            }

            /* Fix for grid layouts in print */
            .grid {
                display: grid !important;
            }

            /* Ensure all elements maintain their screen size */
            .invoice-container > * {
                width: 100% !important;
                max-width: 100% !important;
                min-width: 0 !important;
            }

            /* Prevent flexbox from causing layout shifts */
            .flex {
                display: flex !important;
            }

            /* Specifically fix the header layout */
            .flex.flex-col.md\:flex-row {
                display: flex !important;
                flex-direction: column;
            }

            @media (min-width: 768px) {
                .flex.flex-col.md\:flex-row {
                    flex-direction: row !important;
                }
            }
        }

        /* Screen-specific adjustments */
        @media screen {
            .invoice-container {
                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            }
        }

        .page-break {
            page-break-before: always;
        }

        .a4-footer {
            position: absolute;
            bottom: 15mm;
            left: 15mm;
            right: 15mm;
        }

        /* FIX FOR PRINT LAYOUT */
        .secondary-light {
            background-color: var(--secondary-light);
        }

        /* Ensure consistent layout */
        .invoice-container > div {
            position: relative;
        }
    </style>
</head>

<body class="bg-gray-50 min-h-screen p-4 md:p-6">
<!-- Invoice Container - A4 Size -->
<div class="invoice-container" id="invoiceContent">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start mb-6">
        <img src="{{$logo}}" alt="logo" style="width: 120px; height: auto;" class="mb-2">
        <div class="text-left md:text-right">
            <h1 class="text-2xl font-bold uppercase primary-text">{{$label}}</h1>
            <p class="text-gray-700 font-medium"># {{$order->order_serial_no}}</p>
        </div>
    </div>

    <!-- Company Info & Balance -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm mb-6">
        <div>
            <h2 class="font-bold text-lg mb-2 uppercase">{{$company->company_name}}</h2>
            <p class="secondary-text">{{$company->company_address}}</p>
            @if($company->company_email)
                <p class="secondary-text">Email: {{$company->company_email}}</p>
            @endif
            <p class="secondary-text">{{$company->company_calling_code}} {{ $company->company_phone }}</p>
        </div>
        <div class="text-left md:text-right space-y-1">
            <p class="secondary-text"><strong>Date:</strong> {{ AppLibrary::date($order->order_datetime) }}</p>
            {{-- <p class="secondary-text"><strong>Due Date:</strong> {{AppLibrary::date($order->due_date) }}</p> --}}
            <div class="secondary-light p-3 rounded-md font-bold text-lg border border-gray-300 mt-2">
                <p class="text-lg">Balance Due: {{ AppLibrary::currencyAmountFormat($balance) }}</p>
            </div>
        </div>
    </div>

    <!-- Addresses -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6 text-sm">
        <div class="border border-gray-200 p-3 rounded-md">
            <h3 class="font-bold primary-text text-lg mb-2">Bill To:</h3>
            <p>{{ $order->user->name }}</p>
            @if($order->user?->email)
                <p class="secondary-text">{{ $order->user->email }}</p>
            @endif
            <p class="secondary-text">{{ $order->user->country_code }} {{ $order->user->phone }}</p>
        </div>
        <div class="border border-gray-200 p-3 rounded-md">
            @php
                $addresses = $order->user->addresses ?? [];
                $shipAddresses = collect($addresses)
                    ->take(2)
                    ->pluck('address')
                    ->filter();
            @endphp

            @if($shipAddresses->count())
                <h3 class="font-bold primary-text text-lg mb-2">Ship To:</h3>
                @foreach($shipAddresses as $address)
                    <p>{{ $address }}</p>
                @endforeach
            @else
                <h3 class="font-bold primary-text text-lg mb-2">Ship To:</h3>
                <p class="secondary-text">No shipping address provided.</p>
            @endif
        </div>
    </div>

    <!-- Items Table -->
    <div class="mb-6">
        @if($order->orderProducts)
            <table class="print-table">
                <thead class="text-white table-header-rounded">
                <tr>
                    <th>Item</th>
                    <th>Quantity</th>
                    <th>Rate</th>
                    <th>Amount</th>
                </tr>
                </thead>
                <tbody>
                @foreach($order->orderProducts as $stock)
                    <tr>
                        <td>{{ $stock->product->name }}</td>
                        <td class="secondary-text">{{ abs($stock->quantity) }}</td>
                        <td class="secondary-text">{{ AppLibrary::currencyAmountFormat($stock->price) }}</td>
                        <td class="font-medium">{{ AppLibrary::currencyAmountFormat($stock->total) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <!-- Payment Methods and Summary -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <!-- Payment Methods -->
        <div class="rounded-lg p-3 text-sm border primary-light-bg">
            <h3 class="font-semibold mb-3 text-lg primary-text">Payment Methods:</h3>
            <div class="grid grid-cols-1 gap-3">
                @foreach($paymentMethods as $paymentMethod)
                    <div class="bg-white p-3 rounded-lg border border-gray-200">
                        <h4 class="font-bold text-gray-800">{{ $paymentMethod->name }}</h4>
                        <p class="text-sm secondary-text">{{ $paymentMethod->merchant_code }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Summary -->
        <div class="summary-box">
            <h3 class="font-bold text-lg mb-3 text-center primary-text">Payment Summary</h3>
            <div class="space-y-2">
                <div class="flex justify-between border-b border-gray-300 pb-1">
                    <span class="secondary-text">Subtotal:</span>
                    <span class="font-medium">{{ AppLibrary::currencyAmountFormat($order->subtotal) }}</span>
                </div>
                <div class="flex justify-between border-b border-gray-300 pb-1">
                    <span class="secondary-text">Delivery:</span>
                    <span class="font-medium">{{ AppLibrary::currencyAmountFormat($order->shipping_charge) }}</span>
                </div>
                <div class="flex justify-between border-b border-gray-300 pb-1">
                    <span class="secondary-text">Discount:</span>
                    <span class="text-green-600 font-medium">{{ AppLibrary::currencyAmountFormat($order->discount) }}</span>
                </div>
                <div class="flex justify-between border-b border-gray-300 pb-1">
                    <span class="secondary-text">Tax:</span>
                    <span class="font-medium">{{ AppLibrary::currencyAmountFormat($order->tax) }}</span>
                </div>
                <div class="flex justify-between font-bold mt-3 pt-1 border-t border-gray-400">
                    <span class="primary-text">Overall Total:</span>
                    <span class="primary-text">{{ AppLibrary::currencyAmountFormat($order->total) }}</span>
                </div>
                <div class="flex justify-between font-bold mt-2 pt-1 border-t border-gray-400">
                    <span class="secondary-text">Amount Paid:</span>
                    <span class="text-green-600">{{ AppLibrary::currencyAmountFormat($order->paid) }}</span>
                </div>
                @if($order->change > 0)
                    <div class="flex justify-between font-bold mt-2 pt-1 border-t border-gray-400">
                        <span class="secondary-text">Change:</span>
                        <span class="text-green-600">{{ AppLibrary::currencyAmountFormat($order->change) }}</span>
                    </div>
                @endif
                <div class="flex justify-between font-bold mt-2 pt-1 border-t border-gray-400">
                    <span class="secondary-text">Balance Due:</span>
                    <span class="text-red-600">{{ AppLibrary::currencyAmountFormat($balance) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Notes & Terms -->
    @if($order->pos_payment_note)
        <div class="mb-6 text-sm border border-gray-200 p-3 rounded-md primary-light-bg">
            <p><strong class="primary-text">Notes:</strong><br></p>
            <div class="secondary-text">{!! $order->pos_payment_note !!}</div>
        </div>
    @endif

    <!-- Footer -->
    <div class="a4-footer">
        <div class="text-xs text-center secondary-text">
            {{$label}} from {{ $company->company_name }}, Generated by Smart Duuka Business software |
            <a href="https://smartduuka.com" class="primary-text hover:underline">smartduuka.com</a>
        </div>
    </div>
</div>
</body>

</html>
