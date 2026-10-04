@extends('admin.layouts.admin')
@section('title','Транзакция · '.$transaction->transaction_id)
@section('page-title','Транзакция · '.$transaction->transaction_id)
@section('admin-page','transactions')
@section('content')
<div class="row g-4"><div class="col-lg-7"><section class="admin-surface"><div class="admin-surface__body"><dl class="row mb-0"><dt class="col-sm-4">Покупатель</dt><dd class="col-sm-8">{{ $transaction->buyer?->account?->name ?: $transaction->buyer?->email }}</dd><dt class="col-sm-4">Сумма</dt><dd class="col-sm-8">{{ number_format((float)$transaction->amount,2,',',' ') }} {{ $transaction->currency ?? 'RUB' }}</dd><dt class="col-sm-4">Статус</dt><dd class="col-sm-8">{{ $transaction->status }}</dd><dt class="col-sm-4">Оплата</dt><dd class="col-sm-8">{{ $transaction->paid_at?->format('d.m.Y H:i:s') ?: '—' }}</dd></dl></div></section></div><div class="col-lg-5"><section class="admin-surface"><div class="admin-surface__head"><h3 class="h6 mb-0">Данные платёжного провайдера</h3></div><div class="admin-surface__body"><pre class="small mb-0" style="white-space:pre-wrap">{{ json_encode($transaction->payment_details, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT) }}</pre></div></section></div></div>
@endsection
