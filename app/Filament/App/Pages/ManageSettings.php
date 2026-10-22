<?php

namespace App\Filament\App\Pages;

use App\Models\ReceiptSetting;
use App\Models\TaxSetting;
use App\Support\Audit\ActivityLogger;
use App\Support\Tenant\TenantManager;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ManageSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|\UnitEnum|null $navigationGroup = 'Pengaturan';

    protected static ?int $navigationSort = 4;

    protected static ?string $title = 'Pengaturan';

    protected static ?string $navigationLabel = 'Pengaturan';

    public static function canAccess(): bool
    {
        return auth()->user()?->can('settings.manage') ?? false;
    }

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public function mount(): void
    {
        $tenant = app(TenantManager::class)->current();

        $tax = TaxSetting::query()->whereNull('outlet_id')->first();
        $receipt = ReceiptSetting::query()->whereNull('outlet_id')->first();

        $this->form->fill([
            'pb1_enabled' => $tax?->pb1_enabled ?? true,
            'pb1_rate' => $tax?->pb1_rate ?? 10,
            'service_enabled' => $tax?->service_enabled ?? false,
            'service_rate' => $tax?->service_rate ?? 5,
            'prices_include_tax' => $tax?->prices_include_tax ?? false,
            'header' => $receipt?->header ?? $tenant?->name,
            'footer' => $receipt?->footer,
            'show_logo' => $receipt?->show_logo ?? true,
            'show_tax' => $receipt?->show_tax ?? true,
            'show_cashier' => $receipt?->show_cashier ?? true,
            'paper_width' => $receipt?->paper_width ?? 58,
            'require_shift' => (bool) $tenant?->setting('require_shift', false),
            'discount_pin_threshold' => (float) $tenant?->setting('discount_pin_threshold', 0),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Pajak & layanan')
                ->description('PB1 dan service charge dicatat terpisah sebagai titipan, bukan pendapatan.')
                ->columns(2)
                ->schema([
                    Toggle::make('pb1_enabled')->label('Aktifkan PB1')->columnSpanFull(),
                    TextInput::make('pb1_rate')->label('Tarif PB1')->numeric()->suffix('%')->minValue(0)->maxValue(100),
                    Toggle::make('service_enabled')->label('Aktifkan service charge'),
                    TextInput::make('service_rate')->label('Tarif service')->numeric()->suffix('%')->minValue(0)->maxValue(100),
                    Toggle::make('prices_include_tax')->label('Harga menu sudah termasuk pajak')->columnSpanFull(),
                ]),
            Section::make('Struk')
                ->columns(2)
                ->schema([
                    TextInput::make('header')->label('Judul struk')->maxLength(100),
                    Select::make('paper_width')->label('Lebar kertas')
                        ->options([58 => '58 mm', 80 => '80 mm'])
                        ->default(58),
                    TextInput::make('footer')->label('Ucapan penutup')->maxLength(150)->columnSpanFull(),
                    Toggle::make('show_logo')->label('Tampilkan logo'),
                    Toggle::make('show_tax')->label('Tampilkan pajak'),
                    Toggle::make('show_cashier')->label('Tampilkan nama kasir'),
                ]),
            Section::make('Kasir')
                ->description('Kendalikan alur kasir di layar POS.')
                ->schema([
                    Toggle::make('require_shift')
                        ->label('Wajib buka shift sebelum pembayaran')
                        ->helperText('Jika aktif, kasir harus membuka shift dan mencatat modal awal sebelum menerima pembayaran.'),
                    TextInput::make('discount_pin_threshold')
                        ->label('Batas diskon tanpa PIN')
                        ->numeric()
                        ->minValue(0)
                        ->prefix('Rp')
                        ->helperText('Diskon di atas nilai ini memerlukan PIN supervisor. Isi 0 untuk menonaktifkan.'),
                ]),
        ])->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        TaxSetting::updateOrCreate(
            ['outlet_id' => null],
            [
                'pb1_enabled' => $data['pb1_enabled'],
                'pb1_rate' => $data['pb1_rate'],
                'service_enabled' => $data['service_enabled'],
                'service_rate' => $data['service_rate'],
                'prices_include_tax' => $data['prices_include_tax'],
            ],
        );

        ReceiptSetting::updateOrCreate(
            ['outlet_id' => null],
            [
                'header' => $data['header'],
                'footer' => $data['footer'],
                'show_logo' => $data['show_logo'],
                'show_tax' => $data['show_tax'],
                'show_cashier' => $data['show_cashier'],
                'paper_width' => $data['paper_width'],
            ],
        );

        app(TenantManager::class)->current()?->setSetting('require_shift', (bool) $data['require_shift']);
        app(TenantManager::class)->current()?->setSetting('discount_pin_threshold', (float) $data['discount_pin_threshold']);

        app(ActivityLogger::class)->log(
            'settings.updated',
            'Pengaturan kafe diperbarui',
            app(TenantManager::class)->current(),
            [
                'pb1_rate' => $data['pb1_rate'],
                'service_rate' => $data['service_rate'],
                'require_shift' => (bool) $data['require_shift'],
                'discount_pin_threshold' => (float) $data['discount_pin_threshold'],
            ],
        );

        Notification::make()->title('Pengaturan disimpan')->success()->send();
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([EmbeddedSchema::make('form')])
                ->id('form')
                ->livewireSubmitHandler('save')
                ->footer([
                    Actions::make([
                        Action::make('save')->label('Simpan')->submit('save'),
                    ]),
                ]),
        ]);
    }
}
