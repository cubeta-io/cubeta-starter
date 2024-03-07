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
                    @if(isset($error))
                        {!! $error !!}
                    @endif
                    @foreach($exceptions as $exception)
                        {!! $exception !!}
                    @endforeach
                    @foreach($logs as $log)
                        @if(is_string($log))
                            <div class="border border-5 p-2 rounded-3 border-primary w-100"
                                 style="text-wrap: nowrap; overflow-x: scroll;white-space: pre;">
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
