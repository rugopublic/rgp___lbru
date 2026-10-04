@extends('admin.layouts.admin')
@section('title','Товар · '.$product->title)
@section('page-title','Товар · '.$product->title)
@section('admin-page','products')
@section('content')
<x-ui.form.container endpoint="{{ route('admin.products.update',$product) }}" identifier="product-{{ $product->id }}" :ajax="true" method="PUT" :removable="true" data-delete-endpoint="{{ route('admin.products.destroy',$product) }}" data-delete-title="{{ $product->title }}" data-delete-label="товар">
    <x-slot:main>@include('admin.partials.edit.products.main')</x-slot:main>
    <x-slot:aside>@include('admin.partials.edit.products.aside')</x-slot:aside>
    <x-slot:deletionConsequences><ul class="mb-0"><li><i class="bi bi-dot"></i>Покупки сохранятся в истории.</li><li><i class="bi bi-dot"></i>Файл товара не удаляется при soft delete.</li></ul></x-slot:deletionConsequences>
</x-ui.form.container>
@endsection
