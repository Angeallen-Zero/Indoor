let fechaSeleccionada = null;
let eventosGlobal = [];
const carpetaImagenes = "imagenes";

document.addEventListener("DOMContentLoaded", () => {

    const calendar = new VanillaCalendar('#calendar', {
        settings: {
            selection: { day: 'single' }
        },
        actions: {
            clickDay(event, self) {
                // selectedDates ya viene en YYYY-MM-DD
                fechaSeleccionada = self.selectedDates[0];
                mostrarEventos();
            }
        }
    });

    calendar.init();
    cargarEventos();
});

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
    formData.append("fecha", fechaSeleccionada); // YA está en YYYY-MM-DD
    formData.append("comentario", comentario);
    if (imagen) formData.append("imagen", imagen);

    fetch("guardar_evento.php", { method: "POST", body: formData })
        .then(res => res.text())
        .then(res => {
            if(res === "OK") {
                document.getElementById("comentario").value = "";
                document.getElementById("imagen").value = "";
                cargarEventos();
            } else {
                alert("Error al guardar evento: " + res);
            }
        });
}

function cargarEventos() {
    fetch("obtener_eventos.php")
    .then(res => res.json())
    .then(data => {
        eventosGlobal = data;
        mostrarEventos();
    });
}

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
                <small>${evento.fecha}</small>
            </div>
        `;
        lista.appendChild(div);
    });
}