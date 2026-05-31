# Skill: api-skeleton

## Descripción
Genera una API REST PHP lista para usar con Router singleton, MVC, Auth JWT y roles user/admin.
Sin dependencias externas (no requiere Composer).

## Activación
```
/api-skeleton <nombre-proyecto>
api-skeleton <nombre-proyecto>
```

## Comportamiento

1. Crea `<nombre-proyecto>/` en el directorio actual
2. Copia todos los archivos del skeleton
3. Reemplaza el namespace `App` por el nombre del proyecto en PascalCase
4. Imprime la estructura generada y los pasos para iniciar

## Estructura generada

```
<nombre-proyecto>/
├── index.php              # Front Controller + CORS + rutas
├── config.php             # DB (host/port/dbname/user/pass) + jwt_secret + internal_key
├── autoload.php           # PSR-4 manual sin Composer
└── src/
    ├── Core/
    │   ├── Router.php     # Singleton, dispatch, {param} dinámicos, Router::json()
    │   ├── Database.php   # Singleton PDO con utf8mb4
    │   ├── Auth.php       # requireUser() / requireAdmin() / requireInternal()
    │   └── Jwt.php        # encode() / decode() HS256 sin librerías
    ├── Controllers/
    │   └── AuthController.php   # login, register, me
    └── Models/
        └── User.php       # findByEmail, findById, create, all, update, delete
```

## Archivos clave

### Router.php — Singleton + Front Controller
- `getInstance()` con constructor privado
- Métodos: `get()`, `post()`, `patch()`, `delete()`
- `dispatch()` parsea URI, captura `{params}` con regex `([^/]+)`
- `call()` resuelve `Controller@method` con validación
- `json()` helper estático para todas las respuestas

### Database.php — Singleton PDO
- Conexión única reutilizable
- Lee config desde `BASE_PATH . '/config.php'`
- `ERRMODE_EXCEPTION`, `FETCH_ASSOC`, sin emulación de prepares

### Jwt.php — HS256 sin dependencias
- `encode(array $payload, string $secret): string`
- `decode(string $token, string $secret): ?array`
- Valida firma con `hash_equals` (timing-safe)
- Respeta `exp` automáticamente

### Auth.php — Middleware de roles
- `requireUser()`: extrae Bearer token, decodifica, retorna payload o `exit 401`
- `requireAdmin()`: llama `requireUser()` + verifica `role === 'admin'` o `exit 403`
- `requireInternal()`: verifica header `X-Internal-Key` para bots/cron

### User.php — Modelo completo
- `findByEmail()`, `findById()`, `create()`, `all()`, `update()`, `delete()`
- `update()` acepta campos dinámicos con whitelist `['name', 'role', 'active']`

### AuthController.php
- `login`: valida credenciales, genera JWT 60 días
- `register`: verifica email único, hashea password, genera JWT
- `me`: ruta protegida, retorna datos del usuario autenticado

## Rutas incluidas

| Método | Ruta                  | Auth     | Acción              |
|--------|-----------------------|----------|---------------------|
| POST   | /api/auth/register    | —        | Registro de usuario |
| POST   | /api/auth/login       | —        | Login, retorna JWT  |
| GET    | /api/auth/me          | Bearer   | Datos del usuario   |

## Cómo agregar rutas

**Ruta protegida (user):**
```php
// index.php
$router->get('/api/profile', 'App\Controllers\ProfileController@show');

// ProfileController.php
public function show(array $params): void
{
    $payload = Auth::requireUser(); // lanza 401 si no hay token válido
    Router::json(['id' => $payload['sub']]);
}
```

**Ruta admin:**
```php
// index.php
$router->get('/api/admin/users', 'App\Controllers\AdminController@index');

// AdminController.php
public function index(array $params): void
{
    Auth::requireAdmin(); // lanza 401/403 si no es admin
    $users = (new User())->all();
    Router::json(['users' => $users]);
}
```

**Ruta con parámetro dinámico:**
```php
$router->patch('/api/users/{id}', 'App\Controllers\UserController@update');

public function update(array $params): void
{
    Auth::requireAdmin();
    $id   = (int) $params['id'];
    $body = json_decode(file_get_contents('php://input'), true) ?? [];
    $ok   = (new User())->update($id, $body);
    Router::json(['updated' => $ok]);
}
```

## Verificación

```bash
# 1. Generar proyecto
api-skeleton mi-api

# 2. Crear BD y tabla
mysql -u root -e "CREATE DATABASE mi_api CHARACTER SET utf8mb4;"
mysql -u root mi_api < mi-api/schema.sql   # ver README.md del proyecto

# 3. Iniciar servidor
php -S localhost:8080 -t mi-api

# 4. Registrar usuario
curl -X POST http://localhost:8080/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{"email":"test@test.com","password":"123456","name":"Test"}'

# 5. Login
curl -X POST http://localhost:8080/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@test.com","password":"123456"}'

# 6. Endpoint protegido
curl http://localhost:8080/api/auth/me \
  -H "Authorization: Bearer <TOKEN>"
```

## Notas
- Cambiar `jwt_secret` e `internal_key` en `config.php` antes de producción
- Agregar `.htaccess` o configurar nginx para redirigir todo a `index.php`
- El token JWT expira en 60 días (`86400 * 60`); ajustar en `AuthController`
- Para CORS en producción, actualizar `$allowedOrigins` en `index.php`
