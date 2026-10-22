<?php

namespace App\Livewire\Auth;

use App\Modules\Tenancy\Actions\RegisterTenantAction;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.base')]
class Register extends Component
{
    public string $cafe_name = '';

    public string $outlet_name = '';

    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $password = '';

    public string $password_confirmation = '';

    public bool $agree = false;

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'cafe_name' => ['required', 'string', 'min:2', 'max:100'],
            'outlet_name' => ['nullable', 'string', 'max:100'],
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'agree' => ['accepted'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'cafe_name' => 'nama kafe',
            'outlet_name' => 'nama outlet',
            'name' => 'nama Anda',
            'email' => 'email',
            'phone' => 'nomor telepon',
            'password' => 'kata sandi',
            'agree' => 'persetujuan',
        ];
    }

    public function register(RegisterTenantAction $action): void
    {
        $data = $this->validate();

        $result = $action->execute($data);

        Auth::login($result['owner']);
        session(['active_tenant_id' => $result['tenant']->getKey()]);

        $this->redirect('/app', navigate: true);
    }

    public function render(): View
    {
        return view('livewire.auth.register');
    }
}
