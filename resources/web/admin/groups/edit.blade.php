@extends('admin.layouts.admin')
@section('title','Группа · '.$group->name)
@section('page-title','Группа · '.$group->name)
@section('admin-page','groups')
@section('content')
<x-ui.form.container endpoint="{{ route('admin.groups.update',$group) }}" identifier="group-{{ $group->id }}" :ajax="true" method="PUT" :removable="true" data-delete-endpoint="{{ route('admin.groups.destroy',$group) }}" data-delete-title="{{ $group->name }}" data-delete-label="группу"><x-slot:main><div class="admin-surface"><div class="admin-surface__body"><x-ui.form.input name="name" label="Название" :value="$group->name" required /></div></div></x-slot:aside><div></div></x-slot:aside><x-slot:deletionConsequences><ul class="mb-0"><li><i class="bi bi-dot"></i>Участники не удаляются.</li><li><i class="bi bi-dot"></i>Группа станет недоступна в обычных списках до восстановления.</li></ul></x-slot:deletionConsequences></x-ui.form.container>
@endsection
