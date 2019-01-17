@extends('auth.layout')

@section('content')
  <form method="POST" action="{{ route('password.update') }}">

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

    <label for="password_confirmation">Confirm password</label>
    <input id="password_confirmation" name="password_confirmation" type="password">
    @if($errors->has('password_confirmation'))
      <strong>{{ $errors->first('password_confirmation') }}</strong>
    @endif

    <button type="submit">
      Reset password
    </button>

  </form>
@endsection
