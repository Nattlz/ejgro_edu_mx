document.addEventListener('contextmenu', event => event.preventDefault());
document.addEventListener('keydown', function (e) {
    if (
        e.key === 'F12' ||
        (e.ctrlKey && e.shiftKey && ['I', 'J', 'C'].includes(e.key)) ||
        (e.ctrlKey && e.key === 'U')
    ) {
        e.preventDefault();
    }
});

const alerta = document.getElementById('alertaExito');
if (alerta) {
    setTimeout(() => {
        alerta.style.opacity = '0';
        alerta.style.transform = 'translateY(-10px)';
    }, 4000);
}

const sidebar = document.getElementById('sidebar');
const toggleBtn = document.getElementById('toggleSidebar');
const mainContent = document.getElementById('mainContent');

if (localStorage.getItem('sidebar-collapsed') === 'true') {
    sidebar.classList.add('collapsed');
    mainContent.classList.add('collapsed');
}

if (toggleBtn && sidebar && mainContent) {
    toggleBtn.addEventListener('click', () => {
        sidebar.classList.toggle('collapsed');
        mainContent.classList.toggle('collapsed');

        const isCollapsed = sidebar.classList.contains('collapsed');
        localStorage.setItem('sidebar-collapsed', isCollapsed);
    });
}

function actualizarFechaHora() {
    const ahora = new Date();
    const opcionesFecha = {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    };
    const fecha = ahora.toLocaleDateString('es-MX', opcionesFecha);
    const hora = ahora.toLocaleTimeString('es-MX');

    const contenedor = document.getElementById('fechaHora');
    if (contenedor) {
        contenedor.textContent = `${fecha} — ${hora}`;
    }
}

setInterval(actualizarFechaHora, 1000);
actualizarFechaHora();