@props(['title' => 'Cubeta Admin Dashboard'])
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    {{$slot}}
    <title>{{$title}}}</title>
    <link href="{{asset('css/style.css')}}" rel="stylesheet">
</head>
