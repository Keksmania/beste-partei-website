@extends('layouts.app')

@section('title', $event->name )

@section('content')
<div class="container mt-5 slide-down child">
    <div class="mb-3">{{ $event->date }}</div>
    <h3 >{{ $event->name }}</h3>
    <img style="max-width: 20em; min-width: 20em" src="/images/{{$event->id}}.jpg" alt="Image">
    <div class="mt-3">{!! $event->description !!}</div>
</div>

@endsection
