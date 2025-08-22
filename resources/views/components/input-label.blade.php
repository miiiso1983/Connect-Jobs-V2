@props(['value'])

<label {{ $attributes->merge(['class' => 'label']) }}><span class="label-text">
</span>
    {{ $value ?? $slot }}
</label>
