<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profit Loss Report</title>
    <style>
        @page { size: A4 landscape; margin: 12mm; }
        body { color: #111827; font-family: DejaVu Sans, sans-serif; font-size: 11px; }
        h1 { font-size: 20px; margin: 0 0 4px; }
        .period { color: #6b7280; margin: 0 0 18px; }
        .summary { margin-bottom: 18px; width: 100%; }
        .summary td { border: 1px solid #e5e7eb; padding: 10px; width: 25%; }
        .label { color: #6b7280; font-size: 10px; margin-bottom: 4px; }
        .amount { font-size: 16px; font-weight: bold; }
        .sales { color: #2563eb; }
        .cost { color: #dc2626; }
        .profit { color: #047857; }
        table.report { border-collapse: collapse; width: 100%; }
        .report th, .report td { border: 1px solid #e5e7eb; padding: 8px; }
        .report th { background: #f3f4f6; text-align: left; }
        .report .center { text-align: center; }
        .report .right { text-align: right; }
        .report tfoot td { background: #f9fafb; font-weight: bold; }
    </style>
</head>
<body>
    @php($currency = \App\Support\Currency::current())
    <h1>Profit Loss Report</h1>
    <p class="period">
        {{ $filters['start_date'] ? \Illuminate\Support\Carbon::parse($filters['start_date'])->format('d M Y') : 'Beginning' }}
        to
        {{ $filters['end_date'] ? \Illuminate\Support\Carbon::parse($filters['end_date'])->format('d M Y') : 'Today' }}
    </p>

    <table class="summary">
        <tr>
            <td><div class="label">Total Sales</div><div class="amount sales">{{ \App\Support\Currency::format($summary['total_amount'], $currency) }}</div></td>
            <td><div class="label">Product Cost</div><div class="amount cost">{{ \App\Support\Currency::format($summary['cost_amount'], $currency) }}</div></td>
            <td><div class="label">Product Profit</div><div class="amount">{{ \App\Support\Currency::format($summary['product_profit'], $currency) }}</div></td>
            <td><div class="label">Net Profit</div><div class="amount profit">{{ \App\Support\Currency::format($summary['profit_amount'], $currency) }}</div></td>
        </tr>
    </table>

    <table class="report">
        <thead>
            <tr>
                <th>SL</th><th>Order</th><th class="center">Date</th><th class="center">Sales</th>
                <th class="center">Discount</th><th class="center">Cost</th><th class="center">Delivery</th><th class="right">Profit / Loss</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($orders as $index => $order)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $order['order_number'] }}<br><span style="color:#6b7280;font-size:9px">{{ $order['customer_name'] ?: 'No customer' }}</span></td>
                    <td class="center">{{ $order['order_date'] ? \Illuminate\Support\Carbon::parse($order['order_date'])->format('d M Y') : 'No date' }}</td>
                    <td class="center sales">{{ \App\Support\Currency::format($order['total_amount'], $currency) }}</td>
                    <td class="center">{{ \App\Support\Currency::format($order['discount_amount'], $currency) }}</td>
                    <td class="center cost">{{ \App\Support\Currency::format($order['cost_amount'], $currency) }}</td>
                    <td class="center">{{ \App\Support\Currency::format($order['delivery_charge'], $currency) }}</td>
                    <td class="right {{ $order['profit_amount'] < 0 ? 'cost' : 'profit' }}">{{ \App\Support\Currency::format($order['profit_amount'], $currency) }}</td>
                </tr>
            @empty
                <tr><td colspan="8" class="center">No profit loss records found.</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3">{{ $summary['order_count'] }} order(s)</td>
                <td class="center sales">{{ \App\Support\Currency::format($summary['total_amount'], $currency) }}</td>
                <td class="center">{{ \App\Support\Currency::format($summary['discount_amount'], $currency) }}</td>
                <td class="center cost">{{ \App\Support\Currency::format($summary['cost_amount'], $currency) }}</td>
                <td class="center">{{ \App\Support\Currency::format($summary['delivery_charge'], $currency) }}</td>
                <td class="right">{{ \App\Support\Currency::format($summary['profit_amount'], $currency) }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
