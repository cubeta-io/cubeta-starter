@extends('layout')
@section('content')
    <x-show-layout title="{modelName} With ID {{{modelVariable}->id}}" editRoute="{editRoute}">
        
        {components}

    </x-show-layout>
@endsection