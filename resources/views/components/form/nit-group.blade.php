@props(['nitName' => 'nit', 'dvName' => 'dv', 'nitValue' => null, 'dvValue' => null])

<div class="grid grid-cols-4 gap-x-4">
    <div class="col-span-3">
        <x-form.input name="{{ $nitName }}" icon="badge" placeholder="NIT" :value="$nitValue" required />
    </div>
    <div class="col-span-1">
        <x-form.input name="{{ $dvName }}" icon="pin" placeholder="DV" :value="$dvValue" required />
    </div>
</div>