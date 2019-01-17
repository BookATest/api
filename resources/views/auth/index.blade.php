@extends('auth.layout')

@section('content')
    <div class="base-layout">

        {{-- Header --}}
        <div class="base-layout__header base-layout__header--login">
            <div class="header">
                <a href="{{ route('home') }}"><img src="{{ asset('img/logo-bookatest.png') }}"
                                                   alt="{{ config('app.name') }} logo"></a>
            </div>
        </div>

        {{-- Main content --}}
        <div class="base-layout__main base-layout__main--no-sidebar">
            <div class="main">

                <div class="base-layout--main-container">

                    <p class="body">Click here to go to the <a href="{{ backend_uri()  }}">Admin Portal</a>.</p>

                    <p class="body">Click here to go to the <a href="{{ route('docs.index')  }}">API Docs</a>.</p>

                    @guest
                        <p class="body">Click here to <a href="{{ route('login') }}">Login</a>.</p>
                    @else
                        <p class="body">Click here to <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit()">Logout</a>.</p>
                        <form id="logout-form" method="POST" action="{{ route('logout') }}" style="display: none;">
                            @csrf
                        </form>
                    @endguest

                </div>

            </div>
        </div>

    </div>
@endsection
