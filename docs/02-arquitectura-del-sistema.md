# ConsorciosPro — Arquitectura del Sistema

**Versión:** 1.1  
**Fecha:** 2026-04-28  
**Notas de cambio:** Visión producto; módulos transversales (SIRO, PDF, portal, informes).

---

## 1. Visión del producto

Sistema **mono-empresa** (single-tenant) para Oliva Administraciones, con diseño que permita **escalar** a otros clientes en el futuro (separación de datos de tenant, sin constantes del cliente en el núcleo). La especificación funcional vive en `docs/01-requisitos-del-sistema.md`.

---

## 2. Stack Tecnológico

| Capa | Tecnología | Versión | Justificación |
|---|---|---|---|
| **Backend** | Laravel | 11.x | Framework PHP maduro, excelente ORM (Eloquent), migraciones, API Resources. Compatible con PHP 8.2 |
| **Frontend** | Blade + Livewire + Alpine.js | 3.x / 3.x | SPA-like sin la complejidad de un framework JS separado. Livewire para interactividad server-side, Alpine para micro-interacciones |
| **CSS** | TailwindCSS | 4.x | Utility-first, altamente personalizable, excelente para diseño responsive y moderno |
| **Base de datos** | MySQL | 8.0 | Según restricción del hosting |
| **Servidor** | Apache | — | Según restricción del hosting |
| **Hosting** | Hostinger Cloud | — | Restricción del proyecto |

### ¿Por qué Livewire en vez de Vue/React?

1. **Simplicidad de deploy:** No requiere build step de JS en producción, simplifica el deploy en Hostinger
2. **Menor complejidad:** No necesitamos gestión de estado del lado del cliente (Vuex/Redux)
3. **SPA-feel:** Livewire da interactividad tipo SPA (modales, filtros, tablas dinámicas) sin API REST
4. **Ideal para ABMs:** Los CRUDs con modales, búsquedas y tablas son el caso de uso ideal de Livewire
5. **Alpine.js:** Para micro-interacciones (toggles, dropdowns, animaciones) donde Livewire es excesivo

### Alternativa: Si preferís separar front y back

Se podría usar **Laravel como API REST + Vue 3 (Inertia.js)**. Inertia permite tener un SPA real con Vue pero usando routing del lado de Laravel. Es más complejo pero da más control del frontend. Lo discutimos si querés.

---

## 3. Arquitectura de Alto Nivel

```
┌──────────────────────────────────────────────────┐
│                   BROWSER                         │
│  ┌────────────────────────────────────────────┐   │
│  │   TailwindCSS + Alpine.js + Livewire JS    │   │
│  └────────────────────────────────────────────┘   │
└───────────────────────┬──────────────────────────┘
                        │ HTTP/WebSocket (Livewire)
┌───────────────────────┴──────────────────────────┐
│                   APACHE                          │
│  ┌────────────────────────────────────────────┐   │
│  │              Laravel 11                     │   │
│  │  ┌──────────┐ ┌──────────┐ ┌────────────┐  │   │
│  │  │  Routes  │ │Middleware│ │   Auth      │  │   │
│  │  └─────┬────┘ └──────────┘ └────────────┘  │   │
│  │        │                                    │   │
│  │  ┌─────▼────────────────────────────────┐   │   │
│  │  │         Livewire Components          │   │   │
│  │  │  ┌─────────┐ ┌──────────┐ ┌───────┐  │   │   │
│  │  │  │Consorcio│ │Unidades  │ │Budget │  │   │   │
│  │  │  │Manager  │ │Manager   │ │Manager│  │   │   │
│  │  │  └─────────┘ └──────────┘ └───────┘  │   │   │
│  │  │  ┌──────────┐ ┌──────────┐ ┌────────┐ │   │   │
│  │  │  │Settlement│ │ Expense  │ │Portal  │ │   │   │
│  │  │  │ Engine   │ │ /Gastos  │ │Auth.   │ │   │   │
│  │  │  └──────────┘ └──────────┘ └────────┘ │   │   │
│  │  │  ┌──────────┐ ┌──────────┐           │   │   │
│  │  │  │Informes  │ │PDF/Email │           │   │   │
│  │  │  └──────────┘ └──────────┘           │   │   │
│  │  └──────────────────────────────────────┘   │   │
│  │        │                                    │   │
│  │  ┌─────▼────────────────────────────────┐   │   │
│  │  │          Service Layer               │   │   │
│  │  │  ┌───────────────┐ ┌──────────────┐  │   │   │
│  │  │  │ BudgetService │ │ Settlement   │  │   │   │
│  │  │  │               │ │ Calculator   │  │   │   │
│  │  │  ├───────────────┴──────────────┤  │   │   │
│  │  │  │ RecargoDiario / SiroCupón / PDF │  │   │   │
│  │  │  └───────────────────────────────┘  │   │   │
│  │  └──────────────────────────────────────┘   │   │
│  │        │                                    │   │
│  │  ┌─────▼────────────────────────────────┐   │   │
│  │  │     Eloquent Models (ORM)            │   │   │
│  │  └──────────────────────────────────────┘   │   │
│  └────────────────────────────────────────────┘   │
└───────────────────────┬──────────────────────────┘
                        │
                ┌───────▼───────┐
                │   MySQL 8.0   │
                └───────────────┘
```

