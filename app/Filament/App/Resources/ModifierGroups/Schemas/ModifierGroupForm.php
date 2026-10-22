<?php

namespace App\Filament\App\Resources\ModifierGroups\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ModifierGroupForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label('Nama grup')->required()->maxLength(80)
                ->placeholder('Level gula / Topping'),
            TextInput::make('min_select')->label('Minimal pilih')->numeric()->default(0)->minValue(0),
            TextInput::make('max_select')->label('Maksimal pilih')->numeric()->default(1)->minValue(1),
            TextInput::make('sort_order')->label('Urutan')->numeric()->default(0),
            Toggle::make('is_required')->label('Wajib dipilih'),
        ]);
    }
}
