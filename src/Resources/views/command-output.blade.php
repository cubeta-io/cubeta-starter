@extends('CubetaStarter::layout')
@section('content')
    @include('CubetaStarter::includes.sidebar')
    @php
        $output = \Illuminate\Support\Facades\Cache::get('logs') ?? '' ;
        \Illuminate\Support\Facades\Cache::flush();
    @endphp
    <main>
        <div class="card">
            <div class="card-header">
                <h1>Command Log</h1>
            </div>
            <div class="card-body">
                <pre class="border border-secondary">
                    {{$error ?? $output}}
                </pre>
            </div>
        </div>
    </main>
@endsection
