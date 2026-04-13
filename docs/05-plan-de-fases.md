# ConsorciosPro — Plan de Implementación por Fases

**Versión:** 1.0
**Fecha:** 2026-03-23

---

## Resumen

Este documento define las fases de desarrollo del sistema ConsorciosPro. Cada fase es un entregable funcional que se puede demostrar al cliente.

---

## Fase 0: Setup del Proyecto (1-2 días)

### Objetivo
Proyecto Laravel funcionando con autenticación y layout base.

### Tareas
1. Instalar Laravel 11 con Breeze (autenticación)
2. Configurar Livewire 3 + Alpine.js
3. Configurar TailwindCSS 4
4. Crear layout principal con sidebar colapsable
5. Crear dashboard con tarjetas de navegación
6. Subir logos del cliente y del sistema
7. Configurar `.env` para MySQL local (WAMP)
8. Primer commit en Git

### Entregable
Sistema con login, dashboard con tarjetas, y sidebar de navegación.

---

## Fase 1: ABM Consorcios (2-3 días)

### Objetivo
CRUD completo de consorcios con búsqueda y filtros.

### Tareas
1. Migración: tabla `consorcios`
2. Modelo: `Consorcio.php` con validaciones
3. Seeder con datos de ejemplo
4. Componente Livewire: `ConsorcioList` (tabla con búsqueda, paginación)
5. Componente Livewire: `ConsorcioForm` (modal de creación/edición)
6. Vista detalle de consorcio
7. Soft delete con confirmación

### Campos del formulario
Todos los campos definidos en el SRS §2.1, organizados en secciones:
- Datos generales (nombre, dirección, CUIT)
- Datos bancarios (banco, cuenta, CBU, convenio)
- Datos catastrales (nomenclatura, cuenta rentas)
- Encargado (nombre, apellido, teléfono, horarios)
- Nota

---

## Fase 2: ABM Unidades Funcionales (3-4 días)

### Objetivo
CRUD de departamentos/PH dentro de cada consorcio, con datos de propietario, inquilino e inmobiliaria.

### Tareas
1. Migraciones: `unidades`, `propietarios`, `inquilinos`, `inmobiliarias`
2. Modelos con relaciones Eloquent
3. Seeders
4. Componente Livewire: `UnidadList` (filtrable por consorcio)
5. Componente Livewire: `UnidadForm` (formulario multipanel con secciones colapsables)
6. Validación de que la suma de coeficientes por consorcio ≈ 100%
7. Indicador visual del total de coeficientes

### Formulario organizado en secciones
- Datos de la unidad
- Datos del propietario
- Datos del inquilino (colapsable, opcional)
- Datos de inmobiliaria/encargado (colapsable, opcional)
- Configuración fiscal (IVA, recibos)

---

## Fase 3: Presupuestos Mensuales (4-5 días)

### Objetivo
Crear, clonar y gestionar presupuestos mensuales con conceptos.

### Tareas
1. Migraciones: `presupuestos`, `concepto_presupuestos`
2. Modelos con relaciones
3. `PresupuestoService`: lógica de clonación y ajustes
4. Componente Livewire: `PresupuestoManager` (UI principal)
5. Componente Livewire: `ConceptoForm` (agregar/editar concepto inline o modal)
6. Lógica de cuotas (incremento automático, concepto expirado)
7. Lógica de estimados → ajustes automáticos
8. Tarjetas de resumen (total ordinario, extraordinario, general)
9. Estados: borrador → finalizado → liquidado (con validaciones de transición)

---

## Fase 4: Motor de Liquidación (5-7 días) ⭐ Módulo más complejo

### Objetivo
Generar liquidaciones con los 3 métodos de distribución, exclusiones y personalización por concepto.

### Tareas
1. Migraciones: `liquidaciones`, `liquidacion_conceptos`, `liquidacion_detalles`
2. `LiquidacionCalculator` service con los 3 algoritmos
3. Componente Livewire: `LiquidacionManager` (UI principal)
   - Selector de presupuesto
   - Configuración global (método masivo)
   - Tabla de conceptos con método y unidades asignadas
4. Componente Livewire: `ConceptoConfig` (modal de configuración por concepto)
   - Selección de método
   - Checkboxes de inclusión/exclusión de unidades
   - Input de porcentaje manual por unidad
   - Preview de distribución en tiempo real
5. Vista previa de liquidación completa
6. Generación de liquidación (snapshot de datos)
7. Validaciones pre-generación
8. Vista de liquidaciones históricas

### Algoritmos a implementar
Ver documento `04-reglas-de-negocio.md` §3.1

---

## Fase 5: Gastos y Facturas (3-4 días)

> ⚠️ Requiere más definición del cliente. Implementación básica.

### Tareas (estimadas)
1. Migraciones: `proveedores`, `gastos`
2. ABM proveedores básico
3. Registro de gastos con upload de archivos
4. Vinculación gasto → concepto del presupuesto
5. Flujo de pago (marcar como pagado + comprobante)
6. Integración con presupuestos (actualizar estimados)

---

## Fase 6: Portal de Autogestión y Cobros (4-5 días)

### Objetivo
Plataforma para propietarios/inquilinos y generación de cupones SIRO.

### Tareas
1. Roles de usuario para propietarios e inquilinos
2. Dashboard de inquilino (mis expensas, historial de pagos)
3. Generación de cupones de pago (integración formato SIRO)
4. Lógica de separación de deuda: un cupón independiente por cada mes adeudado
5. Sección de información útil: reglamento, números de emergencia, contacto encargado

---

## Fase 7: Reportes y Exportación (2-3 días)

### Tareas (estimadas)
1. Resumen de liquidación del mes (PDF)
2. Listado de gastos por período (Excel)
3. Estado de cuenta histórico de la unidad

---

## Fase 8: Deploy y Polishing (2-3 días)

### Tareas
1. Deploy a Hostinger
2. Configurar dominio/subdominio
3. SSL/HTTPS
4. Optimización de performance
5. Testing final con datos reales
6. Capacitación al cliente

---

## Timeline Estimado

| Fase | Duración | Dependencias |
|---|---|---|
| Fase 0: Setup | 1-2 días | — |
| Fase 1: Consorcios | 2-3 días | Fase 0 |
| Fase 2: Unidades | 3-4 días | Fase 1 |
| Fase 3: Presupuestos | 4-5 días | Fase 1 |
| Fase 4: Liquidación | 5-7 días | Fase 2 + Fase 3 |
| Fase 5: Gastos | 3-4 días | Fase 3 |
| Fase 6: Portal Autogestión | 4-5 días | Fase 4 |
| Fase 7: Reportes | 2-3 días | Fase 4 |
| Fase 8: Deploy | 2-3 días | Todas |
| **TOTAL** | **~26-36 días** | |

> Las Fases 1-2 y 3 pueden desarrollarse en paralelo parcialmente.
