@extends('CubetaStarter::layout')
@section('content')

    <div class="text-center">
        <div class="text-center">
            <img class="greeting-logo" src="{{asset("$assetsPath/images/logo.png")}}">
        </div>
        <div>
            <img class="logo-under-text" src="{{asset("$assetsPath/images/cubeta.png")}}" alt="">
        </div>
        <p class="p-2">Even the hardest puzzles have a solution!</p>
    </div>


    <div class="text-center">
        <a href="{{route('cubeta-starter.generate-full.page')}}" class="get-started">Start Generating</a>
    </div>

@endsection
