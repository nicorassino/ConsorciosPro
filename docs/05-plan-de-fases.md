# ConsorciosPro — Plan de Implementación por Fases

**Versión:** 1.2  
**Fecha:** 2026-05-04  
**Notas de cambio:** Fase 5 ampliada con SRS v1.5: campos de ciclo de vida de archivos, nomenclatura automática, filtro por período en pantalla de carga, preview de ajustes, descarga masiva con alerta de vencimiento anual.

---

## Resumen

Este documento define las fases de desarrollo del sistema ConsorciosPro. Cada fase es un entregable funcional que se puede demostrar al cliente. La especificación detallada está en `docs/01-requisitos-del-sistema.md`.

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
1. Migración: tabla `consorcios` según SRS §2.1
2. Modelo: `Consorcio.php` con validaciones (CUIT, CBU 22 dígitos si informado, etc.)
3. Seeder con datos de ejemplo
4. Componente Livewire: `ConsorcioList` (tabla con búsqueda, paginación)
5. Componente Livewire: `ConsorcioForm` (modal de creación/edición)
6. Vista detalle de consorcio
7. Soft delete con confirmación

### Campos del formulario
Todos los campos definidos en el SRS §2.1, organizados en secciones:
- Datos generales (nombre, dirección, CUIT)
- Datos bancarios (banco, cuenta, CBU, convenio; texto medios de pago)
- Datos catastrales y matrícula reglamento / Aguas Cordobesas
- **`tiene_cocheras`** (habilita cocheras en UF)
- Encargado (nombre, apellido, teléfono amplio, horarios, días, empresa)
- Administración (nombre, logo), vencimientos nominales y recargo nominal
- Nota (texto largo para cuerpo administrativo / información fija)

---

## Fase 2: ABM Unidades Funcionales (3-4 días)

### Objetivo
CRUD de departamentos/PH dentro de cada consorcio, con datos de propietario, inquilino e inmobiliaria.

### Tareas
1. Migraciones: `unidades`, `propietarios`, `inquilinos`, `inmobiliarias`, `contactos_alternativos`
2. Modelos con relaciones Eloquent
3. Seeders
4. Componente Livewire: `UnidadList` (filtrable por consorcio)
5. Componente Livewire: `UnidadForm` (formulario multipanel con secciones colapsables)
6. Validación de que la suma de coeficientes por consorcio ≈ 100%
7. Indicador visual del total de coeficientes
8. Campos **cochera** solo si el consorcio tiene `tiene_cocheras`

### Formulario organizado en secciones
- Datos de la unidad (incl. coeficientes y contactos alternativos)
- Datos del propietario
- Datos del inquilino (colapsable, opcional)
- Datos de inmobiliaria/encargado (colapsable, opcional)
- Configuración fiscal (IVA, recibos, emails ordinarios/extraordinarios amplios)

---

## Fase 3: Presupuestos Mensuales (4-5 días)

### Objetivo
Crear, clonar y gestionar presupuestos mensuales con conceptos.

### Tareas
1. Migraciones: `presupuestos`, `concepto_presupuestos` (incl. **`dia_*_vencimiento_aplicado`**, **`recargo_mensual_aplicado`**, **`aplica_cocheras`**)
2. Modelos con relaciones
3. `PresupuestoService`: lógica de clonación y ajustes; copia inicial de valores reales desde consorcio
4. Componente Livewire: `PresupuestoManager` (UI principal)
5. Componente Livewire: `ConceptoForm` (agregar/editar concepto inline o modal)
6. Lógica de cuotas (incremento automático, concepto expirado)
7. Lógica de estimados → ajustes automáticos al mes siguiente
8. Tarjetas de resumen (total ordinario, extraordinario, general)
9. Estados: borrador → finalizado → liquidado (con validaciones de transición)

---

## Fase 4: Motor de Liquidación (5-7 días) — Módulo central

### Objetivo
Generar liquidaciones con los 3 métodos de distribución, exclusiones, conjuntos de coeficientes y **filtro cocheras**.

### Tareas
1. Migraciones: `liquidaciones`, `liquidacion_conceptos`, `liquidacion_detalles`, conjuntos de coeficientes ya modelados
2. `LiquidacionCalculator` con los 3 algoritmos y reprorrateo
3. Componente Livewire: `LiquidacionManager` (UI principal)
   - Selector de presupuesto
   - Configuración global (método masivo)
   - **Acción “filtro cocheras”:** excluir UF sin cochera, sin coeficiente en ese flujo, partes iguales por defecto, manual permitido (SRS §2.4)
4. Componente Livewire: `ConceptoConfig` (modal por concepto)
5. Vista previa y generación con **snapshots**
6. Validaciones pre-generación
7. Histórico de liquidaciones

### Algoritmos
Ver `docs/04-reglas-de-negocio.md` §3 y §3.4.

---

## Fase 5: Gastos y Facturas (6-8 días)

