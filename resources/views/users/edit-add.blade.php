@extends('voyager::master')

@section('page_title', __('voyager::generic.users'))

@section('content')
    @php $userId = isset($dataTypeContent) ? $dataTypeContent->id : (isset($user) ? $user->id : null); @endphp
    <livewire:voyager::⚡user-form :id="$userId" />
@stop
