document.addEventListener('DOMContentLoaded', () => {
    const menuButton = document.getElementById('menuButton');
    const menuDropdown = document.getElementById('menuDropdown');

    function toggleMenu() {
        if (menuDropdown) {
            menuDropdown.classList.toggle('hidden');
        }
    }

    window.toggleMenu = toggleMenu;

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

        limpiarErroresFormulario();
    };

    window.cerrarModalContacto = function () {
        const modal = document.getElementById('modalContacto');
        const seccionCursos = document.getElementById('seccionCursos');
        if (modal) modal.classList.add('hidden');
        if (seccionCursos) seccionCursos.classList.remove('hidden');

        resetearFormularioContacto();
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
    let isSubmitting = false;

    if (formContacto) {
        const submitBtn = formContacto.querySelector('button[type="submit"]');
        const btnText = submitBtn.querySelector('.btn-text');
        const originalBtnText = btnText ? btnText.textContent : 'Enviar Mensaje';

        const inputs = formContacto.querySelectorAll('input[required], textarea[required]');
        inputs.forEach(input => {
            input.addEventListener('blur', function () {
                validarCampo(this);
            });

            input.addEventListener('input', function () {
                if (this.classList.contains('field-error') && this.value.trim()) {
                    this.classList.remove('field-error');
                }
            });
        });

        formContacto.addEventListener('submit', function (e) {
            if (isSubmitting) {
                e.preventDefault();
                return;
            }

            let formularioValido = true;
            inputs.forEach(input => {
                if (!validarCampo(input)) {
                    formularioValido = false;
                }
            });

            if (!formularioValido) {
                e.preventDefault();
                mostrarToastError("Por favor corrige los errores en el formulario.");
                return;
            }

            isSubmitting = true;
            mostrarEstadoCarga(submitBtn, btnText, true);
            formContacto.classList.add('form-loading');
        });
    }

    function validarCampo(campo) {
        const valor = campo.value.trim();
        let esValido = true;

        campo.classList.remove('field-error');

        if (campo.hasAttribute('required') && !valor) {
            campo.classList.add('field-error');
            esValido = false;
        }

        if (campo.type === 'email' && valor) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(valor)) {
                campo.classList.add('field-error');
                esValido = false;
            }
        }

        return esValido;
    }

    function mostrarEstadoCarga(btn, btnText, mostrar) {
        if (mostrar) {
            if (btnText) {
                btnText.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Enviando...';
            }
            btn.disabled = true;
            btn.classList.add('opacity-50', 'cursor-not-allowed');
        } else {
            if (btnText) {
                btnText.innerHTML = '<i class="fas fa-paper-plane mr-2"></i>Enviar Mensaje';
            }
            btn.disabled = false;
            btn.classList.remove('opacity-50', 'cursor-not-allowed');
            isSubmitting = false;
        }
    }

    function limpiarErroresFormulario() {
        if (formContacto) {
            const campos = formContacto.querySelectorAll('.field-error');
            campos.forEach(campo => {
                campo.classList.remove('field-error');
            });
        }
    }

    function resetearFormularioContacto() {
        if (formContacto) {
            formContacto.reset();
            limpiarErroresFormulario();
            formContacto.classList.remove('form-loading');

            const submitBtn = formContacto.querySelector('button[type="submit"]');
            const btnText = submitBtn.querySelector('.btn-text');

            if (submitBtn && btnText) {
                mostrarEstadoCarga(submitBtn, btnText, false);
            }
        }
    }

    function mostrarToast(mensaje) {
        const toast = document.getElementById('toastExito');
        if (!toast) {
            console.warn('Toast de éxito no encontrado');
            return;
        }

        const span = toast.querySelector('span');
        if (span) span.textContent = mensaje;

        toast.classList.remove('opacity-0', 'pointer-events-none');
        toast.style.opacity = '1';
        toast.style.pointerEvents = 'all';

        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.pointerEvents = 'none';
            setTimeout(() => {
                toast.classList.add('opacity-0', 'pointer-events-none');
            }, 300);
        }, 5000);
    }

    function mostrarToastError(mensaje) {
        const toast = document.getElementById('toastError');
        if (!toast) {
            console.warn('Toast de error no encontrado');
            return;
        }

        const span = toast.querySelector('span');
        if (span) span.textContent = mensaje;

        toast.classList.remove('opacity-0', 'pointer-events-none');
        toast.style.opacity = '1';
        toast.style.pointerEvents = 'all';

        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.pointerEvents = 'none';
            setTimeout(() => {
                toast.classList.add('opacity-0', 'pointer-events-none');
            }, 300);
        }, 5000);
    }

    window.mostrarToast = mostrarToast;
    window.mostrarToastError = mostrarToastError;

    const toastErrorPHP = document.body.dataset.toastError;
    if (toastErrorPHP && toastErrorPHP.trim()) {
        setTimeout(() => {
            mostrarToastError(toastErrorPHP);
        }, 100);
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
        const modal = document.getElementById('modalCurso');
        if (modal) {
            modal.classList.add('hidden');
            document.getElementById('modalFlyer').src = '';
            document.getElementById('modalNombre').textContent = '';
        }
    };

    window.abrirModalSolicitudDesdeVistaCurso = function () {
        const btn = document.getElementById('modalBotonConstancia');
        if (btn) {
            const cursoId = btn.getAttribute('data-curso-id');
            const nombre = btn.getAttribute('data-nombre');
            cerrarModalCurso();
            abrirModalSolicitud(cursoId, nombre);
        }
    };

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            cerrarModalCurso();
            cerrarModalSolicitud();
            cerrarModalContacto();
        }
    });

    document.addEventListener('click', (e) => {
        const modalContacto = document.getElementById('modalContacto');
        if (modalContacto && e.target === modalContacto) {
            cerrarModalContacto();
        }

        const modalCurso = document.getElementById('modalCurso');
        if (modalCurso && e.target === modalCurso) {
            cerrarModalCurso();
        }

        const modalSolicitud = document.getElementById('modalSolicitud');
        if (modalSolicitud && e.target === modalSolicitud) {
            cerrarModalSolicitud();
        }
    });

    const modoGuardado = localStorage.getItem('modoOscuro');
    if (modoGuardado === 'true') {
        document.documentElement.classList.add('dark');
        if (switchTema) switchTema.checked = true;
    }

    console.log('🎯 IMJ - Sistema cargado correctamente');
});