@extends('voyager::layouts.admin')

@section('page_title', __('voyager::generic.view'))

@section('content')
    <livewire:voyager::bread-read :slug="$slug" :id="$dataTypeContent->getKey()" />
@stop
