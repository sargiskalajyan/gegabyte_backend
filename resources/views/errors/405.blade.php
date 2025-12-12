@extends('errors.layout')

@section('content')
    @php
        $code = 405;
        $message = 'Method Not Allowed';
        $description = 'Ooooups! Looks like you got lost.';
    @endphp
@endsection

