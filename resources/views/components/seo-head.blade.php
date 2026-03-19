@php
    $pageTitle = trim((string) ($title ?? $__env->yieldContent('title', '')));
    $appTitle = strtoupper(config('app.name'));
@endphp

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $pageTitle !== '' ? $pageTitle.' - '.$appTitle : $appTitle }}</title>
<meta name="description" content="{{ trim($__env->yieldContent('meta_description', 'Simple issue tracking for small teams. Track work, assign ownership, and keep discussions where they belong.')) }}">
<link rel="icon" href="/favicon.ico">
