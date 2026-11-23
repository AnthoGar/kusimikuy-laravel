
const menuToggle = document.getElementById('menu-toggle');
const mainMenu = document.getElementById('main-menu');

if (menuToggle && mainMenu) {
    menuToggle.addEventListener('click', () => {
        const isOpen = mainMenu.classList.toggle('is-open');

        menuToggle.setAttribute('aria-expanded', String(isOpen));
        menuToggle.setAttribute(
            'aria-label',
            isOpen ? 'Cerrar menú' : 'Abrir menú'
        );
    });

    mainMenu.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => {
            mainMenu.classList.remove('is-open');
            menuToggle.setAttribute('aria-expanded', 'false');
            menuToggle.setAttribute('aria-label', 'Abrir menú');
        });
    });
}
