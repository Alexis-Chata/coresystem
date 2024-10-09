<div>
    <select
        wire:change="$dispatch('tipoDocumentoChanged', { tipoDocumentoId: $event.target.value, empleadoId: {{ $empleadoId }} })"
    >
        @foreach($options as $value => $label)
            <option value="{{ $value }}" {{ $value == $selected ? 'selected' : '' }}>
                {{ $label }}
            </option>
        @endforeach
    </select>
</div>