<button {{ $attributes->merge(['type' => 'button', 'class' => 'btn btn-outline btn-secondary']) }}>
    {{ $slot }}
</button>
