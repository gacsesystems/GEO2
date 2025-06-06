<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>GEO Encuestas</title>
  @vite(['resources/css/app.css', 'resources/js/main.jsx'])
</head>
<body>
  <div id="app"></div>
  {{-- <script src="{{ mix('js/app.js') }}"></script> --}}
</body>
</html>