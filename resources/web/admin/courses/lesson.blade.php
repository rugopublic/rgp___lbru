@extends('admin.layouts.admin')
@section('title', 'Урок · '.$lessonModel->title)
@section('page-title', 'Урок · '.$lessonModel->title)
@section('admin-page', 'courses')
@section('content')
<div class="d-flex justify-content-between align-items-end gap-2 mb-3"><div><div class="text-muted small">{{ $model->title }}</div><h2 class="h4 mb-1">{{ $lessonModel->title }}</h2><div class="admin-section-note">Видео, шаги и существующий quiz-домен редактируются здесь в одном производственном контуре.</div></div><a class="btn btn-outline-secondary btn-sm" href="{{ route('admin.courses.edit', $model) }}"><i class="bi bi-arrow-left me-1"></i>К курсу</a></div>
<x-ui.form.container endpoint="{{ route('admin.courses.lessons.update', [$model, $lessonModel]) }}" identifier="lesson-{{ $lessonModel->id }}" :ajax="true" method="PUT">
    <x-slot:main>@include('admin.partials.lessons.main')</x-slot:main>
    <x-slot:aside>@include('admin.partials.lessons.aside')</x-slot:aside>
</x-ui.form.container>
@endsection