---

## 4. Módulos transversales

Capacidades que atraviesan varios dominios (detalle en `docs/01-requisitos-del-sistema.md`):

| Área | Función |
|------|---------|
| **SIRO / cobranzas** | Datos para cupones (códigos de barras/QR); cupón **por cada mes adeudado**; montos según motor de **recargo diario** |
| **PDF y correo** | Paquete cupón + informe económico/balance + cuerpo administrativo (notas del consorcio); flujo de **aprobación** previo al envío cuando aplique |
| **Portal de autogestión** | Propietarios/inquilinos: cupones, historial, reglamento, emergencias, contacto encargado |
| **Informes** | Balance, flujo de caja, discriminación de ingresos, deudores, deuda proveedores, estadísticas, conciliación |

Servicios orientativos en `app/Services/`: por ejemplo `LiquidacionCalculator`, `PresupuestoService`, `RecargoDiarioCalculator`, generador SIRO/PDF; **jobs/colas** Laravel si el volumen de PDF/envíos lo justifica.

---

## 5. Estructura del Proyecto Laravel

```
ConsorciosPro/
├── app/
│   ├── Http/
│   │   ├── Controllers/          # Controllers mínimos (Livewire maneja la lógica)
│   │   └── Middleware/
│   ├── Livewire/
│   │   ├── Consorcios/
│   │   │   ├── ConsorcioList.php
│   │   │   ├── ConsorcioForm.php
│   │   │   └── ConsorcioDetail.php
│   │   ├── Unidades/
│   │   │   ├── UnidadList.php
│   │   │   ├── UnidadForm.php
│   │   │   └── UnidadDetail.php
│   │   ├── Presupuestos/
│   │   │   ├── PresupuestoManager.php
│   │   │   ├── ConceptoForm.php
│   │   │   └── PresupuestoClone.php
│   │   ├── Liquidaciones/
│   │   │   ├── LiquidacionManager.php
│   │   │   ├── ConceptoConfig.php
│   │   │   └── LiquidacionPreview.php
│   │   ├── Gastos/
│   │   │   ├── GastoList.php
│   │   │   └── GastoForm.php
│   │   ├── Portal/
│   │   │   └── …                         # Autogestión (rutas/guards específicos)
│   │   ├── Informes/
│   │   │   └── …
│   │   └── Dashboard.php
│   ├── Models/
│   │   ├── Consorcio.php
│   │   ├── Unidad.php
│   │   ├── Propietario.php
│   │   ├── Inquilino.php
│   │   ├── Inmobiliaria.php
│   │   ├── Presupuesto.php
│   │   ├── ConceptoPresupuesto.php
│   │   ├── Liquidacion.php
│   │   ├── LiquidacionConcepto.php
│   │   ├── LiquidacionDetalle.php
│   │   ├── Gasto.php
│   │   ├── Proveedor.php
│   │   └── User.php
│   └── Services/
│       ├── PresupuestoService.php      # Lógica de clonación, ajustes
│       ├── LiquidacionCalculator.php   # Motor de cálculo de liquidación
│       ├── GastoService.php
│       ├── RecargoDiarioCalculator.php # Importes cupón según SRS §2.5
│       └── InformeEconomicoBuilder.php # Balance / exportaciones (evolución)
├── database/
│   ├── migrations/
│   └── seeders/
├── resources/
│   └── views/
│       ├── layouts/
│       │   └── app.blade.php           # Layout principal con sidebar
│       ├── livewire/
│       │   ├── consorcios/
│       │   ├── unidades/
│       │   ├── presupuestos/
│       │   ├── liquidaciones/
│       │   ├── gastos/
│       │   └── dashboard.blade.php
│       └── components/
│           ├── modal.blade.php
│           ├── data-table.blade.php
│           ├── stat-card.blade.php
│           └── breadcrumb.blade.php
├── routes/
│   └── web.php
├── public/
│   └── img/
│       ├── logo_CP.png
│       └── logo_cliente.png
├── AGENTS.md                           # Índice de contexto para agentes IA
├── .cursorrules                        # Reglas Cursor (raíz)
└── .cursor/rules/*.mdc                 # Reglas modulares versionadas
```

