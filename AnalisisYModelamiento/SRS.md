# Especificación de Requisitos de Software (SRS) — GrowSystem

**Organización emisora:** Universidad Autónoma de la Ciudad de México (UACM).
**Estado del documento:** Aprobado para la versión de código descrita abajo.
**Versión del documento:** 2.0
**Fecha de emisión:** 31 de agosto de 2026.

### Historial de cambios

| Versión | Fecha | Cambio | Autor |
|---|---|---|---|
| 1.0 | (anterior) | SRS inicial, describía el prototipo standalone (Wemos R1 D1, sin web ni base de datos). | Equipo GrowSystem |
| 2.0 | 2026-08-31 | Reescritura completa. Describe la versión real del sistema: plataforma web (PHP + MySQL) con autenticación, activación de dispositivo, gestión de plantas, monitoreo/control en tiempo real y panel de administración, más el firmware ESP32 + FreeRTOS que se comunica por REST/JSON. Se elimina el alcance de tienda en línea, calendario y notificaciones por correo, que no forman parte de esta versión. | Equipo GrowSystem |

---

## 1. Introducción

### 1.1 Propósito
Este documento especifica los requisitos de software de **GrowSystem**, una plataforma para el monitoreo y control remoto de un cultivo indoor mediante un dispositivo **ESP32**. El propósito de esta especificación es servir de acuerdo entre el equipo de desarrollo (Miguel Pantoja, Ángel Ramos, Jonathan Alonso) y quien evalúe o dé mantenimiento al proyecto, sobre qué debe hacer el software en su versión actual. Sigue la plantilla recomendada por **IEEE Std 830-1998** e incorpora las adiciones que exige el **Anexo B de dicho estándar** para ser compatible con IEEE/EIA 12207.1.

### 1.2 Alcance
El software identificado como **GrowSystem** permite a un usuario:
- Crear una cuenta y autenticarse.
- Activar un dispositivo ESP32 propio mediante su número de serie.
- Identificar la planta que cultivará, ya sea por foto (usando la API externa PlantNet) o de forma manual.
- Monitorear en tiempo real la humedad, los nutrientes y el estado de los actuadores (bomba de riego, ventilador, luces) de su instancia.
- Configurar remotamente los umbrales de riego/nutrientes, la ventilación y el horario de iluminación.

Adicionalmente, un usuario con rol de **administrador** puede:
- Ver estadísticas generales del parque de dispositivos.
- Dar de alta, liberar o dar de baja dispositivos, y generar sus tokens de acceso.
- Cambiar el rol de cualquier usuario.

**Fuera de alcance de esta versión** (ver también §2.6): tienda en línea, calendario de eventos, generación de tickets en PDF y envío de notificaciones por correo. Estas funciones existieron en una rama anterior del proyecto pero no forman parte del sistema aquí especificado.

### 1.3 Definiciones, acrónimos y abreviaturas
| Término | Significado |
|---|---|
| **Dispositivo** | Unidad física ESP32 identificada por un `numero_serie` único (p. ej. `IND-0004`). |
| **Instancia** | Vínculo entre un usuario, un dispositivo activado y una planta. Es la unidad que se monitorea y controla. |
| **Token API** | Cadena secreta asociada a un dispositivo, usada en el header `X-API-Token` para que el ESP32 se autentique ante el servidor. |
| **PlantNet** | Servicio externo (`my-api.plantnet.org`) que identifica la especie de una planta a partir de una fotografía. |
| **FreeRTOS** | Sistema operativo de tiempo real usado en el firmware del ESP32 para ejecutar tareas concurrentes. |
| **RF / RNF** | Requisito Funcional / Requisito No Funcional. |
| **Rol** | Nivel de acceso de un usuario: `cliente` o `admin`. |

### 1.4 Referencias
- IEEE Std 830-1998, *IEEE Recommended Practice for Software Requirements Specifications*.
- IEEE/EIA 12207.1-1997, *Guide for Information Technology — Software life cycle processes — Life cycle data* (Anexo B de la referencia anterior).
- Documentación de la API de identificación de plantas: `https://my.plantnet.org/`.
- Esquema de base de datos del proyecto: `growsystem.sql`.
- Firmware del dispositivo: `growsystem_esp32.ino`.

### 1.5 Visión general
La sección 2 describe el sistema de forma general (perspectiva, funciones, usuarios, restricciones). La sección 3 detalla los requisitos específicos, organizados por módulo funcional. La sección 4 reúne información de soporte (glosario ya cubierto en 1.3, y limitaciones conocidas).

---

## 2. Descripción general

