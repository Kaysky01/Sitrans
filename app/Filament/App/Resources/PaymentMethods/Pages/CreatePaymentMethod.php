<?php

namespace App\Filament\App\Resources\PaymentMethods\Pages;

use App\Filament\App\Resources\PaymentMethods\PaymentMethodResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePaymentMethod extends CreateRecord
{
    protected static string $resource = PaymentMethodResource::class;
}