### Objetivo
Pantalla independiente de comprobantes con búsqueda por período, vinculación a conceptos, ajustes automáticos, ciclo de vida de archivos y descarga masiva.

### Tareas

#### 5.1 Base de datos y modelos
1. Migraciones: `proveedores`, `gastos` (incl. `factura_nombre_sistema`, `archivo_disponible_online`, `fecha_archivado_local`), `gasto_concepto_presupuesto`
2. Modelos con relaciones Eloquent y casts correspondientes
3. `GastoService`: servicio central con toda la lógica de negocio

#### 5.2 Pantalla de carga (SRS §2.6.1)
4. ABM proveedores con alta rápida inline
5. Registro de gastos con upload de archivos (PDF/imagen, max 5 MB)
6. **Filtro por período:** al seleccionar consorcio + mes/año, mostrar solo los conceptos de ese presupuesto con badge Estimado/Confirmado
7. **Reparto multi-concepto:** una factura → N líneas de imputación; validación de cuadre con el total del comprobante
8. **Nomenclatura automática:** al subir archivo, renombrarlo con patrón `[concepto-slug]_[AAAA-MM]_[consorcio-slug].[ext]` y guardar en `factura_nombre_sistema` (SRS §2.6.4)

#### 5.3 Ajustes y presupuesto (SRS §2.6.2)
9. Integración con presupuesto: actualizar `monto_factura_real` al imputar factura real
10. Generación automática de concepto "Ajuste [nombre]" en el presupuesto del mes siguiente
11. **Preview de ajuste:** mostrar la tabla estimado vs. real vs. diferencia en el editor ANTES de confirmar el guardado

#### 5.4 Flujo de pago (SRS §4.4–4.5)
12. Marcar gasto como pagado: registrar `fecha_pago` + comprobante adjunto
13. Diferenciación devengado (pendiente) / percibido (pagado) para informes y conciliación

#### 5.5 Ciclo de vida de archivos (SRS §4.6)
14. **Alerta de vencimiento:** banner en el listado cuando hay archivos con 11–12 meses de antigüedad
15. **Descarga masiva:** botón "Descargar todas" que genera un ZIP con todos los archivos próximos a vencer, usando los nombres de sistema para fácil identificación
16. **Archivado local:** acción por gasto (o en lote) para marcar `archivo_disponible_online = false` + `fecha_archivado_local = hoy`; el path del archivo se limpia pero el registro histórico permanece
17. Filtro en listado por estado de archivo (online / archivado / sin archivo)

### Campos clave
Ver SRS §2.6 y Modelo de Datos §GASTOS para la lista completa de campos.

---

## Fase 6: Portal de Autogestión, SIRO y PDF (6-8 días)

### Objetivo
Portal para propietarios/inquilinos; cupones coherentes con SRS §2.5; envíos por correo.

### Tareas
1. Roles/guards para portal vs administración
2. Área portal: cupones por período, historial según datos disponibles, reglamento, emergencias, contacto encargado
3. Generación de datos/formato SIRO (códigos de barras/QR); **recargo diario** y dos montos/plazas en cupón
4. Regla de **un cupón por cada mes adeudado** (sin agrupar deuda en cupón actual)
5. Ocultar **CBU** en el cupón impreso
6. Composición PDF: cupón + balance/informe económico editable + cuerpo desde nota del consorcio; workflow de aprobación previo al envío
7. Colas/jobs si el volumen de envíos lo requiere

---

## Fase 7: Informes y Conciliación (5-8 días)

> Depende de datos de cobranzas y movimientos; se puede iterar por entregables.

### Tareas orientativas (SRS §2.7)
1. Informe económico / balance mensual con detalle de egresos
2. Flujo de caja y bancos (saldos iniciales/finales, ingresos/egresos)
3. Discriminación de ingresos por período de expensa vs intereses/recargos
4. Reportes de deudores y deuda a proveedores
5. Estadísticas de gestión
6. Grilla/reporte de conciliación fondos vs obligaciones

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

## Timeline estimado

| Fase | Duración | Dependencias |
|------|----------|--------------|
| Fase 0: Setup | 1-2 días | — |
| Fase 1: Consorcios | 2-3 días | Fase 0 |
| Fase 2: Unidades | 3-4 días | Fase 1 |
| Fase 3: Presupuestos | 4-5 días | Fase 1 |
| Fase 4: Liquidación | 5-7 días | Fase 2 + Fase 3 |
| Fase 5: Gastos | 4-5 días | Fase 3 |
| Fase 6: Portal / SIRO / PDF | 6-8 días | Fase 4 + Fase 5 (parcial) |
| Fase 7: Informes | 5-8 días | Fase 5–6 según alcance |
| Fase 8: Deploy | 2-3 días | Todas |
| **TOTAL (orden de magnitud)** | **~33-46 días** | |

Las fases 1–3 pueden solaparse parcialmente; la fase 7 puede dividirse en hitos cuando existan cobranzas registradas.