### 2.1 Perspectiva del producto
GrowSystem es un sistema **cliente-servidor con un dispositivo embebido como tercer participante**:

1. **Navegador del usuario** ↔ **Backend PHP** (páginas `.php` y endpoints en `api/`), sobre HTTP, con sesión de PHP para mantener el inicio de sesión.
2. **Backend PHP** ↔ **Base de datos MySQL** (`growsystem`), mediante `mysqli` con sentencias preparadas.
3. **Dispositivo ESP32** ↔ **Backend PHP**, sobre HTTP/JSON, autenticado por token (no por sesión), en tres endpoints dedicados (ver §3.1).
4. **Backend PHP** ↔ **PlantNet** (servicio externo), únicamente durante la identificación de una planta por foto.

No existe un servidor intermedio tipo Firebase ni comunicación directa navegador↔ESP32: toda la información pasa por el backend PHP, que es la única fuente de verdad.

### 2.2 Funciones del producto
| ID | Módulo | Descripción |
|---|---|---|
| MOD-01 | Autenticación y roles | Registro, inicio/cierre de sesión, control de acceso por rol (`cliente`/`admin`). |
| MOD-02 | Activación de dispositivo | Vincular un dispositivo por número de serie a la cuenta del usuario, creando una instancia. |
| MOD-03 | Gestión de plantas | Identificar una planta por foto (PlantNet) o registrarla manualmente; editar sus datos. |
| MOD-04 | Monitoreo y control en tiempo real | Dashboard con estado actual, histórico gráfico, y edición de parámetros de control de una instancia. |
| MOD-05 | Panel de administración | Estadísticas, alta/baja/liberación de dispositivos, generación de tokens, cambio de rol de usuarios. |
| MOD-06 | Comunicación con el dispositivo ESP32 | Endpoints REST que el firmware consume para sincronizar control y reportar estado/lecturas. |

### 2.3 Características del usuario
| Rol | Perfil | Responsabilidades |
|---|---|---|
| **Cliente** | Persona sin conocimientos técnicos de electrónica; usa un navegador de escritorio o móvil. | Registrarse, activar su dispositivo, identificar su planta, revisar el dashboard y ajustar los parámetros de riego/luz de su propia instancia. |
| **Administrador** | Encargado técnico del parque de dispositivos (soporte). | Dar de alta dispositivos nuevos, generar sus tokens, liberar o dar de baja dispositivos, y gestionar los roles de los usuarios. |

### 2.4 Restricciones
- El backend requiere **PHP 8.x** con extensión `mysqli` y **MySQL/MariaDB** (probado sobre XAMPP).
- La identificación automática de plantas depende de una **cuenta y API key de PlantNet** (servicio de terceros, con límites de uso propios de su plan gratuito).
- El firmware requiere un **ESP32** con las librerías `WiFiManager`, `HTTPClient`, `NTPClient` y FreeRTOS (nativo del SDK de ESP32).
- La comunicación del ESP32 con el servidor asume que ambos están alcanzables por red (LAN o el servidor expuesto a Internet); la IP del servidor está fija en el firmware (`SERVER_IP`), por lo que un cambio de IP del servidor requiere reflashear el dispositivo.

### 2.5 Supuestos y dependencias
- Se asume que cada dispositivo físico tiene un `numero_serie` único, dado de alta previamente en la tabla `dispositivos` por un administrador, antes de que un cliente pueda activarlo.
- Se asume que solo un dispositivo puede estar asignado a un usuario a la vez por instancia, y que un dispositivo dado de baja no puede reactivarse.
- El sistema depende de la disponibilidad del servicio PlantNet para la identificación automática; si no está disponible, el usuario puede registrar la planta manualmente (ver RF-PLA-002).
- El reloj del ESP32 depende de la disponibilidad de un servidor NTP para el control de horarios de iluminación.

### 2.6 Reparto de requisitos (Apportioning)
Los siguientes requisitos, presentes en versiones anteriores del proyecto o en el esquema de base de datos, **quedan explícitamente fuera de esta versión** y se difieren para una futura iteración:
- Tienda en línea (catálogo, carrito, checkout, ticket en PDF, notificación por correo).
- Calendario de eventos de cultivo.
- Funcionalidad de **notas** por instancia: la tabla `notas` ya existe en el esquema de base de datos (con borrado en cascada al eliminar una instancia), pero **no hay todavía ninguna pantalla que permita crearlas o consultarlas**. Se deja pendiente para una futura versión.
- Notificaciones push o por correo ante condiciones críticas (p. ej. humedad fuera de rango).

---

## 3. Requisitos específicos

