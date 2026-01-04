<div class="space-y-2">
    <p class="text-sm text-gray-500">
        Approved Signature
    </p>

    <img src="{{ asset('storage/' . $record->signature_path) }}" alt="Signature" class="h-24 bg-white border rounded dark:bg-gray-900" />

    <p class="text-xs text-gray-400">
        Signed at {{ $record->approved_at?->format('d M Y H:i') }}
    </p>
</div>