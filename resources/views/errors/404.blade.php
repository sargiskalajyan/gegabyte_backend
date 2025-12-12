@extends('errors.layout')

@section('content')
    @php
        $code = 404;
        $message = 'Page Not Found';
        $description = 'Sorry! The page you are looking for does not exist.';
    @endphp
@endsection
