<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 11px; color: #1e293b; }

        /* ── Header ── */
        .header { background: #0f172a; color: #fff; padding: 20px 30px; display: table; width: 100%; }
        .header-logo { display: table-cell; vertical-align: middle; width: 60%; }
        .header-logo h1 { font-size: 20px; font-weight: 700; letter-spacing: 1px; }
        .header-logo p  { font-size: 10px; color: #94a3b8; margin-top: 2px; }
        .header-meta  { display: table-cell; vertical-align: middle; text-align: right; font-size: 10px; color: #94a3b8; }
        .header-meta strong { color: #fff; }

        /* ── Summary cards ── */
        .summary { display: table; width: 100%; margin: 20px 0; border-spacing: 10px; }
        .summary-cell { display: table-cell; background: #f1f5f9; border-left: 4px solid #3b82f6; padding: 12px 16px; width: 25%; }
        .summary-cell.warn { border-color: #ef4444; background: #fef2f2; }
        .summary-cell .label { font-size: 9px; text-transform: uppercase; color: #64748b; letter-spacing: .5px; }
        .summary-cell .value { font-size: 20px; font-weight: 700; color: #0f172a; margin-top: 2px; }
        .summary-cell.warn .value { color: #dc2626; }

        /* ── Section title ── */
        .section-title { font-size: 12px; font-weight: 700; color: #0f172a; margin: 18px 0 8px;
                         border-bottom: 2px solid #e2e8f0; padding-bottom: 4px; }

        /* ── Table ── */
        table { width: 100%; border-collapse: collapse; font-size: 10px; }
        thead tr { background: #0f172a; color: #fff; }
        thead th { padding: 8px 10px; text-align: left; font-weight: 600; }
        tbody tr:nth-child(even) { background: #f8fafc; }
        tbody tr:hover { background: #f1f5f9; }
        tbody td { padding: 7px 10px; border-bottom: 1px solid #e2e8f0; }
        .badge { display: inline-block; padding: 2px 7px; border-radius: 9px; font-size: 9px; font-weight: 600; }
        .badge-ok   { background: #dcfce7; color: #166534; }
        .badge-warn { background: #fee2e2; color: #991b1b; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }

        /* ── Footer ── */
        .footer { margin-top: 30px; border-top: 1px solid #e2e8f0; padding-top: 10px;
                  font-size: 9px; color: #94a3b8; display: table; width: 100%; }
        .footer-left  { display: table-cell; }
        .footer-right { display: table-cell; text-align: right; }
    </style>
</head>
<body>

    {{-- Header --}}
    <div class="header">
        <div class="header-logo">
            <h1>SmartStock Pro</h1>
            <p>Sistem Manajemen Inventaris</p>
        </div>
        <div class="header-meta">
            <strong>{{ $title }}</strong><br>
            Dibuat : {{ $date }}<br>
            @if(!empty($filters['start_date'])) Periode : {{ $filters['start_date'] }} s/d {{ $filters['end_date'] ?? 'sekarang' }} @endif
        </div>
    </div>

    {{-- Summary --}}
    <div class="summary">
        <div class="summary-cell">
            <div class="label">Total SKU</div>
            <div class="value">{{ number_format($summary['total_products']) }}</div>
        </div>
        <div class="summary-cell">
            <div class="label">Total Stok</div>
            <div class="value">{{ number_format($summary['total_stock']) }}</div>
        </div>
        <div class="summary-cell">
            <div class="label">Gudang Aktif</div>
            <div class="value">{{ number_format($summary['total_warehouses']) }}</div>
        </div>
        <div class="summary-cell warn">
            <div class="label">Stok Kritis</div>
            <div class="value">{{ number_format($summary['low_stock_count']) }}</div>
        </div>
    </div>

    {{-- Produk Table --}}
    <div class="section-title">Daftar Inventaris Produk</div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>SKU</th>
                <th>Nama Produk</th>
                <th>Kategori</th>
                <th>Gudang</th>
                <th class="text-right">Stok</th>
                <th class="text-right">Min. Stok</th>
                <th class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $i => $product)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td><strong>{{ $product->sku }}</strong></td>
                <td>{{ $product->name }}</td>
                <td>{{ $product->category->name ?? '-' }}</td>
                <td>{{ $product->warehouse->name ?? '-' }}</td>
                <td class="text-right">{{ number_format($product->stock) }}</td>
                <td class="text-right">{{ number_format($product->min_stock) }}</td>
                <td class="text-center">
                    @if($product->stock < $product->min_stock)
                        <span class="badge badge-warn">Kritis</span>
                    @else
                        <span class="badge badge-ok">Normal</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center" style="padding:16px;color:#64748b;">Tidak ada data produk.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if(count($transactions) > 0)
    {{-- Transaksi Terkini --}}
    <div class="section-title" style="margin-top:24px;">Transaksi Terkini (30 Hari Terakhir)</div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Tanggal</th>
                <th>Produk</th>
                <th>Gudang</th>
                <th class="text-center">Tipe</th>
                <th class="text-right">Qty</th>
                <th>Referensi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $i => $tx)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($tx->created_at)->format('d M Y') }}</td>
                <td>{{ $tx->product->name ?? '-' }}</td>
                <td>{{ $tx->warehouse->name ?? '-' }}</td>
                <td class="text-center">
                    @if($tx->type === 'in')
                        <span class="badge badge-ok">Masuk</span>
                    @else
                        <span class="badge badge-warn">Keluar</span>
                    @endif
                </td>
                <td class="text-right">{{ number_format($tx->quantity) }}</td>
                <td>{{ $tx->reference_number ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- Footer --}}
    <div class="footer">
        <div class="footer-left">SmartStock Pro &mdash; Laporan Inventaris &mdash; {{ $date }}</div>
        <div class="footer-right">Dicetak oleh sistem secara otomatis</div>
    </div>

</body>
</html>
