@extends('auth.layout')

@section('content')
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

        {{-- Login form --}}
        <form class="form form--login" action="{{ route('password.update') }}" method="POST">
          @csrf

          <input type="hidden" name="token" value="{{ $token }}">

          <h2>Reset password</h2>

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
            @if($errors->has('password'))
              <p class="body">
                <strong>{{ $errors->first('password') }}</strong>
              </p>
            @endif
          </div>

          <div class="form__text">
            <label for="password_confirmation">
              <span>Confirm password</span>
            </label>
            <div>
              <input type="password" id="password_confirmation" name="password_confirmation">
            </div>
          </div>

          <button class="button button__primary button__primary--a" type="submit">
            <span>Reset password</span>
          </button>
        </form>

      </div>
    </div>

  </div>
@endsection
