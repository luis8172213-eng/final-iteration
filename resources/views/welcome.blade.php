@extends('layouts.app')

@section('title', 'Campus Reserve - Home')

@section('content')
    @include('components.hero-section')
    @include('components.features-section')
    @include('components.why-choose-section')
    @include('components.cta-section')
@endsection
