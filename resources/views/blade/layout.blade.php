<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" data-bs-theme="light">
@include ("includes.header")
<body>
  @include ("includes.navbar")
  @include ("includes.sidebar")

  @yield ("content")

  @include ("includes.footer")
</body>
</html>
