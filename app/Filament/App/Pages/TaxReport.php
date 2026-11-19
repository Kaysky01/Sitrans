<?php

namespace App\Filament\App\Pages;

use App\Models\Outlet;
use App\Modules\Reporting\Services\ReportCsv;
use App\Modules\Reporting\Services\SalesSummary;
use BackedEnum;
use Carbon\CarbonImmutable;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class TaxReport extends Page
{
    protected string $view = 'filament.app.pages.tax-report';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedReceiptPercent;

    protected static string|\UnitEnum|null $navigationGroup = 'Transaksi';

    protected static ?string $navigationLabel = 'Pajak (PB1)';

    protected static ?string $title = 'Laporan pajak & service';

    protected static ?int $navigationSort = 3;

    public string $from = '';

    public string $to = '';

    public ?int $outletId = null;

    /** Pesan rentang tanggal tidak valid untuk ditampilkan inline. */
    public ?string $rangeError = null;

    public static function canAccess(): bool
    {
        return auth()->user()?->can('reports.view') ?? false;
    }

    public function mount(): void
    {
        $this->setRange(now()->startOfMonth(), now());
    }

    /**
     * @return array<int, Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('export')
                ->label('Ekspor CSV')
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->action(fn () => $this->exportCsv()),
        ];
    }

    public function exportCsv(): StreamedResponse
    {
        abort_unless(auth()->user()?->can('reports.view'), 403);

        [$from, $to] = $this->bounds();

        $csv = app(ReportCsv::class)->tax($from, $to, $this->outletId);
        $filename = 'laporan-pajak-'.$from->toDateString().'-'.$to->toDateString().'.csv';

        return response()->streamDownload(
            fn () => print ($csv),
            $filename,
            ['Content-Type' => 'text/csv; charset=UTF-8'],
        );
    }

    /**
     * @return Collection<int, Outlet>
     */
    #[Computed]
    public function outlets(): Collection
    {
        return Outlet::query()->where('is_active', true)->orderBy('name')->get();
    }

    public function setRangeToday(): void
    {
        $this->setRange(now(), now());
    }

    public function setRangeLastSevenDays(): void
    {
        $this->setRange(now()->subDays(6), now());
    }

    public function setRangeThisMonth(): void
    {
        $this->setRange(now()->startOfMonth(), now());
    }

    /**
     * @return array<string, mixed>
     */
    #[Computed]
    public function summary(): array
    {
        [$from, $to] = $this->bounds();

        return app(SalesSummary::class)->totals($from, $to, $this->outletId);
    }

    /**
     * @return array<string, array{orders: int, tax: float, service: float, gross: float}>
     */
    #[Computed]
    public function series(): array
    {
        [$from, $to] = $this->bounds();

        return app(SalesSummary::class)->taxSeries($from, $to, $this->outletId);
    }

    private function setRange(\DateTimeInterface $from, \DateTimeInterface $to): void
    {
        $this->from = CarbonImmutable::parse($from)->toDateString();
        $this->to = CarbonImmutable::parse($to)->toDateString();
    }

    /**
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    private function bounds(): array
    {
        $from = $this->parse($this->from, CarbonImmutable::now()->startOfMonth());
        $to = $this->parse($this->to, CarbonImmutable::now());

        if ($from->greaterThan($to)) {
            [$from, $to] = [$to, $from];
            $this->rangeError = 'Rentang tanggal tidak valid: tanggal awal melebihi tanggal akhir. Kedua tanggal ditukar otomatis agar laporan tetap tampil.';
        } else {
            $this->rangeError = null;
        }

        return [$from->startOfDay(), $to->endOfDay()];
    }

    private function parse(string $value, CarbonImmutable $fallback): CarbonImmutable
    {
        try {
            return CarbonImmutable::parse($value);
        } catch (Throwable) {
            return $fallback;
        }
    }
}
