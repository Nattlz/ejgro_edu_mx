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

const modalEditar = document.getElementById('modalEditar');
const modalEliminar = document.getElementById('modalEliminar');
const cerrarEditar = document.getElementById('btnCerrarEditar');
const cerrarEliminar = document.getElementById('btnCerrarEliminar');

document.querySelectorAll('.edit-constancia').forEach(btn => {
    btn.onclick = () => {
        modalEditar.style.display = 'flex';
        document.getElementById('edit-id').value = btn.dataset.id;
        document.getElementById('edit-nombre').value = btn.dataset.nombre;
        document.getElementById('edit-correo').value = btn.dataset.correo;
        document.getElementById('edit-curso').value = btn.dataset.curso;

        document.getElementById('edit-nombre').focus();
    };
});

document.querySelectorAll('.delete-constancia').forEach(btn => {
    btn.onclick = () => {
        modalEliminar.style.display = 'flex';
        document.getElementById('delete-id').value = btn.dataset.id;
        document.getElementById('nombreConstanciaEliminar').textContent = `"${btn.dataset.nombre}"`;
    };
});

cerrarEditar.onclick = () => modalEditar.style.display = 'none';
cerrarEliminar.onclick = () => modalEliminar.style.display = 'none';

window.onclick = e => {
    if (e.target === modalEditar) modalEditar.style.display = 'none';
    if (e.target === modalEliminar) modalEliminar.style.display = 'none';
};

window.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        modalEditar.style.display = 'none';
        modalEliminar.style.display = 'none';
    }
});