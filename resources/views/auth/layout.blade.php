<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- CSRF Token -->
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>{{ config('app.name', 'Laravel') }}</title>

  <!-- Styles -->
  <link href="{{ asset('css/auth.css') }}" rel="stylesheet">
</head>
<body>
<div class="base-layout">

  {{-- Header --}}
  <div class="base-layout__header base-layout__header--login">
    <div class="header">
      <a href="{{ route('home') }}">
        <img src="{{ asset('img/logo-bookatest.png') }}" alt="{{ config('app.name') }} logo">
      </a>
    </div>
  </div>

  {{-- Main content --}}
  <div class="base-layout__main base-layout__main--no-sidebar">
    <div class="main">
      @yield('content')
    </div>
  </div>
</div>
</body>
</html>
