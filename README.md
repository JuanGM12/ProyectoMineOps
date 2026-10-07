# MineOps - Planning Service

##Diagrama, Pruebas de peticiones y GitFlow en el siguiente enlace:
https://docs.google.com/document/d/1C7aNR01bSPKQfl2c-vH1By1J74a2RcdI/edit?usp=sharing&ouid=112810326757389146509&rtpof=true&sd=true

## Descripción

Microservicio académico encargado de gestionar la planificación de actividades operacionales de MineOps. En esta entrega el único agregado implementado es `Activity`.

## Contexto minero

Planning Service registra actividades con área, ubicación, responsable, prioridad, estado y fechas programadas. Esto permite planear y consultar tareas operativas sin acoplar el servicio a módulos futuros de evidencias, mantenimiento o inventario.

## Arquitectura

El proyecto aplica Clean Architecture con dependencias dirigidas hacia el dominio:

```text
HTTP -> Presentation -> Application -> Domain
                              ^
                              |
                       Infrastructure
```

### Domain

Contiene `Activity`, Value Objects, enums, reglas, excepciones, eventos de dominio y el contrato del repositorio. No depende de Laravel, Eloquent ni MySQL.

### Application

Contiene Commands, Queries, Handlers, DTO, contratos de lectura, Unit of Work y los buses que actúan como Mediator.

### Infrastructure

Implementa persistencia con Eloquent, mapeo entre modelos y agregados, consultas paginadas y transacciones de Laravel.

### Presentation

Contiene Controller, Form Requests y Resource HTTP. Esta capa valida el formato, crea Commands o Queries, los envía al Mediator y transforma sus resultados.

## Patrones

- DDD: `Activity` protege sus invariantes y transiciones.
- CQRS: Commands modifican el agregado y Queries usan un repositorio de lectura paginada.
- Mediator: `CommandBus` y `QueryBus` resuelven los handlers registrados en el contenedor.
- Repository: Domain define `ActivityRepository` e Infrastructure lo implementa con Eloquent.
- Unit of Work: los handlers de escritura ejecutan su operación dentro de una transacción.

## Transiciones de estado

La matriz del agregado es estricta:

| Estado actual | Transiciones permitidas |
|---|---|
| `PENDING` | `IN_PROGRESS`, `CANCELLED` |
| `IN_PROGRESS` | `COMPLETED`, `CANCELLED` |
| `COMPLETED` | Ninguna |
| `CANCELLED` | Ninguna |

Repetir la operación correspondiente al estado actual es idempotente: no cambia el agregado ni registra otro evento.

## Tecnologías

- PHP 8.3
- Laravel 13
- MySQL 8 con InnoDB
- Eloquent
- PHPUnit
- Swagger/OpenAPI 3.1

## Requisitos

- PHP 8.3 o superior
- Composer
- MySQL 8
- Extensiones PHP requeridas por Laravel y `pdo_mysql`

## Instalación

```bash
composer install
copy .env.example .env
php artisan key:generate
```

## Configuración MySQL

Crear una base vacía llamada `mineops_planning` y verificar en `.env`:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mineops_planning
DB_USERNAME=root
DB_PASSWORD=
```

## Migraciones

```bash
php artisan migrate
```

La migration de `activities` declara explícitamente InnoDB porque Unit of Work necesita soporte transaccional.

## Ejecutar servidor

```bash
php artisan serve
```

La API queda disponible en `http://localhost:8000/api/v1`.

## Ejecutar pruebas

Suite rápida con SQLite en memoria:

```bash
php artisan test
vendor/bin/pint --test
```

### Integración real con MySQL

Crear una base cuyo nombre termine obligatoriamente en `_test`, por ejemplo `mineops_planning_test`. La prueba reconstruye exclusivamente esa base y verifica InnoDB y rollback real.

En PowerShell:

```powershell
$env:RUN_MYSQL_INTEGRATION='1'
$env:MYSQL_TEST_DATABASE='mineops_planning_test'
$env:MYSQL_TEST_HOST='127.0.0.1'
$env:MYSQL_TEST_PORT='3306'
$env:MYSQL_TEST_USERNAME='root'
$env:MYSQL_TEST_PASSWORD=''
php artisan test tests/Integration/MySqlUnitOfWorkTest.php
```

## API

| Método | Ruta | Descripción |
|---|---|---|
| GET | `/api/v1/health` | Estado del servicio |
| POST | `/api/v1/activities` | Crear actividad |
| GET | `/api/v1/activities` | Listar y filtrar actividades |
| GET | `/api/v1/activities/{id}` | Consultar actividad |
| PUT | `/api/v1/activities/{id}` | Actualizar actividad |
| PATCH | `/api/v1/activities/{id}/start` | Iniciar actividad |
| PATCH | `/api/v1/activities/{id}/complete` | Completar actividad |
| PATCH | `/api/v1/activities/{id}/cancel` | Cancelar actividad |
| GET | `/api/v1/activities/pending` | Listar pendientes |
| GET | `/api/v1/activities/overdue` | Listar vencidas no finalizadas |
| GET | `/api/v1/activities/responsible/{responsibleId}` | Listar por responsable |

El listado admite `page`, `per_page`, `status`, `priority`, `responsible_id`, `scheduled_from`, `scheduled_to`, `sort` y `direction`.

Las respuestas exitosas usan `success` y `data`. Los errores usan `success`, `message` y `errors`.

## Swagger

- Interfaz: `http://localhost:8000/api/documentation`
- Especificación: `http://localhost:8000/api/openapi.yaml`
- Fuente: `docs/openapi.yaml`

## Git Flow

1. Crear `feature/<responsabilidad>` desde `develop`.
2. Implementar y ejecutar PHPUnit y Pint.
3. Publicar la rama.
4. Abrir Pull Request hacia `develop`.
5. Solicitar revisión de otro integrante.
6. Integrar `develop` en `main` únicamente cuando la entrega esté validada.

## Integrantes

Anderson Arley Cano Osorio
Juan Diego Patiño Osorio
Jose Ricardo Quiros García
Juan Pablo Rebolledo

## Arquitectura futura

Fases posteriores podrán incorporar Evidence Service, Forms Service, Maintenance Service, Inventory Service, SST Service, Notification Service, Sync Service y una aplicación Android offline-first. Esos componentes no forman parte de esta entrega.

Los Domain Events de Activity están preparados, pero su publicación se deja para una fase futura que pueda garantizar despacho posterior al commit mediante outbox o mecanismo equivalente.

##Diagrama, Pruebas de peticiones y GitFlow en el siguiente enlace:
https://docs.google.com/document/d/1C7aNR01bSPKQfl2c-vH1By1J74a2RcdI/edit?usp=sharing&ouid=112810326757389146509&rtpof=true&sd=true