### 3.1 Requisitos de interfaces externas

#### 3.1.1 Interfaces de usuario (web)
| Página | Rol requerido | Función |
|---|---|---|
| `registro.php` | Público | Alta de cuenta nueva (rol `cliente` por defecto). |
| `login.php` | Público | Inicio de sesión; redirige a `admin.php` o `dashboard.php` según el rol. |
| `logout.php` | Sesión activa | Cierre de sesión. |
| `activar.php` | Cliente | Flujo de 2 pasos: verificar número de serie del dispositivo → identificar planta → crear instancia. |
| `dashboard.php` | Cliente/Admin | Estado en tiempo real, gráfica histórica y edición de parámetros de control de una instancia. |
| `index.php` | Cliente | Listado de las plantas/instancias activas del usuario. |
| `planta.php`, `add-plantas.php`, `add-manual.php`, `editar-planta.php` | Cliente | Alta (por foto o manual) y edición de fichas de planta. |
| `admin.php` | Admin | Panel de administración (estadísticas, dispositivos, usuarios). |

> **Nota de trazabilidad de defectos:** al momento de escribir este documento, `activar.php`, `admin.php`, `planta.php` e `index(0).php` redirigen a un archivo `acceso.php` que **no existe** en el proyecto cuando no hay sesión iniciada. Debe corregirse ese redirect hacia `login.php` antes de considerar este flujo verificado (ver §RNF-SEG-03).

#### 3.1.2 Interfaz de hardware
- **Dispositivo ESP32**: sensores de humedad y nutrientes (entrada analógica), relés de bomba de agua, ventilador y luces (salida digital), botón físico único para reinicio de la configuración Wi-Fi.

#### 3.1.3 Interfaces de software (externas al sistema)
| Servicio | Uso | Endpoint |
|---|---|---|
| PlantNet | Identificación de especie por fotografía | `POST https://my-api.plantnet.org/v2/identify/all` |

#### 3.1.4 Interfaces de comunicación (API REST interna)
| Endpoint | Método | Consumido por | Autenticación | Descripción |
|---|---|---|---|---|
| `api/control.php` | GET | ESP32 | Header `X-API-Token` | Devuelve umbrales y horarios vigentes para la instancia del dispositivo. |
| `api/estado.php` | POST | ESP32 | Header `X-API-Token` | Reporta el estado actual de sensores y relés (upsert sobre `estado_actual`). |
| `api/lectura.php` | POST | ESP32 | Header `X-API-Token` | Inserta una lectura histórica de humedad/nutrientes (`lecturas`). |
| `api/datos.php` | GET/POST | Frontend web (AJAX) | Sesión PHP | `accion=estado` (estado + control actuales), `accion=grafica` (últimas N lecturas), `accion=control` (POST: guardar nuevos parámetros). |
| `api/admin-accion.php` | POST | Panel admin | Sesión PHP, rol `admin` | `accion=liberar\|baja\|crear\|cambiar_rol\|generar_token`. |
| `api/eliminar-instancia.php` | POST | Frontend web | Sesión PHP | Elimina una instancia y libera el dispositivo asociado. |

El token del ESP32 se valida contra la tabla `api_tokens` y, adicionalmente, se verifica que el número de serie enviado en el cuerpo/parámetros coincida con el dueño real del token (ver `api/auth.php`), de modo que un token filtrado de un dispositivo no pueda usarse para falsear datos de otro.

### 3.2 Requisitos funcionales

#### MOD-01 · Autenticación y roles
| ID | Requisito | Prioridad |
|---|---|---|
| RF-AUT-001 | El sistema permitirá registrar una cuenta con nombre, correo y contraseña, validando que el correo tenga formato válido, la contraseña tenga al menos 6 caracteres, ambas contraseñas coincidan, y el correo no esté ya registrado. | Alta |
| RF-AUT-002 | Las contraseñas se almacenarán únicamente como hash `bcrypt` (`password_hash`), nunca en texto plano. | Alta |
| RF-AUT-003 | El sistema permitirá iniciar sesión con correo y contraseña, y redirigirá a `admin.php` si el rol es `admin`, o a `dashboard.php` si es `cliente`. | Alta |
| RF-AUT-004 | El sistema permitirá cerrar la sesión activa desde cualquier página. | Media |
| RF-AUT-005 | Toda página que requiera sesión iniciada deberá redirigir a la pantalla de inicio de sesión si no la hay. | Alta |

