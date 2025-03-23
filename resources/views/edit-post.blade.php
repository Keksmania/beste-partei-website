@extends('layouts.app')

@section('title', 'Edit Event')

@section('content')
<div class="background-container" style="text-align: center">
        <p class="h3 m-3">Post bearbeiten</p>
        <div style="justify-content: center; display:block;">
            <div class="content-container">
    <createpost :post-id="{{ $postId }}" :is-edit-mode="true"></createpost>
    </div>
        </div>
    </div>
@endsection
