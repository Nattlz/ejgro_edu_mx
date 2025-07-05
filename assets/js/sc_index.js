document.addEventListener('DOMContentLoaded', () => {
    const menuButton = document.getElementById('menuButton');
    const menuDropdown = document.getElementById('menuDropdown');

    function toggleMenu() {
        if (menuDropdown) {
            menuDropdown.classList.toggle('hidden');
        }
    }

    if (menuButton && menuDropdown) {
        menuButton.addEventListener('click', (e) => {
            e.stopPropagation();
            toggleMenu();
        });

        window.addEventListener('click', (e) => {
            if (!menuButton.contains(e.target) && !menuDropdown.contains(e.target)) {
                if (!menuDropdown.classList.contains('hidden')) {
                    menuDropdown.classList.add('hidden');
                }
            }
        });
    }

    function mostrarTab(tab) {
        document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
        document.getElementById(`tab-${tab}`).classList.remove('hidden');

        document.querySelectorAll('.btn-tab').forEach(btn => btn.classList.remove('active-tab'));
        document.getElementById(`btn-${tab}`).classList.add('active-tab');

        const seccionCursos = document.getElementById('seccionCursos');
        if (seccionCursos) seccionCursos.classList.remove('hidden');

        const modal = document.getElementById('modalContacto');
        if (modal) modal.classList.add('hidden');
    }

    window.mostrarTab = mostrarTab;

    window.mostrarModalContacto = function () {
        const modal = document.getElementById('modalContacto');
        const seccionCursos = document.getElementById('seccionCursos');
        if (modal) modal.classList.remove('hidden');
        if (seccionCursos) seccionCursos.classList.add('hidden');

        if (menuDropdown && !menuDropdown.classList.contains('hidden')) {
            menuDropdown.classList.add('hidden');
        }
    };

    window.cerrarModalContacto = function () {
        const modal = document.getElementById('modalContacto');
        const seccionCursos = document.getElementById('seccionCursos');
        if (modal) modal.classList.add('hidden');
        if (seccionCursos) seccionCursos.classList.remove('hidden');
    };

    const buscador = document.getElementById('buscadorCursos');
    if (buscador) {
        buscador.addEventListener('input', function () {
            const valor = this.value.toLowerCase();
            const tabs = ['finalizados', 'activos', 'proximos'];
            tabs.forEach(tab => {
                const contenedor = document.getElementById(`tab-${tab}`);
                const visible = !contenedor.classList.contains('hidden');
                if (visible) {
                    const cursos = contenedor.querySelectorAll('.grid > div');
                    cursos.forEach(card => {
                        const texto = card.textContent.toLowerCase();
                        card.style.display = texto.includes(valor) ? 'block' : 'none';
                    });
                }
            });
        });
    }

    const switchTema = document.getElementById('switchTema');
    if (switchTema) {
        const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        const modoGuardado = localStorage.getItem('modoOscuro');

        if (modoGuardado === 'true' || (modoGuardado === null && prefersDark)) {
            document.documentElement.classList.add('dark');
            switchTema.checked = true;
        }

        switchTema.addEventListener('change', function () {
            if (this.checked) {
                document.documentElement.classList.add('dark');
                localStorage.setItem('modoOscuro', 'true');
            } else {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('modoOscuro', 'false');
            }
        });
    }

    const formContacto = document.getElementById('formContacto');
    if (formContacto) {
        formContacto.addEventListener('submit', function (e) {
            e.preventDefault();

            const nombre = formContacto.querySelector('input[name="nombre"]');
            const correo = formContacto.querySelector('input[name="correo"]');
            const mensaje = formContacto.querySelector('textarea[name="mensaje"]');

            if (!nombre.value.trim() || !correo.value.trim() || !mensaje.value.trim()) {
                mostrarToastError("Por favor completa todos los campos.");
                return;
            }

            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(correo.value.trim())) {
                mostrarToastError("Por favor ingresa un correo electrónico válido.");
                return;
            }

            mostrarToast("Mensaje enviado correctamente.");
            formContacto.reset();
            cerrarModalContacto();
        });
    }

    function mostrarToast(mensaje) {
        const toast = document.getElementById('toastExito');
        if (!toast) return;

        toast.querySelector('span').textContent = mensaje;
        toast.classList.remove('opacity-0', 'pointer-events-none');
        toast.classList.add('opacity-100');

        setTimeout(() => {
            toast.classList.add('opacity-0');
            toast.classList.remove('opacity-100');
        }, 4000);
    }

    function mostrarToastError(mensaje) {
        const toast = document.getElementById('toastError');
        if (!toast) return;

        toast.querySelector('span').textContent = mensaje;
        toast.classList.remove('opacity-0', 'pointer-events-none');
        toast.classList.add('opacity-100');

        setTimeout(() => {
            toast.classList.add('opacity-0');
            toast.classList.remove('opacity-100');
        }, 4000);
    }

    const toastErrorPHP = document.body.dataset.toastError;
    if (toastErrorPHP) {
        mostrarToastError(toastErrorPHP);
    }

    window.abrirModalSolicitud = function (cursoId, nombreCurso) {
        document.getElementById('cursoIdInput').value = cursoId;
        document.getElementById('modalSolicitud').classList.remove('hidden');
    };

    window.cerrarModalSolicitud = function () {
        document.getElementById('modalSolicitud').classList.add('hidden');
    };

    window.abrirModalCurso = function (nombre, flyer, cursoId, tipoCurso, fecha, lugar) {
        document.getElementById('modalFlyer').src = flyer;
        document.getElementById('modalNombre').textContent = nombre;

        const fechaEl = document.getElementById('modalFecha');
        const lugarEl = document.getElementById('modalLugar');

        fechaEl.textContent = fecha || '—';
        lugarEl.textContent = lugar || '—';

        const btnConstancia = document.getElementById('modalBotonConstancia');
        if (tipoCurso === 'finalizados') {
            btnConstancia.classList.remove('hidden');
            btnConstancia.setAttribute('data-curso-id', cursoId);
            btnConstancia.setAttribute('data-nombre', nombre);
        } else {
            btnConstancia.classList.add('hidden');
        }

        document.getElementById('modalCurso').classList.remove('hidden');
    };

    window.cerrarModalCurso = function () {
        document.getElementById('modalCurso').classList.add('hidden');
    };

    window.abrirModalSolicitudDesdeVistaCurso = function () {
        const btn = document.getElementById('modalBotonConstancia');
        const cursoId = btn.getAttribute('data-curso-id');
        const nombre = btn.getAttribute('data-nombre');
        cerrarModalCurso();
        abrirModalSolicitud(cursoId, nombre);
    };

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            cerrarModalCurso();
            cerrarModalSolicitud();
            cerrarModalContacto();
        }
    });

    window.cerrarModalCurso = function () {
        document.getElementById('modalCurso').classList.add('hidden');
        document.getElementById('modalFlyer').src = '';
        document.getElementById('modalNombre').textContent = '';
    };
});