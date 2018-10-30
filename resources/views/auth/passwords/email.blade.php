@extends('auth.layout')

@section('content')
    @if (session('status'))
        {{ session('status') }}
    @endif

    <form method="POST" action="{{ route('password.email') }}">

        @csrf

        <label for="email">Email</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}">
        @if($errors->has('email'))
            <strong>{{ $errors->first('email') }}</strong>
        @endif

        <button type="submit">
            Send password reset link
        </button>

    </form>
@endsection
