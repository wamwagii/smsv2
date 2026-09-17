<div class="space-y-4">
    <div class="grid grid-cols-3 gap-4">
        <div class="rounded-lg bg-gray-50 p-4">
            <div class="text-sm text-gray-500">Billed</div>
            <div class="text-xl font-bold">KES {{ number_format($totalBilled, 2) }}</div>
        </div>
        <div class="rounded-lg bg-green-50 p-4">
            <div class="text-sm text-gray-500">Paid</div>
            <div class="text-xl font-bold text-green-600">KES {{ number_format($totalPaid, 2) }}</div>
        </div>
        <div class="rounded-lg {{ $balance > 0 ? 'bg-red-50' : 'bg-blue-50' }} p-4">
            <div class="text-sm text-gray-500">Balance</div>
            <div class="text-xl font-bold {{ $balance > 0 ? 'text-red-600' : 'text-blue-600' }}">
                KES {{ number_format(abs($balance), 2) }}
                {{ $balance > 0 ? 'owed' : ($balance < 0 ? 'overpaid' : '') }}
            </div>
        </div>
    </div>

    <div>
        <h3 class="font-semibold mb-2">Invoices</h3>
        @forelse ($invoices as $invoice)
            <div class="flex justify-between border-b py-2 text-sm">
                <div>
                    <div class="font-medium">{{ $invoice->invoice_number }}</div>
                    <div class="text-xs text-gray-500">
                        {{ ucfirst(str_replace('_', ' ', $invoice->term)) }}
                        · Due {{ $invoice->due_date?->format('d/m/Y') }}
                    </div>
                </div>
                <div class="text-right">
                    <div class="font-semibold">KES {{ number_format((float) $invoice->amount, 2) }}</div>
                    <div class="text-xs">
                        Paid: KES {{ number_format((float) $invoice->amount_paid, 2) }}
                        @if ((float) $invoice->balance > 0.01)
                            · <span class="text-red-600">Bal: KES {{ number_format((float) $invoice->balance, 2) }}</span>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <p class="text-sm text-gray-500">No invoices recorded.</p>
        @endforelse
    </div>
</div>