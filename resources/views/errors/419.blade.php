@extends('errors.layout')

@section('content')
    @php
        $code = 419;
        $message = 'Page expired';
        $description = 'Ooooups! Looks like your token has expired.';
    @endphp
@endsection


