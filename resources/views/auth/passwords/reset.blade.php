@extends('layout')

@section('content')
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
@endsection
