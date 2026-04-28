@php
    $primaryColor = "#1d4ed8";
    $primaryLight = "#eff6ff";
    $secondaryColor = "#475569";
    $secondaryLight = "#f8fafc";
@endphp

        <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>{{ $label ?? 'Document' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --primary-color: {{ $primaryColor }};
            --primary-light: {{ $primaryLight }};
            --secondary-color: {{ $secondaryColor }};
            --secondary-light: {{ $secondaryLight }};
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f3f4f6;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
        }

        .invoice-container {
            width: 210mm;
            height: 297mm;
            background: white;
            padding: 12mm;
            box-sizing: border-box;
            position: relative;
            overflow: hidden;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        .primary-text {
            color: var(--primary-color);
        }

        .secondary-text {
            color: var(--secondary-color);
        }

        .primary-light-bg {
            background-color: var(--primary-light);
        }

        .secondary-light-bg {
            background-color: var(--secondary-light);
        }

        .print-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10pt;
        }

        .print-table th {
            background-color: var(--primary-color) !important;
            color: white !important;
            text-align: left;
            padding: 10px;
            -webkit-print-color-adjust: exact;
        }

        .print-table td {
            padding: 10px;
            border-bottom: 1px solid #e5e7eb;
        }

        .a4-footer {
            position: absolute;
            bottom: 12mm;
            left: 12mm;
            right: 12mm;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }

            .invoice-container {
                box-shadow: none;
                border: none;
                margin: 0;
            }

            @page {
                size: A4;
                margin: 0;
            }
        }
    </style>
</head>
<body>

