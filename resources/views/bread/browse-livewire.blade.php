@extends('voyager::layouts.admin')

@section('page_title', __('voyager::generic.browse'))

@section('content')
    <livewire:voyager::bread-table :slug="$slug" />
@stop
