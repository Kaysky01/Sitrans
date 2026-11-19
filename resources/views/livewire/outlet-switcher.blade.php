<div>
    @php($outlets = $this->outlets)

    @if ($outlets->count() > 1)
        <div class="flex min-w-0 items-center gap-2 rounded-full bg-white px-3.5 py-2 shadow-sm ring-1 ring-slate-900/10">
            <x-filament::icon icon="heroicon-o-building-storefront" class="h-5 w-5 shrink-0 text-navy-500" />
            <label class="sr-only" for="outlet-switcher">Outlet Aktif</label>
            <select
                id="outlet-switcher"
                wire:model.live="outletId"
                wire:loading.attr="disabled" wire:target="outletId"
                class="max-w-[9rem] truncate border-0 bg-transparent p-0 text-sm font-bold text-slate-900 focus:outline-none focus:ring-0 sm:max-w-none"
            >
                @foreach ($outlets as $outlet)
                    <option value="{{ $outlet->getKey() }}">{{ $outlet->name }}</option>
                @endforeach
            </select>
            @if ($switchError)
                <span class="text-[11px] font-semibold text-rose-600" x-data="{ show: true }" x-init="setTimeout(() => { show = false; $wire.$set('switchError', null) }, 4000)" x-show="show">{{ $switchError }}</span>
            @endif
        </div>
    @elseif ($alwaysShow && $outlets->count() === 1 && $outlets->first())
        <div class="inline-flex items-center gap-2 rounded-full bg-white px-3.5 py-2 shadow-sm ring-1 ring-slate-900/10">
            <x-filament::icon icon="heroicon-o-building-storefront" class="h-5 w-5 shrink-0 text-navy-500" />
            <span class="max-w-[11rem] truncate text-sm font-bold text-slate-900">{{ $outlets->first()->name }}</span>
        </div>
    @endif
</div>
