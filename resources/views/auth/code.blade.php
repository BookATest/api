@extends('auth.layout')

@section('content')
    <form method="POST" action="{{ route('login.code') }}">

        @csrf

        <label for="token">Verification code</label>
        <input id="token" name="token" type="password">
        @if($errors->has('token'))
        <strong>{{ $errors->first('token') }}</strong>
        @endif

        <button type="submit">
            Login
        </button>

    </form>
@endsection
