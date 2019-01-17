@extends('auth.layout')

@section('content')
  {{-- Login form --}}
  <form class="form form--login" action="{{ route('login') }}" method="POST">
    @csrf

    <h2>Login</h2>

    <div class="form__text">
      <label for="email">
        <span>Email</span>
      </label>
      <div>
        <input type="email" id="email" name="email" value="{{ old('email') }}">
      </div>
      @if($errors->has('email'))
        <p class="body">
          <strong>{{ $errors->first('email') }}</strong>
        </p>
      @endif
    </div>

    <div class="form__text">
      <label for="password">
        <span>Password</span>
      </label>
      <div>
        <input type="password" id="password" name="password">
      </div>
    </div>

    <p class="body">
      <a href="{{ route('password.request') }}">Forgotten password?</a>
    </p>

    <button class="button button__primary button__primary--a" type="submit">
      @if (config('bat.otp_enabled'))
        <span>Send code</span>
      @else
        <span>Confirm</span>
      @endif
    </button>
  </form>
@endsection
