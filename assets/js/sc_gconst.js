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

document.getElementById('cursoSelect').addEventListener('change', function () {
    const id = this.value;

    const detalle = document.getElementById('datosCurso');
    const tablaContainer = document.getElementById('tablaCursoContainer');

    if (!id) {
        detalle.innerText = 'Seleccione un curso para ver los detalles.';
        tablaContainer.style.display = 'none';
        document.getElementById('tablaDatos').innerHTML = '';
        document.getElementById('resumen').innerText = '';
        document.getElementById('paginacion').innerHTML = '';
        return;
    }

    tablaContainer.style.display = 'block';

    fetch(`views/process/obtener_curso.php?id=${id}`)
        .then(res => res.json())
        .then(data => {
            if (data && Object.keys(data).length > 0) {
                detalle.innerHTML = `
                    Por su asistencia ${data.asistencia || ''}<br>
                    ${data.nombre || ''}<br>
                    ${data.fecha_imparticion || ''}<br>
                    ${data.horas || ''}<br>
                    ${data.lugar || ''}
                `;
            } else {
                detalle.innerHTML = 'No se encontraron datos del curso.';
            }
        })
        .catch(err => {
            console.error(err);
            detalle.innerText = 'Error al cargar datos del curso.';
        });

    cargarConstancias(id);
});

function cargarConstancias(cursoId, pagina = 1) {
    fetch(`views/process/constancias_por_curso.php?curso_id=${cursoId}&page=${pagina}`)
        .then(res => res.json())
        .then(data => {
            const tabla = document.getElementById('tablaDatos');
            const resumen = document.getElementById('resumen');
            const tablaContainer = document.getElementById('tablaCursoContainer');

            tabla.innerHTML = '';
            const total = data.total;
            const inicio = total === 0 ? 0 : (data.pagina - 1) * data.por_pagina + 1;
            const fin = Math.min(inicio + data.datos.length - 1, total);

            resumen.innerText = `Mostrando ${inicio} a ${fin} de ${total} constancias`;

            data.datos.forEach((c, i) => {
                tabla.innerHTML += `
                    <tr>
                        <td>${inicio + i}</td>
                        <td>${c.nombre_completo}</td>
                        <td>${c.correo}</td>
                        <td>${c.fecha}</td>
                    </tr>`;
            });

            generarPaginacion(cursoId, total, data.por_pagina, data.pagina);
            tablaContainer.style.display = 'block';
        })
        .catch(err => {
            console.error(err);
        });
}