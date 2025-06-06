<!DOCTYPE html>
<html lang="es-MX">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  @vite(['resources/css/app.css', 'resources/js/main.jsx'])
  <title>GeoEncuestas</title>
</head>
<body>
  <div id="app" class="container"></div>
</body>
</html>