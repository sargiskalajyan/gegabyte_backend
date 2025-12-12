@extends('errors.layout')

@section('content')
    @php
        $code = 401;
        $message = 'Page Not Found';
        $description = 'Ooooups! Looks like you got lost.';
    @endphp
@endsection
