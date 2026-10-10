<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Tutup Kasir (Z-Report) - {{ $shift->shift_number }}</title>
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
            padding: 12px;
            max-width: 340px;
            margin: 0 auto;
        }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .fw-bold { font-weight: bold; }
        .divider {
            border-top: 1px dashed #000000;
            margin: 8px 0;
        }
        .double-divider {
            border-top: 2px solid #000000;
            margin: 8px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        td {
            padding: 2px 0;
            vertical-align: top;
        }
        .btn-print-box {
            margin-top: 15px;
            display: flex;
            gap: 8px;
            justify-content: center;
        }
        .btn {
            padding: 6px 12px;
            font-size: 12px;
            cursor: pointer;
            border-radius: 4px;
            border: 1px solid #ccc;
            text-decoration: none;
            color: #333;
            background: #f1f5f9;
        }
        .btn-primary {
            background: #2563eb;
            color: #fff;
            border-color: #2563eb;
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
<body>

    <div class="text-center">
        <h3 style="font-size: 15px; margin-bottom: 2px;">MYGUDANG ERP POS</h3>
        <h4 style="font-size: 13px;">REKAP TUTUP KASIR (Z-REPORT)</h4>
        <p style="font-size: 10px;">Cabang: {{ $shift->branch->name ?? 'Kantor Pusat' }}</p>
    </div>

    <div class="double-divider"></div>

    <table>
        <tr>
            <td>No Shift</td>
            <td class="text-end fw-bold">{{ $shift->shift_number }}</td>
        </tr>
        <tr>
            <td>Kasir</td>
            <td class="text-end">{{ $shift->user->name ?? 'Kasir' }}</td>
        </tr>
        <tr>
            <td>Kas Laci</td>
            <td class="text-end">{{ $shift->cashAccount->name ?? 'Kas Kasir' }}</td>
        </tr>
        <tr>
            <td>Waktu Buka</td>
            <td class="text-end">{{ $shift->start_time ? $shift->start_time->format('d/m/y H:i') : '-' }}</td>
        </tr>
        <tr>
            <td>Waktu Tutup</td>
            <td class="text-end">{{ $shift->end_time ? $shift->end_time->format('d/m/y H:i') : date('d/m/y H:i') }}</td>
        </tr>
    </table>

    <div class="divider"></div>

    <table>
        <tr>
            <td>Modal Awal Laci:</td>
            <td class="text-end fw-bold">Rp {{ number_format($shift->starting_cash, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Total Jual Tunai (+):</td>
            <td class="text-end">Rp {{ number_format($shift->total_cash_sales, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Total Jual Non-Tunai:</td>
            <td class="text-end">Rp {{ number_format($shift->total_non_cash_sales, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Setoran Kas ke Bank (-):</td>
            <td class="text-end">Rp {{ number_format($shift->total_drops, 0, ',', '.') }}</td>
        </tr>
    </table>

    <div class="divider"></div>

    <table>
        <tr class="fw-bold">
            <td>Kas Seharusnya di Laci:</td>
            <td class="text-end">Rp {{ number_format($shift->closing_cash_expected, 0, ',', '.') }}</td>
        </tr>
        <tr class="fw-bold">
            <td>Kas Fisik Aktual:</td>
            <td class="text-end">Rp {{ number_format($shift->closing_cash_actual ?? $shift->closing_cash_expected, 0, ',', '.') }}</td>
        </tr>
        @php
            $diff = (float) $shift->cash_difference;
        @endphp
        <tr class="fw-bold" style="font-size: 13px;">
            <td>Selisih Kas:</td>
            <td class="text-end">
                @if($diff == 0)
                    PAS (Rp 0)
                @elseif($diff > 0)
                    LEBIH (+Rp {{ number_format($diff, 0, ',', '.') }})
                @else
                    KURANG (-Rp {{ number_format(abs($diff), 0, ',', '.') }})
                @endif
            </td>
        </tr>
    </table>

    @if($shift->drops->isNotEmpty())
        <div class="divider"></div>
        <div style="font-size: 11px; font-weight: bold; margin-bottom: 4px;">Rincian Setoran / Drop:</div>
        <table>
            @foreach($shift->drops as $drop)
                <tr>
                    <td>{{ $drop->toAccount->name ?? 'Bank/Kas' }}</td>
                    <td class="text-end">Rp {{ number_format($drop->amount, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </table>
    @endif

    <div class="double-divider"></div>

    <div class="text-center" style="font-size: 10px; margin-top: 8px;">
        <p>Total Transaksi: {{ $shift->invoices->count() }} Struk</p>
        <p>Status: <strong>{{ strtoupper($shift->status) }}</strong></p>
        <p style="margin-top: 8px;">Dicetak pada: {{ date('d/m/Y H:i:s') }}</p>
    </div>

    <div class="btn-print-box no-print">
        <button class="btn btn-primary" onclick="window.print()">Cetak Struk Z</button>
        <a href="{{ route('pos.index') }}" class="btn">Kembali ke POS</a>
    </div>

</body>
</html>
