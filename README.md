# ConsorciosPro

Sistema web de administración de consorcios para **Oliva Administraciones**: presupuestos, liquidación de expensas, gastos/facturas, integración SIRO y portal de autogestión.

La especificación funcional (**SRS**) está en [`docs/01-requisitos-del-sistema.md`](docs/01-requisitos-del-sistema.md).

## Documentación

| Documento | Descripción |
|-----------|-------------|
| [docs/01-requisitos-del-sistema.md](docs/01-requisitos-del-sistema.md) | Requisitos (fuente de verdad) |
| [docs/02-arquitectura-del-sistema.md](docs/02-arquitectura-del-sistema.md) | Stack y arquitectura |
| [docs/03-modelo-de-datos.md](docs/03-modelo-de-datos.md) | Modelo de datos |
| [docs/04-reglas-de-negocio.md](docs/04-reglas-de-negocio.md) | Reglas de negocio |
| [docs/05-plan-de-fases.md](docs/05-plan-de-fases.md) | Plan por fases |

## Stack

- PHP 8.2, Laravel 11  
- Livewire 3, Alpine.js, TailwindCSS 4  
- MySQL 8.0  

## Requisitos locales

- PHP 8.2+ con extensiones habituales de Laravel  
- Composer  
- Node.js + npm (assets/Vite)  
- MySQL 8  

## Puesta en marcha (desarrollo)

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Configurar base de datos en `.env`, luego:

```bash
php artisan migrate
npm install && npm run dev
php artisan serve
```

## Contexto para agentes (Cursor)

- [`AGENTS.md`](AGENTS.md) — índice  
- [`.cursorrules`](.cursorrules) — reglas en raíz  
- [`.cursor/rules/`](.cursor/rules/) — reglas modulares (`*.mdc`, versionadas en Git)

## Licencia

El framework Laravel incluido en el proyecto es open source bajo la [licencia MIT](https://opensource.org/licenses/MIT). La aplicación ConsorciosPro y su documentación propia siguen la licencia definida por el propietario del repositorio.
