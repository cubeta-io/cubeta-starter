@extends('CubetaStarter::layout')
@section('content')
    @include('CubetaStarter::includes.sidebar')
    @php
        $logs = \Illuminate\Support\Facades\Cache::get('logs') ?? [] ;
        $exceptions = \Illuminate\Support\Facades\Cache::get('exceptions') ?? [];
        \Illuminate\Support\Facades\Cache::flush();
    @endphp
    <main>
        <div class="card">
            <div class="card-header">
                <h1>Generating Log</h1>
            </div>
            <div class="card-body">
                <div class="d-flex flex-column justify-content-between gap-3">
                    @foreach($exceptions as $exception)
                        {!! $exception !!}
                    @endforeach
                    @foreach($logs as $log)
                        @if(is_string($log))
                            <div class="border p-3 rounded-3 border-primary">
                                {{$log}}
                            </div>
                        @else
                            {!! $log->getHtml() !!}
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </main>
@endsection
