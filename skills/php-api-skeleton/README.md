# api-skeleton

Skill para generar APIs REST PHP desde cero. Incluye Router singleton, MVC, Auth JWT y roles.

## Uso rápido

```bash
api-skeleton mi-api
cd mi-api
php -S localhost:8080
```

## Schema SQL requerido

```sql
CREATE TABLE users (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email      VARCHAR(255) NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,
    name       VARCHAR(100) DEFAULT NULL,
    role       ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    active     TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## Patrones implementados

| Patrón         | Archivo              | Descripción                          |
|----------------|----------------------|--------------------------------------|
| Singleton      | Router, Database     | Instancia única con getInstance()    |
| Front Controller | index.php          | Punto de entrada único               |
| MVC            | Controllers + Models | Separación de lógica y datos         |
| Repository     | User.php             | Acceso a datos centralizado          |
| Middleware     | Auth.php             | Guards reutilizables por rol         |

## Estructura de directorios

```
src/
├── Core/           # Infraestructura (Router, DB, Auth, JWT)
├── Controllers/    # Lógica HTTP (request → response)
└── Models/         # Acceso a datos (PDO queries)
```

## Agregar un nuevo recurso (ejemplo: Post)

1. Crear `src/Models/Post.php` con los métodos necesarios
2. Crear `src/Controllers/PostController.php`
3. Registrar las rutas en `index.php`

```php
// index.php
$router->get('/api/posts',        'App\Controllers\PostController@index');
$router->get('/api/posts/{id}',   'App\Controllers\PostController@show');
$router->post('/api/posts',       'App\Controllers\PostController@store');
$router->patch('/api/posts/{id}', 'App\Controllers\PostController@update');
$router->delete('/api/posts/{id}','App\Controllers\PostController@destroy');
```

## Configuración .htaccess (Apache)

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^ index.php [QSA,L]
```

## Configuración nginx

```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```
