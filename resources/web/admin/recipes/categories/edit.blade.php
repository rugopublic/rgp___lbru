@extends('admin.layouts.app')

@section('content')
<x-ui.form.container endpoint="{{ route('admin.recipe.categories.update', $category) }}" identifier="recipe-category-{{ $category->id }}" :ajax="true" method="PUT" :removable="true" data-delete-endpoint="{{ route('admin.recipe.categories.destroy', $category) }}" data-delete-title="{{ $category->name }}" data-delete-label="категорию">
    <x-slot:main>
        <div class="admin-editor-stack">
            <section class="admin-surface">
                <div class="admin-surface__body">
                    <x-ui.form.input name="name" label="Название" :value="$category->name" required maxlength="255" />
                    <x-ui.form.textarea name="description" id="description" label="Описание" :value="$category->description" />
                </div>
            </section>
        </div>
    </x-slot:main>
    <x-slot:aside>
        <div class="admin-aside-stack">
            <section class="sales-callout">
                <strong>Рецептов: {{ $category->recipes_count ?? 0 }}</strong>
                <div class="admin-section-note mt-1">Категория остаётся компактной и не превращается в отдельную подсистему.</div>
            </section>
        </div>
    </x-slot:aside>
    <x-slot:deletionConsequences>
        <ul class="mb-0"><li><i class="bi bi-dot"></i>Категория уйдёт из обычных списков.</li><li><i class="bi bi-dot"></i>Контент не будет физически удалён.</li></ul>
    </x-slot:deletionConsequences>
</x-ui.form.container>
@endsection
