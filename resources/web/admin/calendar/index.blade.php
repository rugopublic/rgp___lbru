@extends('admin.layouts.admin')
@section('title','Календарь')
@section('page-title','Календарь публикаций')
@section('admin-page','calendar')
@section('content')
<div class="d-flex justify-content-between align-items-end mb-3"><div><div class="text-muted small">Read-only агрегатор</div><h2 class="h5 mb-1">{{ $months[$month] }} {{ $year }}</h2><div class="admin-section-note">Тот же источник данных, которым пользуется scheduler.</div></div><div class="d-flex gap-2"><a class="btn btn-light border" href="{{ route('admin.calendar.index',['month'=>$month===1?12:$month-1,'year'=>$month===1?$year-1:$year]) }}">←</a><a class="btn btn-light border" href="{{ route('admin.calendar.index',['month'=>$month===12?1:$month+1,'year'=>$month===12?$year+1:$year]) }}">→</a></div></div><section class="admin-surface"><div class="admin-surface__body"><div class="admin-list">@forelse($events as $event)<a href="{{ $event['url'] }}" class="admin-list-item text-decoration-none"><div><strong class="text-dark">{{ $event['title'] }}</strong><div class="admin-list-item__meta">{{ $event['type'] }} · {{ $event['status'] }}</div></div><time class="small text-muted">{{ $event['at']->format('d.m.Y H:i') }}</time></a>@empty<div class="admin-empty">В этом месяце событий нет.</div>@endforelse</div></div></section>
@endsection