<div class="invoice-container">
    <div class="flex justify-between items-start mb-8">
        @if($logo)
            <img src="{{ $logo }}" alt="logo" style="max-height: 60px;">
        @else
            <div class="h-[60px] w-20 bg-gray-100 flex items-center justify-center text-[10px] text-gray-400">No Logo</div>
        @endif
        <div class="text-right">
            <h1 class="text-3xl font-black primary-text">{{ $label ?? 'INVOICE' }}</h1>
            <p class="text-gray-500 font-bold">#{{ $order->order_serial_no ?? 'N/A' }}</p>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-8 mb-8 text-sm">
        <div>
            <h2 class="font-bold text-lg uppercase mb-1">{{ $company->company_name ?? 'Our Company' }}</h2>
            <p class="secondary-text">{{ $company->company_address ?? 'Address not set' }}</p>
            <p class="secondary-text">Phone: {{ $company->company_phone ?? 'N/A' }}</p>
        </div>
        <div class="text-right flex flex-col items-end">
            <div class="space-y-1">
                <p><strong>Date:</strong> {{ isset($order->order_datetime) ? date('Y-m-d H:i', strtotime($order->order_datetime)) : 'N/A' }}</p>
                <p><strong>Due Date:</strong> {{ isset($order->due_date) ? date('Y-m-d H:i', strtotime($order->due_date)) : 'N/A' }}</p>
            </div>
            <div class="mt-2 px-4 py-2 rounded-lg font-bold border-2 border-red-200 text-red-600 bg-red-50">
                Balance Due: {{ number_format($order->balance ?? 0) }} UGX
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4 mb-8 text-sm">
        <div class="p-4 rounded-xl border border-gray-100 secondary-light-bg">
            <h3 class="font-bold primary-text mb-2 uppercase text-xs">Bill To:</h3>
            <p class="font-bold text-gray-800">{{ $order->user->name ?? 'Guest Customer' }}</p>
            <p class="secondary-text">{{ $order->user->email ?? '' }}</p>
            <p class="secondary-text">{{ $order->user->phone ?? '' }}</p>
        </div>
        <div class="p-4 rounded-xl border border-gray-100">
            <h3 class="font-bold primary-text mb-2 uppercase text-xs">Ship To:</h3>
            <p class="secondary-text">{{ $order->shipping_address ?? 'No shipping address provided.' }}</p>
        </div>
    </div>

    <div class="mb-8">
        <table class="print-table">
            <thead>
            <tr>
                <th>Item Description</th>
                <th class="text-center">Qty</th>
                <th class="text-right">Price</th>
                <th class="text-right">Total</th>
            </tr>
            </thead>
            <tbody>
            @forelse(($order->orderProducts ?? []) as $item)
                <tr>
                    <td class="font-medium">{{ $item->item->name ?? 'Unknown Product' }}</td>
                    <td class="text-center">{{ $item->quantity ?? 0 }}</td>
                    <td class="text-right">{{ number_format($item->unit_price ?? 0) }}</td>
                    <td class="text-right font-bold">{{ number_format($item->total ?? 0) }}</td>
                </tr>
            @empty
                {{-- No product items --}}
            @endforelse
            @forelse(($order->orderServiceProducts ?? []) as $item)
                <tr>
                    <td class="font-medium">
                        {{ $item->service->name ?? 'Unknown Service' }}

                        {{-- Add Tier --}}
                        @if(!empty($item->tier))
                            ({{ $item->tier->serviceTier->name ?? '' }})
                        @endif

                        {{-- Add Addons --}}
                        @if(!empty($item->addons) && count($item->addons) > 0)
                            + {{ collect($item->addons)->pluck('addon.name')->filter()->implode(', ') }}
                        @endif
                    </td>
                    <td class="text-center">{{ $item->quantity ?? 0 }}</td>
                    <td class="text-right">{{ number_format($item->unit_price ?? 0) }}</td>
                    <td class="text-right font-bold">{{ number_format($item->total ?? 0) }}</td>
                </tr>
            @empty
                {{-- No service items --}}
            @endforelse
            @if(count($order->orderProducts) == 0 && count($order->orderServiceProducts) == 0)
                <tr>
                    <td colspan="4" class="text-center py-4 text-gray-400">No items found.</td>
                </tr>
            @endif
            </tbody>
        </table>
    </div>

    <div class="grid grid-cols-2 gap-8">
        <div>
            @if(isset($paymentMethods) && count($paymentMethods) > 0)
                <div class="p-4 rounded-xl primary-light-bg border border-blue-100">
                    <h3 class="font-bold primary-text text-xs uppercase mb-3">Payment Methods</h3>
                    @foreach($paymentMethods as $method)
                        @if(!\Illuminate\Support\Str::contains($method->name,'Wallet'))
                            <div class="flex justify-between text-xs mb-1 last:mb-0">
                                <span class="font-semibold text-gray-700">{{ $method->name ?? 'N/A' }}</span>
                                <span class="secondary-text">Code: {{ $method->merchant_code ?? '-' }}</span>
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif
            @if($order->pos_payment_note ?? FALSE)
                <div class="mt-4 text-[10px] italic text-gray-400">
                    <strong>Note:</strong> {{ $order->pos_payment_note }}
                </div>
            @endif
        </div>

        <div class="space-y-2 text-sm">
            <div class="flex justify-between px-2">
                <span class="secondary-text">Subtotal</span>
                <span>{{ number_format($order->subtotal ?? 0) }} UGX</span>
            </div>
            <div class="flex justify-between px-2">
                <span class="secondary-text">Tax</span>
                <span>{{ number_format($order->tax ?? 0) }} UGX</span>
            </div>
            <div class="flex justify-between px-2 py-2 primary-light-bg rounded-lg font-bold text-lg primary-text">
                <span>Total</span>
                <span>{{ number_format($order->total ?? 0) }} UGX</span>
            </div>
            <div class="flex justify-between px-2 text-green-600 font-medium">
                <span>Paid</span>
                <span>- {{ number_format($order->paid ?? 0) }} UGX</span>
            </div>
            <div class="flex justify-between px-2 pt-2 border-t border-dashed border-gray-300 font-black text-red-600">
                <span>Balance Due</span>
                <span>{{ number_format($order->balance ?? 0) }} UGX</span>
            </div>
        </div>
    </div>

    <div class="a4-footer">
        <div class="text-[10px] text-center text-gray-400">
            {{ $label ?? 'Document' }} from {{ $company->company_name ?? 'Our Company' }} | Generated by Smart Duuka Business software |
            <span class="primary-text font-bold">smartduuka.com</span>
        </div>
    </div>
</div>

</body>
</html>