@extends('admin.layouts.admin')
@section('title','Новый пользователь')
@section('page-title','Новый пользователь')
@section('admin-page','users')
@section('content')
<x-ui.form.container endpoint="{{ route('admin.users.store') }}" identifier="user-create" :ajax="true"><x-slot:main><div class="admin-editor-stack"><div class="admin-surface"><div class="admin-surface__body"><x-ui.form.input name="name" label="Имя" required/><x-ui.form.input name="email" label="Email" type="email" required/><x-ui.form.input name="password" label="Пароль" type="password" required minlength="12" autocomplete="new-password"/><x-ui.form.input name="password_confirmation" label="Повторите пароль" type="password" required autocomplete="new-password"/><label class="form-label">Группы</label>@foreach($groups as $group)<div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="group_ids[]" value="{{ $group['value'] }}" id="group-{{ $group['value'] }}"><label class="form-check-label" for="group-{{ $group['value'] }}">{{ $group['label'] }}</label></div>@endforeach</div></div></div></x-slot:main><x-slot:aside><div class="admin-aside-stack"><div class="sales-callout"><strong>Безопасность</strong><div class="admin-section-note mt-1">Минимум 12 символов; пароль в готовом виде нигде не показывается.</div></div></div></x-slot:aside></x-ui.form.container>
@endsection
