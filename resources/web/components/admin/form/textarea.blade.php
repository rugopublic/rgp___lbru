@props(['name', 'label' => null, 'value' => null, 'rows' => 4, 'required' => false, 'help' => null])
@if($label)<label for="{{ $attributes->get('id', $name) }}" class="form-label">{{ $label }}</label>@endif
<textarea name="{{ $name }}" id="{{ $attributes->get('id', $name) }}" rows="{{ $rows }}"
          @if($required) required @endif {{ $attributes->merge(['class' => 'form-control']) }}>{{ old($name, $value) }}</textarea>
@if($help)<div class="form-text">{{ $help }}</div>@endif
@error($name)<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
