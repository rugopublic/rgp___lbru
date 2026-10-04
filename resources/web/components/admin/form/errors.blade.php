@if ($errors->any())
<div class="alert alert-danger" role="alert">
    <div class="fw-semibold mb-2">Проверьте заполнение формы:</div>
    <ul class="mb-0 ps-3">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif
