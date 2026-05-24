@extends('voyager::layouts.admin')

@section('page_title', __('voyager::generic.roles'))

@section('content')
    <livewire:voyager::role-list />
@stop
