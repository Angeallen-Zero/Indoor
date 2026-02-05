import { initializeApp } from 'https://www.gstatic.com/firebasejs/10.7.1/firebase-app.js';
import { getAuth, signInWithEmailAndPassword, onAuthStateChanged, signOut } from 'https://www.gstatic.com/firebasejs/10.7.1/firebase-auth.js';
import { getDatabase, ref, set, onValue, update } from 'https://www.gstatic.com/firebasejs/10.7.1/firebase-database.js';

const firebaseConfig = {
    apiKey: "AIzaSyCdZGbZJOJj9gDM3HpaJDz2yldutLfgJPs",
    authDomain: "indoor-web-18468.firebaseapp.com",
    databaseURL: "https://indoor-web-18468-default-rtdb.firebaseio.com",
    projectId: "indoor-web-18468",
    storageBucket: "indoor-web-18468.firebasestorage.app",
    messagingSenderId: "850530062301",
    appId: "1:850530062301:web:1c41cd32c67802d6e26d91"
};

const app = initializeApp(firebaseConfig);
const auth = getAuth(app);
const database = getDatabase(app);

const loginSection = document.getElementById('loginSection');
const dashboardSection = document.getElementById('dashboardSection');
const loginForm = document.getElementById('loginForm');
const loginError = document.getElementById('loginError');
const logoutBtn = document.getElementById('logoutBtn');
const userEmailSpan = document.getElementById('userEmail');

const soilMoisture = document.getElementById('soilMoisture');
const pumpStatus = document.getElementById('pumpStatus');
const fanStatus = document.getElementById('fanStatus');
const lightStatus = document.getElementById('lightStatus');

const humiditySlider = document.getElementById('humiditySlider');
const humidityValue = document.getElementById('humidityValue');
const ventilationSwitch = document.getElementById('ventilationSwitch');
const lightOnTime = document.getElementById('lightOnTime');
const lightOffTime = document.getElementById('lightOffTime');
const saveLightingBtn = document.getElementById('saveLightingBtn');

onAuthStateChanged(auth, (user) => {
    if (user) {

        userEmailSpan.textContent = user.email;
        loginSection.style.display = 'none';
        dashboardSection.style.display = 'block';
        initializeDashboard();
    } else {

        loginSection.style.display = 'block';
        dashboardSection.style.display = 'none';
    }
});

loginForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    
    try {
        loginError.style.display = 'none';
        await signInWithEmailAndPassword(auth, email, password);
    } catch (error) {
        loginError.textContent = 'Error de inicio de sesión: ' + error.message;
        loginError.style.display = 'block';
    }
});

logoutBtn.addEventListener('click', () => {
    signOut(auth);
});

function initializeDashboard() {

    const sensorRef = ref(database, 'sensores/humedadSuelo');
    onValue(sensorRef, (snapshot) => {
        const value = snapshot.val();
        if (value !== null) {
            soilMoisture.textContent = value.toFixed(1) + ' %';
        }
    });

    const statusRef = ref(database, 'estado');
    onValue(statusRef, (snapshot) => {
        const data = snapshot.val();
        if (data) {
            updateStatusBadge(pumpStatus, data.bomba);
            updateStatusBadge(fanStatus, data.ventilador);
            updateStatusBadge(lightStatus, data.luces);
        }
    });

    const controlRef = ref(database, 'control');
    onValue(controlRef, (snapshot) => {
        const data = snapshot.val();
        if (data) {

            if (data.umbralHumedad !== undefined) {
                humiditySlider.value = data.umbralHumedad;
                humidityValue.textContent = data.umbralHumedad;
            }


            if (data.ventilacion !== undefined) {
                ventilationSwitch.checked = data.ventilacion;
            }


            if (data.iluminacion) {
                if (data.iluminacion.horaEncendido) {
                    lightOnTime.value = data.iluminacion.horaEncendido;
                }
                if (data.iluminacion.horaApagado) {
                    lightOffTime.value = data.iluminacion.horaApagado;
                }
            }
        }
    });

    humiditySlider.addEventListener('input', (e) => {
        humidityValue.textContent = e.target.value;
    });

    humiditySlider.addEventListener('change', (e) => {
        const value = parseInt(e.target.value);
        update(ref(database, 'control'), {
            umbralHumedad: value
        });
    });


    ventilationSwitch.addEventListener('change', (e) => {
        update(ref(database, 'control'), {
            ventilacion: e.target.checked
        });
    });

    saveLightingBtn.addEventListener('click', () => {
        const onTime = lightOnTime.value;
        const offTime = lightOffTime.value;
        
        update(ref(database, 'control/iluminacion'), {
            horaEncendido: onTime,
            horaApagado: offTime
        }).then(() => {
            alert('Horario guardado exitosamente');
        }).catch((error) => {
            alert('Error al guardar: ' + error.message);
        });
    });
}

function updateStatusBadge(element, status) {
    if (status === 'ON' || status === true) {
        element.textContent = 'ON';
        element.classList.remove('off');
        element.classList.add('on');
    } else {
        element.textContent = 'OFF';
        element.classList.remove('on');
        element.classList.add('off');
    }
}
