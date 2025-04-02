# Documentación del Proyecto: El Porvenir Steaks API

Este repositorio contiene una API RESTful desarrollada con Laravel para el sistema de pedidos de El Porvenir Steaks. La API ofrece funcionalidades para gestión de usuarios, productos, categorías, pedidos, repartidores y ubicaciones.

## Índice

- [Requisitos](#requisitos)
- [Configuración del Entorno](#configuración-del-entorno)
- [Inicialización del Proyecto](#inicialización-del-proyecto)
- [Estructura del Proyecto](#estructura-del-proyecto)
- [Base de Datos](#base-de-datos)
- [Endpoints de la API](#endpoints-de-la-api)
- [Autenticación](#autenticación)
- [Pruebas con Postman](#pruebas-con-postman)
- [Desarrollo de la App Móvil](#desarrollo-de-la-app-móvil)
- [Recomendaciones](#recomendaciones)

## Requisitos

- PHP 8.2 o superior
- Composer
- MySQL o SQLite
- Node.js y NPM (para compilar assets)
- Firebase Cloud Messaging para notificaciones push

## Configuración del Entorno

### Clonar el repositorio:

```bash
git clone https://github.com/VasqCD/back-porvenir-steaks.git
cd back-porvenir-steaks
```

### Instalar dependencias de Composer:

```bash
composer install
```

### Instalar dependencias de Node.js:

```bash
npm install
```

### Copiar archivo de entorno:

```bash
cp .env.example .env
```

### Configurar variables de entorno en el archivo `.env`:

```
APP_NAME="El Porvenir Steaks"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=el_porvenir_steaks
DB_USERNAME=root
DB_PASSWORD=

# Configuración Firebase para FCM
FIREBASE_CREDENTIALS=path/to/firebase-credentials.json
```

### Generar clave de la aplicación:

```bash
php artisan key:generate
```

### Configurar Firebase para FCM:

1. Crea un proyecto en Firebase Console
2. Descarga el archivo de credenciales del proyecto
3. Guarda el archivo en una ubicación segura y referencia la ruta en `FIREBASE_CREDENTIALS`

## Inicialización del Proyecto

### Crear la base de datos:

```bash
# En MySQL
mysql -u root -p
CREATE DATABASE el_porvenir_steaks;
exit;

# O simplemente usa SQLite
touch database/database.sqlite
```

### Ejecutar migraciones y seeders:

```bash
php artisan migrate --seed
```

### Compilar assets (si es necesario):

```bash
npm run build
```

### Iniciar el servidor:

```bash
php artisan serve
```

### Generar documentación de la API:

```bash
php artisan scribe:generate
```

La documentación estará disponible en http://localhost:8000/docs.

## Estructura del Proyecto

El proyecto sigue la estructura estándar de Laravel con algunos directorios adicionales:

- `app/Filament`: Panel administrativo usando Filament
- `app/Http/Controllers/Api`: Controladores de la API
- `app/Models`: Modelos de Eloquent
- `app/Observers`: Observadores para los modelos
- `app/Policies`: Políticas de autorización
- `app/Services`: Servicios (AuthService, FcmService)
- `database/migrations`: Migraciones de la base de datos
- `routes/api.php`: Rutas de la API
- `config`: Archivos de configuración

## Base de Datos

El esquema de la base de datos incluye las siguientes tablas principales:

- `users`: Usuarios del sistema (clientes, repartidores, administradores)
- `categorias`: Categorías de productos
- `productos`: Productos disponibles
- `ubicaciones`: Direcciones de entrega
- `repartidores`: Perfil de repartidores
- `pedidos`: Pedidos realizados
- `detalle_pedidos`: Detalles de cada pedido
- `notificaciones`: Sistema de notificaciones
- `historial_estados_pedido`: Historial de cambios de estado

## Endpoints de la API

### Autenticación

- `POST /api/register`: Registro de usuario
- `POST /api/login`: Inicio de sesión
- `POST /api/verificar-codigo`: Verificar código de email
- `POST /api/reenviar-codigo`: Reenviar código de verificación
- `POST /api/recuperar-password`: Solicitar recuperación de contraseña
- `POST /api/cambiar-password`: Cambiar contraseña

### Usuarios

- `GET /api/user`: Obtener perfil del usuario
- `POST /api/user/update`: Actualizar perfil
- `POST /api/logout`: Cerrar sesión

### Categorías

- `GET /api/categorias`: Listar categorías
- `POST /api/categorias`: Crear categoría (admin)
- `GET /api/categorias/{id}`: Ver categoría
- `PUT /api/categorias/{id}`: Actualizar categoría (admin)
- `DELETE /api/categorias/{id}`: Eliminar categoría (admin)

### Productos

- `GET /api/productos`: Listar productos
- `POST /api/productos`: Crear producto (admin)
- `GET /api/productos/{id}`: Ver producto
- `PUT /api/productos/{id}`: Actualizar producto (admin)
- `DELETE /api/productos/{id}`: Eliminar producto (admin)

### Ubicaciones

- `GET /api/ubicaciones`: Listar ubicaciones del usuario
- `POST /api/ubicaciones`: Crear ubicación
- `GET /api/ubicaciones/{id}`: Ver ubicación
- `PUT /api/ubicaciones/{id}`: Actualizar ubicación
- `DELETE /api/ubicaciones/{id}`: Eliminar ubicación

### Pedidos

- `GET /api/pedidos`: Listar pedidos del usuario
- `POST /api/pedidos`: Crear pedido
- `GET /api/pedidos/{id}`: Ver pedido
- `POST /api/pedidos/{id}/estado`: Actualizar estado
- `POST /api/pedidos/{id}/calificar`: Calificar pedido
- `GET /api/pedidos-pendientes`: Listar pedidos pendientes
- `POST /api/pedidos/{id}/asignar-repartidor`: Asignar repartidor (admin)

### Notificaciones

- `GET /api/notificaciones`: Listar notificaciones
- `POST /api/notificaciones/{id}/marcar-leida`: Marcar como leída
- `POST /api/notificaciones/marcar-todas-leidas`: Marcar todas como leídas

### FCM

- `POST /api/fcm/register`: Registrar token FCM
- `POST /api/fcm/test`: Probar notificación push

Para ver la documentación completa de la API con ejemplos, visite la ruta `/docs` después de generar la documentación con Scribe.

## Autenticación

La API utiliza Laravel Sanctum para la autenticación basada en tokens. Para acceder a los endpoints protegidos:

1. Registra un usuario:
   ```
   POST /api/register
   ```

2. Inicia sesión para obtener un token:
   ```
   POST /api/login
   ```

3. Usa el token en los encabezados de las solicitudes:
   ```
   Authorization: Bearer {tu_token}
   ```

## Pruebas con Postman

1. Descarga la colección de Postman desde `/docs.postman` o importa directamente desde la documentación.
2. Configura el entorno de Postman con la variable `base_url` (por defecto: http://localhost:8000).
3. Ejecuta la solicitud de login para obtener un token de autenticación.
4. El token se guardará automáticamente en las variables del entorno para las siguientes solicitudes.

## Desarrollo de la App Móvil

### Requisitos

- La API está diseñada para ser consumida desde aplicaciones móviles.
- Las notificaciones push se manejan a través de Firebase Cloud Messaging.

### Pasos Recomendados

1. Configura Firebase en tu proyecto móvil.
2. Implementa la autenticación utilizando el flujo de tokens.
3. Desarrolla primero las funcionalidades de registro/login, visualización de productos y categorías.
4. Implementa la funcionalidad de pedidos, ubicaciones y notificaciones.
5. Añade la gestión del estado de pedidos para repartidores.

## Recomendaciones

### Seguridad

- No almacenes tokens en localStorage sin cifrado
- Verifica siempre la identidad del usuario al modificar datos
- Valida todos los datos de entrada

### Rendimiento

- Implementa caché en el lado del cliente para reducir solicitudes
- Usa paginación para listas largas de productos

### Experiencia de Usuario

- Implementa estados de carga para operaciones asíncronas
- Almacena datos para uso offline cuando sea posible
- Proporciona mensajes de error claros y específicos

### Pruebas

- Prueba cada endpoint antes de implementar la interfaz
- Verifica los permisos de usuario para cada función
- Comprueba el comportamiento con conexiones inestables

### Notificaciones

- Implementa la gestión de tokens FCM
- Solicita permisos de notificación al usuario
- Maneja notificaciones en segundo plano y en primer plano

Para cualquier consulta adicional o soporte, contacta al equipo de desarrollo backend.

¡Buena suerte con el desarrollo! 🚀
