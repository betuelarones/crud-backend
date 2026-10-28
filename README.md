# Overskul CRUD API

API REST para la gestión de productos y categorías, construida con **Laravel 13**, **PHP 8.4** y **MySQL/MariaDB**. Incluye validaciones en español, paginación, búsqueda, eliminación lógica, auditoría de cambios, documentación OpenAPI y monitoreo con Telescope.

## Stack

- PHP 8.4 y Laravel 13
- MySQL 8 / MariaDB 11
- `spatie/laravel-activitylog` para auditoría de modelos
- `darkaonline/l5-swagger` para documentación OpenAPI
- `laravel/telescope` para observabilidad local
- PHPUnit para pruebas de integración

## Funcionalidades

- CRUD completo de categorías en `/api/v1/categories`.
- CRUD completo de productos en `/api/v1/products`.
- Eliminación lógica (`SoftDeletes`) en ambos recursos.
- Búsqueda por nombre, filtro por categoría y paginación configurable.
- Validación con Form Requests y mensajes en español.
- Categorías con productos activos no se pueden eliminar (HTTP `409`).
- No se pueden asignar productos a categorías inexistentes o eliminadas.
- Registro automático de creación, actualización y eliminación con Activity Log.
- Respuestas JSON homogéneas y manejo centralizado de errores.
- Documentación interactiva Swagger UI.
- Telescope accesible en entorno local.

## Requisitos

- PHP 8.4 o superior con las extensiones habituales de Laravel
- Composer 2
- MySQL 8 o MariaDB 11
- Base de datos `crud_backend` creada con `utf8mb4_unicode_ci`

## Instalación

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
```

Configura la conexión en `.env`:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=crud_backend
DB_USERNAME=root
DB_PASSWORD=root

APP_URL=http://127.0.0.1:8000
FRONTEND_URL=http://localhost:5173
```

Crea la base de datos si aún no existe:

```sql
CREATE DATABASE crud_backend CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Ejecuta migraciones y datos de ejemplo:

```powershell
php artisan migrate --seed
```

Genera la documentación y levanta el servidor:

```powershell
php artisan l5-swagger:generate
php artisan serve --host=127.0.0.1 --port=8000
```

El seeding crea 4 categorías y 12 productos de ejemplo de forma idempotente.

## Docker (WSL + MySQL 8.4)

El backend incluye un stack Docker autocontenido con PHP-FPM, Nginx y MySQL. No necesita PHP, Composer ni MySQL instalados en Windows.

Requisitos:

- Docker Desktop con integración para Ubuntu en WSL 2.
- Ejecutar los comandos desde la terminal de Ubuntu.

```bash
cd crud-backend
docker compose up -d --build
```

Servicios y puertos:

| Servicio | Puerto host | Descripción |
| --- | --- | --- |
| `nginx` | `8000` | API, Swagger UI y Telescope |
| `mysql` | `3307` | MySQL 8.4 (el puerto 3306 queda libre para MariaDB local) |
| `app` | interno `9000` | PHP 8.4 FPM |

URLs:

- API: `http://localhost:8000/api/v1`
- Swagger: `http://localhost:8000/api/docs`
- Telescope: `http://localhost:8000/telescope`

El arranque genera y persiste `APP_KEY`, ejecuta las migraciones, siembra datos solo si la tabla `categories` está vacía y regenera Swagger. Los datos se guardan en los volúmenes `crud-backend_mysql_data` y `crud-backend_storage_data`.

```bash
docker compose logs -f app
docker compose down          # conserva los volúmenes
docker compose down -v       # borra MySQL y storage; el siguiente arranque vuelve a sembrar
```

Credenciales locales de MySQL: base `crud_backend`, usuario `crud_user`, contraseña `crud_password`, root `root_password`. Están pensadas solo para desarrollo local.

La imagen oficial de MySQL no tiene variante Alpine. Se usa `mysql:8.4` como única excepción; PHP, Nginx y el frontend usan imágenes Alpine.

## URLs

| Recurso | URL |
| --- | --- |
| API | `http://127.0.0.1:8000/api/v1` |
| Swagger UI | `http://127.0.0.1:8000/api/docs` |
| Telescope | `http://127.0.0.1:8000/telescope` |

## Endpoints

