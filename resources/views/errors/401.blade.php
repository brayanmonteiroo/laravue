@extends('errors::minimal')

@section('title', __('Unauthorized'))
@section('code', '401')
@section('message', __('Unauthorized'))

@section('action')
    <a class="btn btn-primary" href="{{ route('login') }}">{{ __('Log in') }}</a>
    <a class="btn btn-ghost" href="{{ route('home') }}">{{ __('Back to home') }}</a>
@endsection
