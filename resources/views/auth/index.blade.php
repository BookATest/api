@extends('auth.layout')

@section('content')
    <ul>
        <li>
            Click here to go to the <a href="{{ backend_uri()  }}">Admin Portal</a>.
        </li>
        <li>
            Click here to go to the <a href="{{ route('docs.index')  }}">API Docs</a>.
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
    </ul>
@endsection