| Método | Ruta | Descripción |
| --- | --- | --- |
| `GET` | `/api/v1/categories` | Listado paginado de categorías |
| `POST` | `/api/v1/categories` | Crear categoría |
| `GET` | `/api/v1/categories/{id}` | Ver categoría |
| `PUT/PATCH` | `/api/v1/categories/{id}` | Actualizar categoría |
| `DELETE` | `/api/v1/categories/{id}` | Eliminar categoría (lógica) |
| `GET` | `/api/v1/products` | Listado paginado de productos |
| `POST` | `/api/v1/products` | Crear producto |
| `GET` | `/api/v1/products/{id}` | Ver producto |
| `PUT/PATCH` | `/api/v1/products/{id}` | Actualizar producto |
| `DELETE` | `/api/v1/products/{id}` | Eliminar producto (lógica) |

Parámetros de listado:

- Categorías: `search`, `page`, `per_page` (1-100, por defecto 100).
- Productos: `search`, `category_id`, `page`, `per_page` (1-100, por defecto 10).

Campos de categoría: `name` (obligatorio, máx. 120), `description` (opcional, máx. 2000).
Campos de producto: `name` (obligatorio, máx. 150), `description` (opcional), `price` (obligatorio, > 0), `stock` (obligatorio, entero >= 0), `category_id` (obligatorio y activo).

## Formato de respuestas

Listado:

```json
{
  "success": true,
  "message": "Productos obtenidos correctamente.",
  "data": {
    "items": [],
    "pagination": {
      "current_page": 1,
      "per_page": 10,
      "total": 0,
      "last_page": 1,
      "from": null,
      "to": null
    }
  }
}
```

Error de validación:

```json
{
  "success": false,
  "message": "Los datos enviados no son válidos.",
  "errors": {
    "name": ["El nombre es obligatorio."]
  }
}
```

- `DELETE` responde `204 No Content` sin cuerpo.
- Un recurso inexistente o eliminado responde `404`.
- Intentar eliminar una categoría con productos activos responde `409` con el mensaje correspondiente.

## Soft deletes y auditoría

- `categories` y `products` usan `deleted_at`; los listados y relaciones excluyen registros eliminados.
- `spatie/laravel-activitylog` registra eventos `created`, `updated` y `deleted` sobre los modelos.
- Al eliminar una categoría se valida que no tenga productos activos.

## Swagger

La especificación se genera a partir de las anotaciones en controladores y los esquemas de `app/OpenApi`. Para regenerarla tras cambios:

```powershell
php artisan l5-swagger:generate
```

El JSON generado queda en `storage/api-docs/api-docs.json`.

## Telescope

Disponible en `/telescope`. En `local` el acceso está permitido sin autenticación; fuera de local se limita a los correos definidos en `TELESCOPE_ALLOWED_EMAILS` y al gate `viewTelescope`. Los parámetros y cabeceras sensibles están ocultos.

Variables relevantes:

```dotenv
TELESCOPE_ENABLED=true
TELESCOPE_PATH=telescope
TELESCOPE_ALLOWED_EMAILS=
```

## Pruebas

```powershell
php artisan test
```

Cobertura actual: **19 pruebas y 129 aserciones**, incluyendo CRUD, validaciones, paginación, búsqueda, filtros, soft delete, bloqueo de eliminación de categorías y auditoría. Las pruebas usan SQLite en memoria.

## Estructura relevante

```text
app/
  Http/
    Controllers/Api/V1/   Controladores de categorías y productos
    Requests/             Validación de entrada
    Resources/            Serialización JSON
  Models/                 Category, Product
  OpenApi/                Esquemas y especificación Swagger
  Support/ApiResponse.php Respuestas y paginación homogéneas
database/
  migrations/             Tablas con soft deletes e índices
  factories/ seeders/     Datos de ejemplo
tests/Feature/Api/         Pruebas de la API y auditoría
```

## Decisiones

- Sin autenticación: la prueba no la requiere; el foco está en el CRUD, validaciones y frontend.
- Se respetó Laravel 13, la versión instalada en el proyecto.
- La eliminación de categorías con productos activos devuelve `409` para evitar dejar productos huérfanos.
- Los precios se serializan como cadena decimal para preservar precisión.
