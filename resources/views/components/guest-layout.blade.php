<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/logo_nomitech.svg') }}">
    <link rel="shortcut icon" type="image/svg+xml" href="{{ asset('images/logo_nomitech.svg') }}">
</head>

<body
    class="flex items-center justify-center min-h-screen relative px-4 py-12 overflow-x-hidden bg-[#F9FAFB] font-sans">

    {{ $slot }}

</body>

</html>