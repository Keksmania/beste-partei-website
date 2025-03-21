@extends('layouts.app')

@section('title', $post->name)

@section('content')
<div class="container mt-5 slide-down">
    <div class="mb-3">{{ $post->date }}</div>
    <h3>{{ $post->name }}</h3>
    <img 
        style="max-width: 20em; min-width: 20em" 
        src="{{ $post->image ? asset('storage/' . $post->image) : url('/images/1.jpg') }}" 
        alt="Image">
    <div class="mt-3">{!! $post->description !!}</div>
</div>
@endsection
