<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Stock Report</title>
    <style>
        @page { size: A4 landscape; margin: 12mm; }
        body { color: #111827; font-family: DejaVu Sans, sans-serif; font-size: 11px; }
        h1 { font-size: 20px; margin: 0 0 4px; }
        .period { color: #6b7280; margin: 0 0 18px; }
        .summary { margin-bottom: 18px; width: 100%; }
        .summary td { border: 1px solid #e5e7eb; padding: 10px; width: 25%; }
        .label { color: #6b7280; font-size: 10px; margin-bottom: 4px; }
        .amount { font-size: 16px; font-weight: bold; }
        .blue { color: #2563eb; }
        .amber { color: #d97706; }
        .green { color: #047857; }
        table.report { border-collapse: collapse; width: 100%; }
        .report th, .report td { border: 1px solid #e5e7eb; padding: 8px; }
        .report th { background: #f3f4f6; text-align: left; }
        .report .right { text-align: right; }
        .report .muted { color: #6b7280; font-size: 9px; }
        .report tfoot td { background: #f9fafb; font-weight: bold; }
    </style>
</head>
<body>
    @php($currency = \App\Support\Currency::current())
    <h1>Stock Report</h1>
    <p class="period">
        @if ($filters['search'])
            Search: {{ $filters['search'] }}
        @else
            All stock products
        @endif
    </p>

    <table class="summary">
        <tr>
            <td><div class="label">Products</div><div class="amount">{{ number_format($summary['total_products']) }}</div></td>
            <td><div class="label">Purchased Qty</div><div class="amount blue">{{ number_format($summary['total_purchased_qty']) }}</div></td>
            <td><div class="label">Sold Qty</div><div class="amount amber">{{ number_format($summary['total_sold_qty']) }}</div></td>
            <td><div class="label">Stock Value</div><div class="amount green">{{ \App\Support\Currency::format($summary['total_stock_value'], $currency) }}</div></td>
        </tr>
    </table>

    <table class="report">
        <thead>
            <tr>
                <th>SL</th>
                <th>Product</th>
                <th>Category</th>
                <th class="right">Purchased</th>
                <th class="right">Sold</th>
                <th class="right">Stock</th>
                <th class="right">Stock Value</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($products as $index => $product)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        {{ $product['name'] }}
                        <br>
                        <span class="muted">{{ $product['sku'] ?: 'No SKU' }}</span>
                    </td>
                    <td>
                        {{ $product['category_name'] ?: 'No category' }}
                        @if ($product['subcategory_name'])
                            <br>
                            <span class="muted">{{ $product['subcategory_name'] }}</span>
                        @endif
                    </td>
                    <td class="right">{{ number_format($product['purchased_qty']) }} {{ $product['unit'] ?: 'pcs' }}</td>
                    <td class="right">{{ number_format($product['sold_qty']) }} {{ $product['unit'] ?: 'pcs' }}</td>
                    <td class="right">{{ number_format($product['stock_quantity']) }} {{ $product['unit'] ?: 'pcs' }}</td>
                    <td class="right">{{ \App\Support\Currency::format($product['stock_value'], $currency) }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="right">No stock records found.</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3">{{ number_format($summary['total_products']) }} product(s)</td>
                <td class="right">{{ number_format($summary['total_purchased_qty']) }}</td>
                <td class="right">{{ number_format($summary['total_sold_qty']) }}</td>
                <td class="right">{{ number_format($summary['total_stock_qty']) }}</td>
                <td class="right">{{ \App\Support\Currency::format($summary['total_stock_value'], $currency) }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
