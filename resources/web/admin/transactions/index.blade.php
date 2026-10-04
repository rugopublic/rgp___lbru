@extends('admin.layouts.admin')
@section('title','Транзакции')
@section('page-title','Транзакции')
@section('admin-page','transactions')
@section('content')
<section class="admin-surface"><div class="admin-surface__body">@include('admin.partials.filters')<div class="table-responsive mt-3"><table class="table admin-table"><thead><tr><th>ID</th><th>Покупатель</th><th>Сумма</th><th>Статус</th><th>Дата</th></tr></thead><tbody>@forelse($transactions as $tx)<tr><td><a href="{{ route('admin.transactions.show',$tx->id) }}" class="fw-semibold text-decoration-none">{{ $tx->transaction_id }}</a></td><td>{{ $tx->buyer?->account?->name ?: $tx->buyer?->email }}</td><td>{{ number_format((float)$tx->amount,2,',',' ') }} {{ $tx->currency ?? 'RUB' }}</td><td><span class="admin-status admin-status--published">{{ $tx->status }}</span></td><td>{{ $tx->paid_at?->format('d.m.Y H:i') ?: $tx->created_at?->format('d.m.Y H:i') }}</td></tr>@empty<tr><td colspan="5"><div class="admin-empty">Транзакций пока нет.</div></td></tr>@endforelse</tbody></table></div>{{ $transactions->links() }}</div></section>
@endsection
