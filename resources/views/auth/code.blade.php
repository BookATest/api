@extends('auth.layout')

@section('content')
  {{-- Login form --}}
  <form
    id="form"
    class="form form--login"
    action="{{ route('login.code') }}"
    method="POST"
    onsubmit="
      event.preventDefault();
      var code1 = document.getElementById('code1').value;
      var code2 = document.getElementById('code2').value;
      var code3 = document.getElementById('code3').value;
      var code4 = document.getElementById('code4').value;
      var code5 = document.getElementById('code5').value;
      document.getElementById('token').value = '' + code1 + code2 + code3 + code4 + code5;
      document.getElementById('form').submit();
    "
  >
    @csrf

    <input type="hidden" name="token" id="token">

    <h2>Login</h2>

    <p>
      We have just sent you an authentication code to your phone.
      <br>
      You should have received it within the next 2 minutes.
    </p>

    <div class="form__code">
      <label for="code1">
        <span>Confirmation code</span>
      </label>

      <div>
        <input type="password" id="code1" maxlength="1">
      </div>
      <div>
        <input type="password" id="code2" maxlength="1">
      </div>
      <div>
        <input type="password" id="code3" maxlength="1">
      </div>
      <div>
        <input type="password" id="code4" maxlength="1">
      </div>
      <div>
        <input type="password" id="code5" maxlength="1">
      </div>

      @if($errors->has('token'))
        <p class="body">
          <strong>{{ $errors->first('token') }}</strong>
        </p>
      @endif
    </div>


    <button class="button button__primary button__primary--a" type="submit">
      <span>Login</span>
    </button>
  </form>
@endsection
