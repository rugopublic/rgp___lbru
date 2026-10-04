@extends('admin.layouts.admin')
@section('title','Курс · '.$course->title)
@section('page-title','Курс · '.$course->title)
@section('admin-page','courses')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-3"><div><div class="text-muted small">{{ $course->public_id }}</div><h2 class="h4 mb-1">{{ $course->title }}</h2><div class="admin-section-note">Курс редактируется одним production-form; структура и преподаватели сохраняются отдельными тонкими endpoint-ами.</div></div><a class="btn btn-outline-secondary btn-sm" href="{{ route('courses.show',$course->public_id) }}" target="_blank" rel="noopener"><i class="bi bi-box-arrow-up-right me-1"></i>Публичная страница</a></div>
<x-ui.form.container endpoint="{{ route('admin.courses.update',$course) }}" identifier="course-{{ $course->id }}" :ajax="true" method="PUT" :removable="true" data-delete-endpoint="{{ route('admin.courses.destroy',$course) }}" data-delete-title="{{ $course->title }}" data-delete-label="курс">
    <x-slot:main>@include('admin.partials.edit.courses.main')</x-slot:main>
    <x-slot:aside>@include('admin.partials.edit.courses.aside')</x-slot:aside>
    <x-slot:deletionConsequences><ul class="mb-0"><li><i class="bi bi-dot"></i>Soft delete не удаляет курс или медиа.</li><li><i class="bi bi-dot"></i>Force delete должен происходить только отдельной операцией после проверки последствий.</li></ul></x-slot:deletionConsequences>
</x-ui.form.container>
<script>document.querySelectorAll('[data-course-structure] [data-sortable]').forEach((el)=>{el.dataset.sortableGrouped='true';});</script>
@include('admin.partials.create-title-modal',['id'=>'courseModuleCreateModal','title'=>'Новый модуль','action'=>route('admin.courses.modules.store',$course),'submitLabel'=>'Создать модуль'])
@include('admin.partials.create-course-lesson-modal',['course'=>$course])
@endsection
