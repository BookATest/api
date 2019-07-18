@extends('layout')

@section('content')
  {{-- Login form --}}
  <form class="form form--login" action="{{ route('password.email') }}" method="POST">
    @csrf

    @if (session('status'))
      <h2>{{ session('status') }}</h2>
    @endif

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

    <button class="button button__primary button__primary--a" type="submit">
        <span>Send password reset link</span>
    </button>
  </form>
@endsection
