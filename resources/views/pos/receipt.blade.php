<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Pembayaran {{ $invoice->invoice_number }}</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Courier New', Courier, monospace;
        }
        body {
            background-color: #ffffff;
            color: #000000;
            font-size: 12px;
            padding: 10px;
            max-width: 320px;
            margin: 0 auto;
        }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .fw-bold { font-weight: bold; }
        .divider {
            border-top: 1px dashed #000000;
            margin: 6px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        td {
            padding: 2px 0;
            vertical-align: top;
        }
        @media print {
            body {
                padding: 0;
                max-width: 100%;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body onload="if(window.location.search.includes('print=true')) window.print()">

    <div class="text-center">
        <h3 style="font-size: 16px; margin-bottom: 2px;">RIZAL STORE - MYGUDANG ERP</h3>
        <p style="font-size: 10px;">Jl. Raya Bisnis No. 88, Jakarta</p>
        <p style="font-size: 10px;">Telp: (021) 555-8899</p>
    </div>

    <div class="divider"></div>

    <table>
        <tr>
            <td>No: {{ $invoice->invoice_number }}</td>
            <td class="text-end">{{ date('d/m/Y H:i', strtotime($invoice->created_at)) }}</td>
        </tr>
        <tr>
            <td>Kasir: {{ $invoice->createdBy->name ?? 'Kasir' }}</td>
            <td class="text-end">Plg: {{ $invoice->customer->name ?? 'Umum' }}</td>
        </tr>
    </table>

    <div class="divider"></div>

    <table>
        @foreach($invoice->items as $item)
        <tr>
            <td colspan="2" class="fw-bold">{{ $item->item->name ?? 'Barang' }}</td>
        </tr>
        <tr>
            <td style="padding-left: 8px;">
                {{ (float) $item->quantity == (int) $item->quantity ? number_format($item->quantity, 0) : number_format($item->quantity, 2, ',', '.') }} {{ $item->item->unit->symbol ?? '' }}
                @if($item->koli)
                    <span style="font-size: 10px; color: #444;">({{ $item->koli }} Koli)</span>
                @endif
                x Rp {{ number_format($item->unit_price, 0, ',', '.') }}
            </td>
            <td class="text-end">
                Rp {{ number_format($item->subtotal, 0, ',', '.') }}
            </td>
        </tr>
        @endforeach
    </table>

    <div class="divider"></div>

    <table>
        <tr>
            <td>Subtotal</td>
            <td class="text-end">Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}</td>
        </tr>
        @if($invoice->tax_amount > 0)
        <tr>
            <td>PPN</td>
            <td class="text-end">Rp {{ number_format($invoice->tax_amount, 0, ',', '.') }}</td>
        </tr>
        @endif
        <tr class="fw-bold" style="font-size: 13px;">
            <td>TOTAL</td>
            <td class="text-end">Rp {{ number_format($invoice->grand_total, 0, ',', '.') }}</td>
        </tr>
    </table>

    <div class="divider"></div>

    @if(!empty($invoice->payment_details) && is_array($invoice->payment_details))
    <table>
        <tr style="font-size: 10px; font-weight: bold; color: #333;">
            <td colspan="2">RINCIAN PEMBAYARAN:</td>
        </tr>
        @foreach($invoice->payment_details as $p)
        <tr>
            <td style="padding-left: 4px;">
                {{ $p['payment_method_label'] ?? ucfirst($p['payment_method'] ?? 'Bayar') }}
                @if(!empty($p['account_name']))
                    <span style="font-size: 9px; color: #444;">({{ $p['account_name'] }})</span>
                @endif
                @if(!empty($p['reference_no']))
                    <div style="font-size: 9px; color: #666;">Ref: {{ $p['reference_no'] }}</div>
                @endif
            </td>
            <td class="text-end">
                Rp {{ number_format($p['amount'] ?? 0, 0, ',', '.') }}
            </td>
        </tr>
        @endforeach
    </table>
    <div class="divider"></div>
    @endif

    @if($invoice->notes)
    <p style="font-size: 10px; margin-bottom: 4px;">{{ $invoice->notes }}</p>
    @endif

    <div class="text-center" style="margin-top: 10px;">
        <p style="font-size: 11px; font-weight: bold;">-- TERIMA KASIH --</p>
        <p style="font-size: 9px; margin-top: 2px;">Barang yang sudah dibeli tidak dapat ditukar/dikembalikan.</p>
    </div>

    <div class="no-print" style="margin-top: 15px; text-align: center;">
        <button onclick="window.print()" style="padding: 6px 12px; font-size: 12px; cursor: pointer;">
            🖨️ Cetak Struk
        </button>
    </div>

</body>
</html>
