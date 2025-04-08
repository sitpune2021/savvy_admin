<title>{{ $title ?? config('app.name') }}</title>
<meta name="description" content="{{ $description ?? 'Default description' }}">
<meta name="keywords" content="{{ $keywords ?? 'default, keywords' }}">
<meta name="author" content="Niranjan Kakatkar - SIT Solutions">
<meta property="og:title" content="{{ $title ?? config('app.name') }}">
<meta property="og:description" content="{{ $description ?? 'Default description' }}">
<meta property="og:type" content="CRM">
<meta property="og:url" content="{{ url()->current() }}">
<link rel="shortcut icon" href="{{ asset('img/favicon.png')}}">
