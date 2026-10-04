@extends('admin.layouts.admin')
@section('title','Статья · '.$article->title)
@section('page-title','Статья · '.$article->title)
@section('admin-page','articles')
@section('content')
<x-ui.form.container endpoint="{{ route('admin.articles.update',$article) }}" identifier="article-{{ $article->id }}" :ajax="true" method="PUT" :removable="true" data-delete-endpoint="{{ route('admin.articles.destroy',$article) }}" data-delete-title="{{ $article->title }}" data-delete-label="статью">
    <x-slot:main>@include('admin.partials.edit.articles.main')</x-slot:main>
    <x-slot:aside>@include('admin.partials.edit.articles.aside')</x-slot:aside>
    <x-slot:deletionConsequences><ul class="mb-0"><li><i class="bi bi-dot"></i>Статья уйдёт из рабочего списка, но медиа сохранятся при soft delete.</li><li><i class="bi bi-dot"></i>Inline media очищается окончательно только при force delete.</li></ul></x-slot:deletionConsequences>
</x-ui.form.container>
@endsection
