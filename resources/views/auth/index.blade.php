@extends('layout')

@section('content')
  <div class="base-layout--main-container">

    <p class="body">Click here to go to the <a href="{{ backend_uri()  }}">Admin Portal</a>.</p>

    <p class="body">Click here to go to the <a href="{{ route('docs.index')  }}">API Docs</a>.</p>

    @guest
      <p class="body">Click here to <a href="{{ route('login') }}">Login</a>.</p>
    @else
      <p class="body">
        Click here to
        <a
          href="{{ route('logout') }}"
          onclick="event.preventDefault(); document.getElementById('logout-form').submit()"
        >Logout</a>.
      </p>
      <form id="logout-form" method="POST" action="{{ route('logout') }}" style="display: none;">
        @csrf
      </form>
    @endguest

  </div>
@endsection
