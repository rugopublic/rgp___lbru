@extends('admin.layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h2 class="h5 mb-1">Категории товаров</h2>
        <div class="admin-section-note">Категории нужны для понятной коммерческой витрины, без лишнего дерева.</div>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#productCategoryCreateModal">
        <i class="bi bi-plus-lg me-1"></i>Новая категория
    </button>
</div>

<section class="admin-surface">
    <div class="admin-surface__body">
        @include('admin.partials.filters')
        <div class="table-responsive mt-3">
            <table class="table admin-table">
                <thead>
                    <tr><th>Название</th><th>Товаров</th><th>Состояние</th><th class="text-end">Действия</th></tr>
                </thead>
                <tbody>
                @forelse($items as $item)
                    <tr>
                        <td><a class="fw-semibold text-decoration-none" href="{{ route('admin.product.categories.edit', $item) }}">{{ $item->name }}</a></td>
                        <td>{{ $item->products_count }}</td>
                        <td>
                            @if($item->deleted_at)
                                <span class="admin-status admin-status--deleted">Удалена</span>
                            @else
                                <span class="admin-status admin-status--visible">Активна</span>
                            @endif
                        </td>
                        <td class="text-end">
                            @if($item->deleted_at)
                                <form class="d-inline" method="POST" action="{{ route('admin.product.categories.restore', $item) }}">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-success" type="submit">Восстановить</button>
                                </form>
                            @else
                                <a class="btn btn-sm btn-light border" href="{{ route('admin.product.categories.edit', $item) }}">Изменить</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4"><div class="admin-empty">Категорий ещё нет.</div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        {{ $items->links() }}
    </div>
</section>

@include('admin.partials.create-title-modal', ['id'=>'productCategoryCreateModal','title'=>'Новая категория товара','action'=>route('admin.product.categories.store')])
@endsection
