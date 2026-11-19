<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Struk {{ $order->number }}</title>
</head>
<body style="margin:0;padding:24px;background:#faf7f2;font-family:Arial,Helvetica,sans-serif;color:#1c1917;">
    <div style="max-width:420px;margin:0 auto;background:#ffffff;border-radius:16px;padding:24px;">
        <div style="text-align:center;">
            @if ($receipt?->show_logo ?? true)
                <img src="{{ asset('images/sitrans-logo.png') }}" alt="Sitrans" width="56" height="56" style="display:block;width:56px;height:56px;margin:0 auto 8px;object-fit:contain;" />
            @endif
            <h1 style="margin:0;font-size:18px;">{{ $receipt?->header ?? $order->outlet?->name ?? 'Sitrans' }}</h1>
            @if ($order->outlet?->address)
                <p style="margin:4px 0 0;font-size:12px;color:#57534e;">{{ $order->outlet->address }}</p>
            @endif
        </div>

        <hr style="border:none;border-top:1px dashed #d6d3d1;margin:16px 0;">

        <table width="100%" style="font-size:12px;color:#44403c;">
            <tr>
                <td>{{ $order->number }}</td>
                <td align="right">{{ $order->created_at?->format('d/m/Y H:i') }}</td>
            </tr>
            <tr>
                <td>{{ $order->type->shortLabel() }}</td>
                <td align="right">{{ $order->cashier?->name }}</td>
            </tr>
        </table>

        <hr style="border:none;border-top:1px dashed #d6d3d1;margin:16px 0;">

        <table width="100%" style="font-size:13px;color:#1c1917;">
            @foreach ($order->items as $item)
                <tr>
                    <td style="padding:4px 0;">
                        {{ $item->name }}@if ($item->variant_name) ({{ $item->variant_name }})@endif
                        @foreach ($item->modifiers as $modifier)
                            <div style="font-size:11px;color:#57534e;">+ {{ $modifier->name }}</div>
                        @endforeach
                        <div style="font-size:11px;color:#57534e;">{{ (int) $item->quantity }} x {{ \App\Support\Format::rupiah($item->unit_price) }}</div>
                    </td>
                    <td align="right" valign="top" style="padding:4px 0;">{{ \App\Support\Format::rupiah($item->line_total) }}</td>
                </tr>
            @endforeach
        </table>

        <hr style="border:none;border-top:1px dashed #d6d3d1;margin:16px 0;">

        <table width="100%" style="font-size:12px;color:#44403c;">
            <tr>
                <td>Subtotal</td>
                <td align="right">{{ \App\Support\Format::rupiah($order->subtotal) }}</td>
            </tr>
            @if ($order->discount_total > 0)
                <tr>
                    <td>Diskon</td>
                    <td align="right">− {{ \App\Support\Format::rupiah($order->discount_total) }}</td>
                </tr>
            @endif
            @if ($order->service_amount > 0)
                <tr>
                    <td>Service</td>
                    <td align="right">{{ \App\Support\Format::rupiah($order->service_amount) }}</td>
                </tr>
            @endif
            @if ($order->tax_amount > 0)
                <tr>
                    <td>PB1</td>
                    <td align="right">{{ \App\Support\Format::rupiah($order->tax_amount) }}</td>
                </tr>
            @endif
            <tr>
                <td style="padding-top:8px;font-size:15px;font-weight:bold;color:#065f46;">Total</td>
                <td align="right" style="padding-top:8px;font-size:15px;font-weight:bold;color:#065f46;">{{ \App\Support\Format::rupiah($order->grand_total) }}</td>
            </tr>
            @foreach ($order->payments as $payment)
                <tr>
                    <td>{{ $payment->method_name }}</td>
                    <td align="right">{{ \App\Support\Format::rupiah($payment->amount) }}</td>
                </tr>
            @endforeach
            @if ($order->change_total > 0)
                <tr>
                    <td>Kembali</td>
                    <td align="right">{{ \App\Support\Format::rupiah($order->change_total) }}</td>
                </tr>
            @endif
        </table>

        <hr style="border:none;border-top:1px dashed #d6d3d1;margin:16px 0;">

        <p style="margin:0;text-align:center;font-size:12px;color:#57534e;">
            {{ $receipt?->footer ?? 'Terima kasih atas kunjungan Anda' }}
        </p>
    </div>
</body>
</html>
