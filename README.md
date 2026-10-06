# MineOps - Planning Service

Base compartida del microservicio académico **Planning Service**. La implementación funcional se realizará mediante ramas separadas y Pull Requests hacia `develop`.

Esta rama base no contiene implementaciones de `Domain`, `Application`, `Infrastructure` ni `Presentation`. Cada responsable debe crear únicamente los archivos correspondientes a su tarea.

## Incluido en la base

- Laravel 13 y PHP 8.3.
- Configuración local de MySQL en `.env.example`.
- API versionada bajo `/api/v1`.
- Endpoint compartido `GET /api/v1/health`.
- Swagger UI y contrato OpenAPI inicial.
- Formato JSON común para errores HTTP, validaciones y errores inesperados.
- PHPUnit y Laravel Pint.

## Instalación con WAMP y MySQL

1. Iniciar Apache y MySQL desde WAMP.
2. Crear una base de datos vacía llamada `mineops_planning`.
3. Instalar y configurar el proyecto:

```bash
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

La configuración inicial utiliza:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mineops_planning
DB_USERNAME=root
DB_PASSWORD=
```

Si MySQL tiene una contraseña diferente, debe actualizarse únicamente el archivo `.env` local.

## Verificación de la base

```bash
php artisan test
vendor/bin/pint --test
php artisan route:list --path=api
```

- Salud: `http://localhost:8000/api/v1/health`
- Swagger UI: `http://localhost:8000/api/documentation`
- OpenAPI: `docs/openapi.yaml`

## Flujo de trabajo

1. Crear `develop` desde la rama base.
2. Cada integrante crea `feature/<responsabilidad>` desde `develop`.
3. Implementar solamente la responsabilidad asignada.
4. Ejecutar pruebas y Pint.
5. Abrir Pull Request hacia `develop`.
6. Solicitar revisión de al menos otro integrante antes del merge.

Los futuros directorios deberán respetar estas dependencias:

```text
Presentation -> Application -> Domain
Infrastructure -> Application / Domain
```

`Domain` no puede importar Laravel, Eloquent, controladores ni clases de infraestructura.
