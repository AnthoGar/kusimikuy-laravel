<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="description" content="KusiMikuy conecta donantes, comedores populares y colaboradores para rescatar alimentos y generar impacto en Cusco.">

    <title>@yield('title', 'KusiMikuy | Conectamos alimentos, creamos impacto')</title>

    <link rel="icon" href="{{ asset('img/logo.jpg') }}" type="image/jpeg">
    <link rel="stylesheet" href="{{ asset('css/kusimikuy.css') }}">

    @stack('styles')
</head>

<body>

    @include('components.public.navbar')

    <main>
        @yield('content')
    </main>

    @include('components.public.footer')

    <script src="{{ asset('js/kusimikuy.js') }}" defer></script>
    @stack('scripts')
</body>

</html>