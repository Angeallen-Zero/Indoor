<div class="calendario-wrapper">

    <div class="calendario-container">

        <h2 class="titulo-calendario">Seguimiento del vivero</h2>

        <div class="calendario-panel">

            <!-- IZQUIERDA -->
            <div class="calendario-left">
                <div id="calendar" class="vanilla-calendar"></div>
            </div>

            <!-- DERECHA -->
            <div class="calendario-right">

                <div class="actividad-box">
                    <h3>Actividad del día</h3>
                    <div id="actividadLista"></div>
                </div>

                <div class="form-box">
                    <h3>Agregar evento</h3>

                    <input type="text" id="comentario" placeholder="Escribe algo...">
                    <input type="file" id="imagen">

                    <button onclick="guardarEvento()">Guardar</button>
                </div>

            </div>

        </div>

    </div>

</div>

<script src="calendario.js"></script>