@extends('voyager::layouts.admin')

@section('page_title', __('voyager::generic.users'))

@section('content')
    <livewire:voyager::user-list />
@stop