---

## 6. Diseño de la Interfaz — Principios

### 6.1 Sistema de Navegación

**Sidebar colapsable** (no navbar superior como en los prototipos):
- Logo CP + nombre "ConsorciosPro" en la parte superior
- Ítems (administración): Dashboard, Consorcios, Unidades, Presupuestos, Liquidaciones, Gastos/Comprobantes, Informes (según implementación); portal autogestión en rutas separadas
- La sidebar colapsa a íconos en pantallas medianas y se oculta (hamburger) en mobile
- Logo del cliente en el footer de la sidebar

### 6.2 Layout General

```
┌────────┬──────────────────────────────────────────┐
│        │  Breadcrumb / Título de Página            │
│  SIDE  │──────────────────────────────────────────│
│  BAR   │                                          │
│        │  Contenido Principal                     │
│  Logo  │  (Cards, Tablas, Formularios)            │
│  Nav   │                                          │
│  Items │                                          │
│        │                                          │
│  Logo  │──────────────────────────────────────────│
│ Client │  Footer                                  │
└────────┴──────────────────────────────────────────┘
```

### 6.3 Paleta de Colores

| Uso | Color | Hex |
|---|---|---|
| Primary | Azul profundo | `#1E3A5F` |
| Primary Light | Azul claro | `#4A90D9` |
| Secondary | Gris neutro | `#6B7280` |
| Success | Verde esmeralda | `#059669` |
| Warning | Ámbar | `#D97706` |
| Danger | Rojo intenso | `#DC2626` |
| Background | Gris claro | `#F3F4F6` |
| Cards | Blanco | `#FFFFFF` |
| Text Primary | Gris oscuro | `#111827` |
| Text Secondary | Gris medio | `#6B7280` |

### 6.4 Componentes Clave

1. **Dashboard:** Grid de tarjetas con íconos, contadores, y accesos directos (inspirado en el prototipo que le gustó al cliente)
2. **Tablas de datos:** Con búsqueda, filtros, paginación, selección múltiple
3. **Formularios ABM:** Formularios amplios en modales o páginas completas con secciones colapsables para datos extensos (propietario, inquilino, inmobiliaria)
4. **Pantalla de Liquidación:** Tabla interactiva con configuración global arriba, conceptos en filas, acciones inline
5. **Loading states:** Skeleton loaders en Livewire, nunca pantallas en blanco

---

## 7. Seguridad

| Aspecto | Implementación |
|---|---|
| Autenticación | Laravel Breeze (login simple con email/password) |
| CSRF | Protección nativa de Laravel |
| Validaciones | Form Requests de Laravel en cada operación |
| SQL Injection | Eloquent ORM (prepared statements) |
| XSS | Blade escaping automático |
| Passwords | bcrypt hashing (Laravel default) |

---

## 8. Deploy en Hostinger

1. **Git push** → repositorio remoto
2. **SSH a Hostinger** → `git pull`
3. `composer install --no-dev`
4. `php artisan migrate`
5. `php artisan config:cache`
6. `php artisan route:cache`
7. `.env` configurado con datos de MySQL de Hostinger

> Se puede crear un script de deploy automatizado o usar GitHub Actions si Hostinger lo soporta.