#### MOD-02 · Activación de dispositivo
| ID | Requisito | Prioridad |
|---|---|---|
| RF-DIS-001 | El sistema permitirá a un usuario ingresar el número de serie de un dispositivo para verificarlo. | Alta |
| RF-DIS-002 | El sistema rechazará la activación si el número de serie no existe, si ya está asignado a otra cuenta, o si está dado de baja, mostrando un mensaje específico para cada caso. | Alta |
| RF-DIS-003 | Tras verificar el dispositivo, el sistema exigirá identificar una planta (ver MOD-03) antes de completar la activación. | Alta |
| RF-DIS-004 | Al completar la activación, el sistema creará una instancia que vincula usuario, dispositivo y planta, usando el nombre común de la planta como alias por defecto. | Alta |

#### MOD-03 · Gestión de plantas
| ID | Requisito | Prioridad |
|---|---|---|
| RF-PLA-001 | El sistema permitirá subir una fotografía de la planta y enviarla a la API de PlantNet para obtener nombre común, nombre científico, familia, género y porcentaje de confianza de la identificación. | Alta |
| RF-PLA-002 | Si la identificación automática falla o el usuario lo prefiere, el sistema permitirá registrar la planta manualmente con sus datos básicos. | Media |
| RF-PLA-003 | El sistema permitirá editar los datos de una planta ya registrada. | Media |

#### MOD-04 · Monitoreo y control en tiempo real
| ID | Requisito | Prioridad |
|---|---|---|
| RF-MON-001 | El dashboard mostrará, para la instancia seleccionada, el estado actual de bomba, ventilador, luces, humedad y nutrientes. | Alta |
| RF-MON-002 | El dashboard actualizará el estado actual mediante sondeo periódico al endpoint `api/datos.php?accion=estado`, sin recargar la página. | Alta |
| RF-MON-003 | El dashboard mostrará una gráfica con al menos las últimas 60 lecturas históricas de humedad y nutrientes. | Media |
| RF-MON-004 | El sistema permitirá modificar, desde el dashboard, el umbral de humedad, el umbral de nutrientes, el estado de ventilación y el horario de encendido/apagado de luces de una instancia. | Alta |
| RF-MON-005 | Un usuario solo podrá ver y modificar instancias de su propiedad, salvo que su rol sea `admin`. | Alta |

#### MOD-05 · Panel de administración
| ID | Requisito | Prioridad |
|---|---|---|
| RF-ADM-001 | El panel de administración mostrará estadísticas generales del total de dispositivos y usuarios registrados. | Media |
| RF-ADM-002 | El administrador podrá dar de alta un nuevo dispositivo (número de serie) en estado "disponible". | Alta |
| RF-ADM-003 | El administrador podrá generar un token de acceso para un dispositivo disponible. | Alta |
| RF-ADM-004 | El administrador podrá liberar un dispositivo asignado, dejándolo disponible para otro usuario. | Media |
| RF-ADM-005 | El administrador podrá dar de baja definitivamente un dispositivo. | Media |
| RF-ADM-006 | El administrador podrá cambiar el rol de cualquier usuario entre `cliente` y `admin`. | Media |
| RF-ADM-007 | Todas las rutas y acciones del panel de administración deberán validar que la sesión activa tenga rol `admin`, devolviendo error 403 en caso contrario. | Alta |

#### MOD-06 · Comunicación con el dispositivo ESP32
| ID | Requisito | Prioridad |
|---|---|---|
| RF-ESP-001 | El firmware consultará `api/control.php` cada 30 segundos para sincronizar los parámetros de control vigentes. | Alta |
| RF-ESP-002 | El firmware reportará el estado de sensores y actuadores a `api/estado.php` cada 5 segundos. | Alta |
| RF-ESP-003 | El firmware enviará una lectura histórica a `api/lectura.php` cada 10 segundos. | Media |
| RF-ESP-004 | Toda petición del ESP32 deberá incluir el header `X-API-Token`; el servidor rechazará con HTTP 401 las peticiones sin token o con token inactivo, y con HTTP 403 si el número de serie enviado no corresponde al dueño del token. | Alta |
| RF-ESP-005 | El firmware obtendrá la hora vigente por NTP para el control del horario de iluminación. | Media |
| RF-ESP-006 | El firmware expondrá un botón físico único para reiniciar la configuración de red Wi-Fi (portal cautivo vía `WiFiManager`). | Baja |

### 3.3 Requisitos de rendimiento
| ID | Requisito |
|---|---|
| RNF-PER-001 | El reporte de estado del ESP32 (`api/estado.php`) no deberá tardar más de 5 s en completarse bajo condiciones normales de red, para no acumular retraso respecto al siguiente ciclo de la tarea. |
| RNF-PER-002 | El sondeo del dashboard al endpoint de estado no deberá superar 1 petición por segundo por usuario conectado, para limitar la carga sobre la base de datos. |
| RNF-PER-003 | La consulta de la gráfica histórica limitará el resultado a un máximo de 200 lecturas por petición. |

