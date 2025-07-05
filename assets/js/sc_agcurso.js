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

const modalAgregar = document.getElementById('modalCurso');
const abrirAgregar = document.getElementById('btnAbrirModal');
const cerrarAgregar = document.getElementById('btnCerrarModal');

abrirAgregar.onclick = () => modalAgregar.style.display = 'flex';
cerrarAgregar.onclick = () => modalAgregar.style.display = 'none';

const modalEditar = document.getElementById('modalEditar');
const cerrarEditar = document.getElementById('btnCerrarEditar');

document.querySelectorAll('.edit-curso').forEach(btn => {
    btn.onclick = () => {
        modalEditar.style.display = 'flex';
        document.getElementById('editarId').value = btn.dataset.id;
        document.getElementById('editarAsistencia').value = btn.dataset.asistencia;
        document.getElementById('editarNombre').value = btn.dataset.nombre;
        document.getElementById('editarFecha').value = btn.dataset.fecha;
        document.getElementById('editarHoras').value = btn.dataset.horas;
        document.getElementById('editarLugar').value = btn.dataset.lugar;
    };
});
cerrarEditar.onclick = () => modalEditar.style.display = 'none';

const modalEliminar = document.getElementById('modalEliminar');
const cerrarEliminar = document.getElementById('btnCerrarEliminar');
const nombreCursoEliminar = document.getElementById('nombreCursoEliminar');

document.querySelectorAll('.delete-curso').forEach(btn => {
    btn.onclick = () => {
        modalEliminar.style.display = 'flex';
        document.getElementById('eliminarId').value = btn.dataset.id;
        nombreCursoEliminar.textContent = `"${btn.dataset.nombre}"`;
    };
});
cerrarEliminar.onclick = () => modalEliminar.style.display = 'none';

window.onclick = e => {
    if (e.target === modalAgregar) modalAgregar.style.display = 'none';
    if (e.target === modalEditar) modalEditar.style.display = 'none';
    if (e.target === modalEliminar) modalEliminar.style.display = 'none';
};