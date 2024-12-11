@extends('layouts.app')

@section('title', 'Create Event')

@section('content')
    <div class="background-container" style="text-align: center">
        <p class="h3 m-3">Event erstellen</p>
        <div style="justify-content: center; display:block;">
            <div class="content-container">
                <createpost></createpost>
            </div>
        </div>
    </div>
@endsection
