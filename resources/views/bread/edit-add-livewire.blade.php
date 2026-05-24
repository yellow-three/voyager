@extends('voyager::layouts.admin')

@section('page_title', __('voyager::generic.edit'))

@section('content')
    <livewire:voyager::bread-form :slug="$dataType->slug" :id="$dataTypeContent->getKey() ?? null" />
@stop
