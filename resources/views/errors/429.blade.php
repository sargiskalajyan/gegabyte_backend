@extends('errors.layout')

@section('content')
    @php
        $code = 429;
        $message = 'Too Many Requests';
        $description = 'Ooooups! Looks like your did too many requests.';
    @endphp
@endsection


