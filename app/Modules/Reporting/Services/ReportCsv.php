<?php

namespace App\Modules\Reporting\Services;

use Carbon\CarbonInterface;

/**
 * Renders reporting data as CSV. Values are pre-formatted as plain numbers so
 * the export can be opened in a spreadsheet; every file starts with a UTF-8
 * BOM so Excel reads the Indonesian characters correctly.
 */
final class ReportCsv
{
    private const BOM = "\xEF\xBB\xBF";

    public function __construct(private readonly SalesSummary $summary) {}

    public function sales(CarbonInterface $from, CarbonInterface $to, ?int $outletId = null): string
    {
        $totals = $this->summary->totals($from, $to, $outletId);

        $lines = [
            ['Laporan penjualan', $from->toDateString(), $to->toDateString()],
            [],
            ['Metrik', 'Nilai'],
            ['Transaksi', $totals['orders']],
            ['Penjualan', $totals['revenue']],
            ['Diskon', $totals['discounts']],
            ['PB1', $totals['tax']],
            ['Service', $totals['service']],
            ['Total bruto', $totals['gross']],
            ['HPP', $totals['cogs']],
            ['Laba kotor', $totals['gross_margin']],
            ['Rata-rata / transaksi', $totals['avg_ticket']],
            ['Item terjual', $totals['items_sold']],
            [],
            ['Penjualan per hari'],
            ['Tanggal', 'Penjualan'],
        ];

        foreach ($this->summary->dailySeries($from, $to, $outletId) as $day => $revenue) {
            $lines[] = [$day, $revenue];
        }

        $lines[] = [];
        $lines[] = ['Menu terlaris'];
        $lines[] = ['Menu', 'Qty', 'Omzet'];

        foreach ($this->summary->topProducts($from, $to, outletId: $outletId) as $row) {
            $lines[] = [$row->name, (float) $row->qty, (float) $row->total];
        }

        $lines[] = [];
        $lines[] = ['Metode pembayaran'];
        $lines[] = ['Metode', 'Transaksi', 'Total'];

        foreach ($this->summary->paymentBreakdown($from, $to, $outletId) as $row) {
            $lines[] = [$row->method_name, (int) $row->payments, (float) $row->total];
        }

        return $this->encode($lines);
    }

    public function tax(CarbonInterface $from, CarbonInterface $to, ?int $outletId = null): string
    {
        $totals = $this->summary->totals($from, $to, $outletId);

        $lines = [
            ['Laporan PB1 & service', $from->toDateString(), $to->toDateString()],
            [],
            ['Total PB1', $totals['tax']],
            ['Total service', $totals['service']],
            ['Total titipan', round($totals['tax'] + $totals['service'], 2)],
            [],
            ['Tanggal', 'Transaksi', 'PB1', 'Service', 'Total bruto'],
        ];

        foreach ($this->summary->taxSeries($from, $to, $outletId) as $day => $row) {
            $lines[] = [$day, $row['orders'], $row['tax'], $row['service'], $row['gross']];
        }

        return $this->encode($lines);
    }

    /**
     * @param  array<int, array<int, int|float|string|null>>  $lines
     */
    private function encode(array $lines): string
    {
        $handle = fopen('php://temp', 'r+');

        foreach ($lines as $line) {
            fputcsv($handle, $line);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return self::BOM.$csv;
    }
}
