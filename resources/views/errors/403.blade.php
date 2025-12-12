@extends('errors.layout')

@section('content')
    @php
        $code = 403;
        $message = 'Forbidden';
        $description = 'Ooooups! Looks like you got lost.';
    @endphp
@endsection
