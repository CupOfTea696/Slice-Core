@extends('errors::error')

@section('code', 503)
@section('title', 'Service Unavailable')
@section('message', $exception->getMessage() ?: 'Sorry, we are doing some maintenance. Please check back soon.')
