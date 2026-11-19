<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Login;

class RateLimitedLogin extends Login
{
    /**
     * Keep login attempts isolated per email and origin IP address. Filament
     * enforces five attempts per minute before credentials are evaluated.
     */
    protected function getRateLimitKey(mixed $method, mixed $component = null): string
    {
        if ($method !== 'authenticate') {
            return parent::getRateLimitKey($method, $component);
        }

        $email = mb_strtolower(trim((string) ($this->data['email'] ?? '')));

        return 'sitrans:login:'.sha1($email.'|'.request()->ip());
    }
}
