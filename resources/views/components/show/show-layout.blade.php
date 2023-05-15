@props(['title' , 'editRoute' => null])

@extends('layout')
@section('content')
    <main id="main" class="main">
        <div class="page-title">
            <h1 class=""></h1>
            <h2>{{$title}}</h2>
        </div>
        <section class="section profile">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body pt-3">
                            <div class="tab-content p-2">
                                <div class="pb-3">
                                    @if(isset($editRoute))
                                        <a href="{{$editRoute}}" class="p-2">
                                            <button class="btn btn-primary">Edit</button>
                                        </a>
                                    @endif
                                </div>


                                {{$slot}}



                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
@endsection
