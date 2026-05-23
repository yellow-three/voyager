@extends('voyager::layouts.admin')

@section('page_title', __('voyager::generic.edit'))

@section('content')
    <livewire:voyager::⚡bread-form :slug="$slug" :id="$id ?? null" />
@stop
