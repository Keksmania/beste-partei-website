<!-- resources/views/start.blade.php -->
@extends('layouts.app')

@section('title', 'Start')

@section('content')
    <div class="background-container" style="text-align: center">
        <p class="h1 m-3">Studentische Politik-Revolution</p>
        <div style="justify-content: center; display:block;">
            <div class="content-container">
                <h3>Eventkalender</h3>
                <postlist :event="true"></postlist>
             <!--   <h3>Blog Posts</h3> -->
            <!--    <postlist :event="false"></postlist> -->
                <p>Aktuell ist diese Seite noch im Aufbau.</p>
            </div>
           
        </div>
        
        
    </div>
 

@endsection

 