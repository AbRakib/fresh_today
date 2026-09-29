<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <title>{{ $order->order_number }} PDF</title>
    <style>
        body { color: #111827; font-family: freesans, sans-serif; font-size: 12px; line-height: 1.45; }
        h1, h2, p { margin: 0; }
        .header { border-bottom: 2px solid #111827; margin-bottom: 22px; padding-bottom: 16px; width: 100%; }
        .header td { border: 0; padding: 0; vertical-align: top; }
        .brand { width: 58%; }
        .meta { width: 42%; }
        .brand h1 { font-size: 24px; margin-bottom: 6px; }
        .muted { color: #6b7280; }
        .meta { text-align: right; }
        .meta h2 { font-size: 22px; letter-spacing: 1px; margin-bottom: 6px; text-transform: uppercase; }
        .grid { margin-bottom: 20px; width: 100%; }
        .grid .box { border: 1px solid #e5e7eb; padding: 12px; vertical-align: top; width: 50%; }
        .grid .box + .box { border-left: 0; }
        .label { color: #6b7280; font-size: 10px; font-weight: bold; margin-bottom: 6px; text-transform: uppercase; }
        .strong { font-weight: bold; }
        table { border-collapse: collapse; width: 100%; }
        .items th, .items td { border: 1px solid #e5e7eb; padding: 8px; }
        .items th { background: #f3f4f6; font-size: 10px; text-align: left; text-transform: uppercase; }
        .center { text-align: center; }
        .right { text-align: right; }
        .totals { margin-left: auto; margin-top: 18px; width: 45%; }
        .totals td { border: 1px solid #e5e7eb; padding: 8px; }
        .totals .grand td { background: #111827; color: #ffffff; font-size: 14px; font-weight: bold; }
        .status { display: inline-block; border: 1px solid #d1d5db; border-radius: 3px; margin-top: 8px; padding: 3px 8px; }
        .note { border-top: 1px solid #e5e7eb; margin-top: 22px; padding-top: 12px; }
    </style>
</head>
<body>
    @php
        $currency = \App\Support\Currency::current();
        $formatDate = fn ($date) => $date ? \Illuminate\Support\Carbon::parse($date)->format('d M Y') : 'Not set';
        $paymentStatus = ['Unpaid', 'Paid', 'Partial'][$order->payment_status] ?? 'Unknown';
        $orderStatus = ['Pending', 'Processing', 'Delivered', 'Cancelled'][$order->order_status] ?? 'Unknown';
    @endphp

    <table class="header">
        <tr>
        <td class="brand">
            <h1>{{ $setting?->company_name ?: config('app.name') }}</h1>
            @if ($setting?->address)<p>{{ $setting->address }}</p>@endif
            @if ($setting?->phone)<p>{{ $setting->phone }}</p>@endif
            @if ($setting?->email)<p>{{ $setting->email }}</p>@endif
        </td>
        <td class="meta">
            <h2>Order</h2>
            <p class="strong">{{ $order->order_number }}</p>
            <p class="muted">Order date: {{ $formatDate($order->order_date) }}</p>
            <p class="muted">Delivery date: {{ $formatDate($order->delivery_date) }}</p>
            <p class="status">{{ $orderStatus }} / {{ $paymentStatus }}</p>
        </td>
        </tr>
    </table>

    <table class="grid">
        <tr>
        <td class="box">
            <div class="label">Customer</div>
            <p class="strong">{{ $order->customer?->name ?: 'Unknown customer' }}</p>
            @if ($order->customer?->phone)<p>{{ $order->customer->phone }}</p>@endif
            @if ($order->customer?->email)<p>{{ $order->customer->email }}</p>@endif
            @if ($order->customer?->address)<p>{{ $order->customer->address }}</p>@endif
        </td>
        <td class="box">
            <div class="label">Delivery</div>
            <p>{{ $order->delivery_address ?: $order->customer?->address ?: 'No delivery address' }}</p>
            @if ($order->creator?->name)<p class="muted">Created by: {{ $order->creator->name }}</p>@endif
        </td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th class="center">SL</th>
                <th>Product</th>
                <th class="center">Qty</th>
                <th class="right">Price</th>
                <th class="right">Discount</th>
                <th class="right">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($order->details as $index => $detail)
                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td>
                        <span class="strong">{{ $detail->product?->name ?: 'Unknown product' }}</span>
                        @if ($detail->product?->sku)<br><span class="muted">SKU: {{ $detail->product->sku }}</span>@endif
                    </td>
                    <td class="center">{{ $detail->order_qty }}</td>
                    <td class="right">{{ \App\Support\Currency::format($detail->sale_price, $currency) }}</td>
                    <td class="right">{{ \App\Support\Currency::format($detail->discount_amount, $currency) }}</td>
                    <td class="right">{{ \App\Support\Currency::format($detail->total_amount, $currency) }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="center">No order items found.</td></tr>
            @endforelse
        </tbody>
    </table>

    <table class="totals">
        <tr><td>Subtotal</td><td class="right">{{ \App\Support\Currency::format($order->subtotal, $currency) }}</td></tr>
        <tr><td>Discount</td><td class="right">{{ \App\Support\Currency::format($order->discount_amount, $currency) }}</td></tr>
        <tr><td>Delivery charge</td><td class="right">{{ \App\Support\Currency::format($order->delivery_charge, $currency) }}</td></tr>
        <tr class="grand"><td>Total</td><td class="right">{{ \App\Support\Currency::format($order->total_amount, $currency) }}</td></tr>
        <tr><td>Paid</td><td class="right">{{ \App\Support\Currency::format($order->paid_amount, $currency) }}</td></tr>
        <tr><td>Due</td><td class="right">{{ \App\Support\Currency::format($order->due_amount, $currency) }}</td></tr>
    </table>

    @if ($order->note)
        <div class="note">
            <div class="label">Note</div>
            <p>{{ $order->note }}</p>
        </div>
    @endif
</body>
</html>
