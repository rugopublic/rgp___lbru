@extends('admin.layouts.admin')
@section('title','Рецепт · '.$recipe->title)
@section('page-title','Рецепт · '.$recipe->title)
@section('admin-page','recipes')
@section('content')
<x-ui.form.container endpoint="{{ route('admin.recipes.update',$recipe) }}" identifier="recipe-{{ $recipe->id }}" :ajax="true" method="PUT" :removable="true" data-delete-endpoint="{{ route('admin.recipes.destroy',$recipe) }}" data-delete-title="{{ $recipe->title }}" data-delete-label="рецепт">
    <x-slot:main>@include('admin.partials.edit.recipes.main')</x-slot:main>
    <x-slot:aside>@include('admin.partials.edit.recipes.aside')</x-slot:aside>
    <x-slot:deletionConsequences><ul class="mb-0"><li><i class="bi bi-dot"></i>Медиа остаются при soft delete.</li><li><i class="bi bi-dot"></i>При окончательном удалении папка рецепта чистится.</li></ul></x-slot:deletionConsequences>
</x-ui.form.container>
@include('admin.partials.create-title-modal',['id'=>'recipeGroupCreateModal','title'=>'Новая группа','action'=>route('admin.recipes.groups.store',$recipe),'submitLabel'=>'Создать группу'])
@include('admin.partials.create-title-modal',['id'=>'recipeIngredientCreateModal','title'=>'Новый ингредиент','action'=>route('admin.recipes.ingredients.store',$recipe),'submitLabel'=>'Добавить ингредиент'])
@endsection
