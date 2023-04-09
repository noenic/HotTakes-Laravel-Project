@extends('layouts.app')
@section('content')

<div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1 class="text-center my-5" style="color: #dc3545;">Liste des sauces</h1>

                <div class="d-flex justify-content-center mb-3" style="padding: 20px;">
                        @foreach ($sauces as $sauce)
                        <a href="{{ route('sauces.show', $sauce->id) }}" class="mx-1 text-reset text-decoration-none link">
                            <div class="sauce-container d-flex flex-column align-items-center" style="padding: 20px;">
                                <div class="sauce-image">
                                    <img src="{{ $sauce->imageUrl }}" alt="{{ $sauce->name }}" height="194px" width="194px" style="border-radius: 10px;">
                                </div>
                                <div class="sauce-info" style="text-align: center;">
                                    <h2>{{ $sauce->name }}</h2>
                                    <p>Heat: {{ $sauce->heat }}/10</p>
                                </div>
                            </div>
                        </a>
                         @endforeach
                </div>
                <div class="d-flex justify-content-center">
                    {!! $sauces->links('pagination::bootstrap-4') !!}
                </div>



@endsection

<style>

.link:hover {
    transform: scale(1.1);
    transition: 0.2s;
}



</style>