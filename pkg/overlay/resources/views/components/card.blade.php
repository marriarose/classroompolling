@props(['title' => null])
<div {{ $attributes->merge(['class' => 'card shadow-sm mb-4']) }}>
    @if ($title)<div class="card-header bg-white fw-semibold border-0 pt-3">{{ $title }}</div>@endif
    <div class="card-body">{{ $slot }}</div>
</div>
