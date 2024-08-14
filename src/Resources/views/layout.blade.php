<!doctype html>
<html lang="en" style="--chakra-ui-color-mode: light;" data-react-helmet="lang">
<body class="h-100">

@include('CubetaStarter::includes.header')
@include('CubetaStarter::includes.navbar')
@include('CubetaStarter::includes.loading-modal')
@yield('content')

@include('CubetaStarter::includes.footer')
@include('CubetaStarter::includes.logs')
@stack('custom-scripts')
</body>
</html>
