let fechaSeleccionada = null;
let eventosGlobal = [];
const carpetaImagenes = "imagenes";

document.addEventListener("DOMContentLoaded", () => {

    const calendar = new VanillaCalendar('#calendar', {
        settings: {
            lang: 'es', // 🔥 idioma español
            iso8601: true,
            selection: {
                day: 'single'
            }
        },

        locale: {
            months: [
                'Enero','Febrero','Marzo','Abril','Mayo','Junio',
                'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'
            ],
            weekDays: [
                'Lun','Mar','Mié','Jue','Vie','Sáb','Dom'
            ]
        },

        actions: {
            clickDay(event, self) {
                fechaSeleccionada = self.selectedDates[0];
                console.log("Fecha seleccionada:", fechaSeleccionada);
                mostrarEventos();
            }
        }
    });

    calendar.init();
    cargarEventos();
});


/* ========================= */
/* GUARDAR EVENTO */
/* ========================= */

function guardarEvento() {
    if (!fechaSeleccionada) {
        alert("Selecciona un día");
        return;
    }

    const comentario = document.getElementById("comentario").value.trim();
    const imagen = document.getElementById("imagen").files[0];

    if (!comentario && !imagen) {
        alert("Escribe un comentario o sube una imagen");
        return;
    }

    const formData = new FormData();
    formData.append("fecha", fechaSeleccionada);
    formData.append("comentario", comentario);
    if (imagen) formData.append("imagen", imagen);

    fetch("guardar_evento.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.text())
    .then(res => {
        if (res === "OK") {
            document.getElementById("comentario").value = "";
            document.getElementById("imagen").value = "";
            cargarEventos();
        } else {
            alert("Error al guardar evento: " + res);
        }
    });
}


/* ========================= */
/* CARGAR EVENTOS */
/* ========================= */

function cargarEventos() {
    fetch("obtener_eventos.php")
    .then(res => res.json())
    .then(data => {
        eventosGlobal = data;
        mostrarEventos();
    });
}


/* ========================= */
/* MOSTRAR EVENTOS */
/* ========================= */

function mostrarEventos() {
    const lista = document.getElementById("actividadLista");
    lista.innerHTML = "";

    if (!fechaSeleccionada) {
        lista.innerHTML = "<p>Selecciona un día</p>";
        return;
    }

    const filtrados = eventosGlobal.filter(e => e.fecha === fechaSeleccionada);

    if (filtrados.length === 0) {
        lista.innerHTML = "<p>No hay eventos</p>";
        return;
    }

    filtrados.forEach(evento => {
        const div = document.createElement("div");

        div.innerHTML = `
            ${evento.imagen ? `<img src="${carpetaImagenes}/${evento.imagen}" alt="Evento">` : ""}
            <div>
                <b>${evento.comentario}</b><br>
                <small>${new Date(evento.fecha).toLocaleDateString('es-MX')}</small>
            </div>
        `;

        lista.appendChild(div);
    });
}