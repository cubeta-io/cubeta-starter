<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
  <meta charset="UTF-8" />
  <meta
    name="viewport"
    content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0"
  />
  <meta http-equiv="X-UA-Compatible" content="ie=edge" />
  <title>{{ trans('site.dashboard.auth.reset_email_subject') }}</title>
</head>
<body>
  <p>{{ trans('site.dashboard.auth.reset_email_message') }}</p>
  <p><strong>{{ $code }}</strong></p>
</body>
</html>
