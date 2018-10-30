@extends('auth.layout')

@section('content')
    <form method="POST" action="{{ route('login') }}">

        @csrf

        <label for="email">Email</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}">
        @if($errors->has('email'))
            <strong>{{ $errors->first('email') }}</strong>
        @endif

        <label for="password">Password</label>
        <input id="password" name="password" type="password">
        @if($errors->has('password'))
            <strong>{{ $errors->first('password') }}</strong>
        @endif
        <a href="{{ route('password.request') }}">Forgotten password?</a>

        <button type="submit">
            @if(config('ck.otp_enabled'))
                Send code
            @else
                Login
            @endif
        </button>

    </form>
@endsection
