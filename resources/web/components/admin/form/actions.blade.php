@props(['cancel' => null, 'submit' => 'Сохранить'])
<div class="d-flex flex-wrap gap-2 justify-content-end mt-4">
    @if($cancel)<a href="{{ $cancel }}" class="btn btn-outline-secondary">Отмена</a>@endif
    <button type="submit" class="btn btn-primary">{{ $submit }}</button>
</div>
