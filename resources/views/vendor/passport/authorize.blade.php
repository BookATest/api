@extends('auth.layout')

@section('content')
  <div class="form form--login" action="{{ route('login') }}" method="POST">
    @csrf

    <h2>Authorisation request</h2>

    <p class="body">{{ $client->name }} is requesting permission to access your account.</p>

    <form method="POST" action="{{ url('/oauth/authorize') }}" style="display: inline-block">
      @csrf
      {{ method_field('DELETE') }}

      <input type="hidden" name="state" value="{{ $request->state }}">
      <input type="hidden" name="client_id" value="{{ $client->id }}">

      <button class="button button__primary button__primary--a" type="submit">
          <span>Cancel</span>
      </button>
    </form>

    <form method="POST" action="{{ url('/oauth/authorize') }}" style="display: inline-block">
      @csrf

      <input type="hidden" name="state" value="{{ $request->state }}">
      <input type="hidden" name="client_id" value="{{ $client->id }}">

      <button class="button button__primary button__primary--a" type="submit">
        <span>Authorise</span>
      </button>
    </form>
  </div>
@endsection
