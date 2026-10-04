@extends('admin.layouts.admin')
@section('title','Категория · '.$category->name)
@section('page-title','Категория курса · '.$category->name)
@section('admin-page','courses')
@section('content')
<x-ui.form.container endpoint="{{ route('admin.courses.categories.update',$category) }}" identifier="course-category-{{ $category->id }}" :ajax="true" method="PUT"><x-slot:main><div class="admin-surface"><div class="admin-surface__body"><x-ui.form.input name="name" label="Название" :value="$category->name" required /><x-ui.form.textarea name="description" id="description" label="Описание" :value="$category->description" /></div></div></x-slot:main><x-slot:aside><div class="admin-aside-stack"><div class="sales-callout"><strong>Курсов: {{ $category->courses_count ?? 0 }}</strong></div></div></x-slot:aside></x-ui.form.container>
@endsection
