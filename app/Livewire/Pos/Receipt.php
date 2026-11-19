<?php

namespace App\Livewire\Pos;

use App\Mail\OrderReceiptMail;
use App\Models\Order;
use App\Models\ReceiptSetting;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Throwable;

#[Layout('components.layouts.base')]
class Receipt extends Component
{
    public Order $order;

    public ?ReceiptSetting $receipt = null;

    public string $email = '';

    public ?string $sentTo = null;

    public ?string $sendError = null;

    public bool $sendingEmail = false;

    public function mount(Order $order): void
    {
        $this->order = $order->load(['items.modifiers', 'payments', 'outlet', 'cashier']);
        $this->receipt = ReceiptSetting::query()->where('outlet_id', $order->outlet_id)->first()
            ?? ReceiptSetting::query()->whereNull('outlet_id')->first();
    }

    public function sendEmail(): void
    {
        $this->validate(
            ['email' => ['required', 'email', 'max:150']],
            [],
            ['email' => 'email tujuan'],
        );

        $to = $this->email;

        try {
            $this->sendingEmail = true;

            Mail::to($to)->send(new OrderReceiptMail($this->order, $this->receipt));

            $this->reset('email', 'sendError');
            $this->sentTo = $to;
        } catch (Throwable $exception) {
            report($exception);

            $this->sendError = 'Struk gagal dikirim. Periksa koneksi atau coba beberapa saat lagi.';
        } finally {
            $this->sendingEmail = false;
        }
    }

    public function newOrder(): mixed
    {
        return $this->redirect(route('pos'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.pos.receipt');
    }
}
