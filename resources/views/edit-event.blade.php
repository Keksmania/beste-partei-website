@extends('layouts.app')

@section('title', 'Edit Event')

@section('content')

    <createpost :event-id="{{ $eventId }}" :is-edit-mode="true"></createpost>
@endsection
