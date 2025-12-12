@extends('errors.layout')

@section('content')
    @php
        $code = 503;
        $message = 'Server Error';
        $description = 'Ooooups! Looks like the service is unavailable.';
    @endphp
@endsection


