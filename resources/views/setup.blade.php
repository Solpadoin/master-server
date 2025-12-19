<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Setup - {{ config('app.name', 'Master Server') }}</title>
    @vite(['resources/css/app.css', 'resources/js/setup.js'])
</head>
<body class="bg-gray-900">
    <div id="setup-app"></div>
</body>
</html>
