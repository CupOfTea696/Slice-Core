@extends('errors::layout')

@section('content')
<div class="container">
    <main class="jumbotron">
        <h1 class="display-1">
            <strong>@yield('code')</strong> @yield('title')
        </h1>
        <div class="lead">
            @yield('message')
        </div>
        <hr>
        <a href="/" class="btn btn-outline-primary" role="button">Return to Home</a>
    </main>
</div>
@endsection
