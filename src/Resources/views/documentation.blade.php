@extends('CubetaStarter::layout')
@section('content')
    @include('CubetaStarter::includes.sidebar')
    <main class="main">
        <div class="card">
            <div class="card-body">
                <pre>
                    {!! $docs !!}
                </pre>
            </div>
        </div>
    </main>
@endsection
