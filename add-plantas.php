<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agregar Planta - Indoor Plant</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="add-plantas-styles.css">

    <style>
        .resultado-dinamico {
            margin-top: 30px;
        }

        .card-resultado {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.08);
        }

        .card-resultado img {
            max-width: 220px;
            border-radius: 10px;
            margin-top: 10px;
        }

        .loader {
            margin-top: 15px;
            font-weight: bold;
            color: #2e7d32;
        }

        .success {
            color: green;
            font-weight: bold;
        }

        .error {
            color: red;
            font-weight: bold;
        }
    </style>
</head>
<body>

<header class="encabezado">
    <div class="seccion-logo">
        <a href="index.php" class="boton-volver">
            ←
        </a>
        <div class="icono-planta">🌱</div>
        <h1 class="titulo-app">Indoor Plant</h1>
    </div>
</header>

<main class="contenedor">

    <section class="encabezado-pagina">
        <h2 class="titulo-pagina">Agregar Nueva Planta</h2>
        <p class="subtitulo-pagina">Elige cómo deseas agregar tu planta</p>
    </section>

    <section class="contenedor-opciones">

        <!-- Opción Manual -->
        <div class="tarjeta-opcion opcion-manual">
            <h3 class="titulo-opcion">Agregar Manualmente</h3>
            <p class="descripcion-opcion">
                Ingresa los detalles manualmente.
            </p>

           <button class="boton-opcion" onclick="location.href='add-manual.php'">
                Ingresar Datos
            </button>
        </div>

        <!-- Opción Escáner -->
        <div class="tarjeta-opcion opcion-escaner">
            <h3 class="titulo-opcion">Escanear con Foto</h3>
            <p class="descripcion-opcion">
                Toma una foto y obtén información automática.
            </p>

            <button class="boton-opcion" id="btnEscanear">
                Tomar Foto
            </button>

            <input type="file" id="inputFoto" accept="image/*" capture="environment" style="display:none;">
        </div>

    </section>

    <!-- Resultado -->
    <div id="resultadoEscaneo" class="resultado-dinamico"></div>

</main>

<script>
const btnEscanear = document.getElementById("btnEscanear");
const inputFoto = document.getElementById("inputFoto");
const resultado = document.getElementById("resultadoEscaneo");

btnEscanear.addEventListener("click", () => {
    inputFoto.click();
});

inputFoto.addEventListener("change", async () => {

    const archivo = inputFoto.files[0];
    if (!archivo) return;

    resultado.innerHTML = "<div class='loader'>🔍 Analizando imagen...</div>";

    const formData = new FormData();
    formData.append("imagen", archivo);

    try {

        const respuesta = await fetch("procesar-imagen.php", {
            method: "POST",
            body: formData
        });

        const datos = await respuesta.json();

        if (datos.error) {
            resultado.innerHTML = "<div class='error'>Error: " + datos.error + "</div>";
            return;
        }

        resultado.innerHTML = `
            <div class="card-resultado">
                <h2>${datos.nombreComun}</h2>
                <p><i>${datos.nombreCientifico}</i></p>
                <p><b>Familia:</b> ${datos.familia}</p>
                <p><b>Género:</b> ${datos.genero}</p>
                <p><b>Confianza:</b> ${datos.confianza}%</p>

                <img src="${datos.imagenSubida}">
                ${datos.imagenReferencia ? `<img src="${datos.imagenReferencia}">` : ""}

                <br><br>
                <button type="button" id="guardarBtn" class="boton-opcion">
                    💾 Guardar Planta
                </button>
            </div>
        `;

        document.getElementById("guardarBtn").addEventListener("click", async () => {

            try {

                const guardar = await fetch("guardar-planta.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify(datos)
                });

                const resGuardar = await guardar.json();

                if (resGuardar.success) {

                    window.location.href = "index.php";

                } else {
                    resultado.innerHTML += "<div class='error'>Error: " + (resGuardar.error || "No se pudo guardar") + "</div>";
                }

            } catch (error) {
                resultado.innerHTML += "<div class='error'>Error al conectar con guardar-planta.php</div>";
            }

        });

    } catch (error) {
        resultado.innerHTML = "<div class='error'>Error de conexión</div>";
    }

});
</script>

</body>
</html>