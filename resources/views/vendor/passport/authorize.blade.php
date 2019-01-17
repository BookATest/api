@extends('auth.layout')

@section('content')
  <h1>Authorisation request</h1>

  <p>{{ $client->name }} is requesting permission to access your account.</p>

  <form method="POST" action="{{ url('/oauth/authorize') }}">

    @csrf
    {{ method_field('DELETE') }}

    <input type="hidden" name="state" value="{{ $request->state }}">
    <input type="hidden" name="client_id" value="{{ $client->id }}">

    <button type="submit">Cancel</button>

  </form>

  <form method="POST" action="{{ url('/oauth/authorize') }}">

    @csrf

    <input type="hidden" name="state" value="{{ $request->state }}">
    <input type="hidden" name="client_id" value="{{ $client->id }}">

    <button type="submit">Authorise</button>

  </form>
@endsection
