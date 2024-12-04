<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'Laravel'))</title>


    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">
    @include('partials.navbar')
    <div id="app" class="min-h-screen bg-gray-100 parent">
    
        @yield('content')
    </div>
    @include('partials.footer')

   </body>
</html>
