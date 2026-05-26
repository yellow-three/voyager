@extends('voyager::layouts.admin')

@section('page_title', __('voyager::generic.dashboard'))

@section('page_header')
    <div class="mb-6">
        <h1 class="text-2xl font-extrabold text-gray-900">{{ __('voyager::generic.dashboard') }}</h1>
    </div>
@stop

@section('content')
    <livewire:voyager::⚡dashboard />
@stop
