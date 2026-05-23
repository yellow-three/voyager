@extends('voyager::master')

@section('page_title', __('voyager::generic.roles'))

@section('content')
    @php $roleId = isset($role) ? $role->id : null; @endphp
    <livewire:voyager::⚡role-form :id="$roleId" />
@stop
