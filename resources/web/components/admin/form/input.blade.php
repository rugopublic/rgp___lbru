@props(['name', 'label' => null, 'value' => null, 'type' => 'text', 'required' => false, 'help' => null])
@if($label)<label for="{{ $attributes->get('id', $name) }}" class="form-label">{{ $label }}</label>@endif
<input type="{{ $type }}" name="{{ $name }}" id="{{ $attributes->get('id', $name) }}"
       value="{{ old($name, $value) }}" @if($required) required @endif
       {{ $attributes->merge(['class' => 'form-control']) }}>
@if($help)<div class="form-text">{{ $help }}</div>@endif
@error($name)<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