### 3.4 Requisitos de base de datos lógica
| Tabla | Descripción |
|---|---|
| `usuarios` | Cuentas del sistema, con contraseña en hash y rol (`cliente`/`admin`). |
| `dispositivos` | Inventario físico de ESP32, con número de serie y estado (`disponible`/`asignado`/`baja`). |
| `api_tokens` | Tokens de autenticación de dispositivo, con estado activo/inactivo y último uso. |
| `instancias` | Vínculo usuario–dispositivo–planta; unidad de monitoreo y control. |
| `control` | Parámetros de control vigentes por instancia (umbrales, horario de luces). |
| `estado_actual` | Última lectura de estado reportada por cada instancia (una fila por instancia). |
| `lecturas` | Histórico de lecturas de humedad/nutrientes por instancia. |
| `plantas` | Ficha de la planta (identificada por foto o manual). |
| `notas` | Reservada para observaciones por instancia; **sin interfaz de usuario en esta versión** (ver §2.6). |
| `v_dashboard`, `v_historial`, `v_panel_admin` | Vistas de conveniencia para las consultas del dashboard, el histórico y el panel admin. |

### 3.5 Restricciones de diseño
- Acceso a datos mediante `mysqli` con sentencias preparadas (mitiga inyección SQL) en todos los endpoints revisados.
- El backend es PHP procedural (sin framework ni capa ORM), organizado por página/endpoint.
- La autenticación web usa sesiones nativas de PHP (`session_start()`); la autenticación de dispositivo usa un token por header, sin sesión.

### 3.6 Atributos del sistema de software
| Atributo | Requisito |
|---|---|
| **RNF-SEG-001 (Seguridad)** | Las contraseñas de usuario se almacenan con `bcrypt`; el sistema además reconoce hashes SHA-256 heredados por compatibilidad, pero todo alta nueva debe usar `bcrypt`. |
| **RNF-SEG-002 (Seguridad)** | Cada endpoint de administración debe verificar el rol `admin` en servidor, no solo ocultar el enlace en la interfaz. |
| **RNF-SEG-003 (Seguridad, pendiente)** | La clave de la API de PlantNet está actualmente escrita en texto plano dentro de `procesar-imagen.php`, subido a un repositorio público. Debe moverse a una variable de entorno o archivo de configuración excluido del control de versiones antes de considerar este requisito cumplido. |
| **RNF-FIA-001 (Fiabilidad)** | Si el ESP32 pierde conectividad Wi-Fi, las tareas de reporte deben omitir el envío en vez de bloquear el sistema (comportamiento ya implementado en el firmware). |
| **RNF-MAN-001 (Mantenibilidad)** | Cada endpoint de `api/` debe documentar en cabecera su método HTTP, quién lo consume y su propósito (ya es la convención seguida en el código actual; debe mantenerse). |
| **RNF-POR-001 (Portabilidad)** | El backend debe ejecutarse sobre cualquier stack compatible con PHP 8 + MySQL (p. ej. XAMPP, LAMP), sin dependencias de un proveedor específico de nube. |

---

## 4. Información de soporte

### 4.1 Limitaciones conocidas al momento de este documento
- Cuatro páginas (`activar.php`, `admin.php`, `planta.php`, `index(0).php`) referencian un archivo `acceso.php` inexistente como destino de redirección al no haber sesión iniciada.
- La clave de la API de PlantNet está expuesta en el código fuente (ver RNF-SEG-003).
- La tabla `notas` no tiene todavía interfaz de usuario asociada (ver §2.6).
- `index(0).php` parece un archivo duplicado (posiblemente de una descarga repetida) y debería eliminarse del proyecto tras confirmar que `index.php` es la versión vigente.

### 4.2 Apéndice — mapeo con IEEE/EIA 12207.1 (Anexo B de IEEE 830)
Este documento incorpora explícitamente los elementos que el Anexo B de IEEE 830 exige además de la plantilla base: fecha de emisión y estado (portada), organización emisora (portada), historial de cambios (portada), y el detalle de requisitos de interfaz, rendimiento, base de datos lógica, restricciones de diseño y atributos de software (secciones 3.1–3.6). Quedan pendientes para un documento complementario de proceso: plan de gestión de configuración, plan de pruebas y lista de riesgos, tal como se señaló en la auditoría previa del proyecto.
