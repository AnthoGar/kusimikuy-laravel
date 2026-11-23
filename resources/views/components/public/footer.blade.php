<footer class="site-footer">
    <div class="container footer-content">

        <div class="footer-brand">
            <a href="{{ url('/') }}" class="brand footer-logo">
                <img src="{{ asset('img/logo.jpg') }}" alt="">
                <span>KusiMikuy</span>
            </a>

            <p>
                Conectamos alimentos, creamos impacto.
                Tecnología al servicio de la comunidad.
            </p>
        </div>

        <div class="footer-links">
            <h3>Explora</h3>
            <a href="#proposito">Nuestro propósito</a>
            <a href="#como-funciona">Cómo funciona</a>
            <a href="#comunidad">Comunidad</a>
        </div>

        <div class="footer-links">
            <h3>Conócenos</h3>
            <p>Una iniciativa para aprovechar mejor los alimentos y fortalecer comunidades en Cusco.</p>
        </div>

    </div>

    <div class="container footer-bottom">
        <p>© {{ date('Y') }} KusiMikuy. Todos los derechos reservados.</p>
        <span>Hecho con propósito en Cusco, Perú.</span>
    </div>
</footer>