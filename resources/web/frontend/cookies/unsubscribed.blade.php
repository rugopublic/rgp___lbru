@extends('frontend.layouts.main')
@section('title', 'Рассылка отключена')
@section('content')
<div class="container py-5"><div class="row justify-content-center"><div class="col-md-7"><div class="card border-0 shadow-sm"><div class="card-body p-4 p-lg-5 text-center"><div class="display-6 mb-3">✓</div><h1 class="h3">Маркетинговые письма отключены</h1><p class="text-muted mb-4">Мы больше не будем отправлять вам маркетинговые рассылки. При желании настройку можно изменить в профиле.</p><a href="{{ route('home') }}" class="btn btn-primary">Вернуться на сайт</a></div></div></div></div></div>
@endsection
