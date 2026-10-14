<head>
  <meta charset="utf-8" />
  <meta content="width=device-width, initial-scale=1.0" name="viewport" />
  <title>
    {{ config('cubeta-starter.project_name') }} - {{ trans('site.dashboard.name') }}
  </title>
  <meta content="" name="description" />
  <meta content="" name="keywords" />
  <link href="{{ asset("images/cubeta-logo.png") }}" rel="icon" />
  <link href="{{ asset("images/cubeta-logo.png") }}" rel="apple-touch-icon" />
  @vite (["resources/js/cubeta-starter.js", "resources/css/cubeta-starter.css"])
</head>
