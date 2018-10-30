@extends('auth.layout')

@section('content')
    <ol>
        <li>
            Click here to go to the <a href="{{ backend_uri()  }}">Admin Portal</a>.
        </li>
        @guest
        <li>
            Click here to <a href="{{ route('login') }}">Login</a>.
        </li>
        @else
        <li>
            Click here to <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit()">Logout</a>.
            <form id="logout-form" method="POST" action="{{ route('logout') }}" style="display: none;">
                @csrf
            </form>
        </li>
        @endguest
    </ol>
@endsection
