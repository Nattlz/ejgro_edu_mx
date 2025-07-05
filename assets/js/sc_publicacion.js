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

function mostrarModalFlyer(src) {
    document.getElementById('flyerModalImg').src = src;
    document.getElementById('flyerModal').style.display = 'flex';
}

function cerrarModalFlyer() {
    document.getElementById('flyerModal').style.display = 'none';
}

function abrirModalEditar(pub) {
    document.getElementById('edit_id').value = pub.id;
    document.getElementById('edit_estado').value = pub.estado;
    document.getElementById('modalEditar').style.display = 'flex';
}

function cerrarModalEditar() {
    document.getElementById('modalEditar').style.display = 'none';
}

function abrirModalEliminar(id) {
    document.getElementById('delete_id').value = id;
    document.getElementById('modalEliminar').style.display = 'flex';
}

function cerrarModalEliminar() {
    document.getElementById('modalEliminar').style.display = 'none';
}

function vistaPreviaFlyer(event) {
    const input = event.target;
    const previewContainer = document.getElementById('previewFlyer');
    const previewImg = document.getElementById('previewImg');

    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function (e) {
            previewImg.src = e.target.result;
            previewContainer.style.display = 'flex';
        }
        reader.readAsDataURL(input.files[0]);
    } else {
        previewImg.src = "#";
        previewContainer.style.display = 'none';
    }
}