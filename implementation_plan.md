# Adaptación del Módulo de Gastos/Facturas a la Documentación Actualizada

Comparación exhaustiva entre el código actual y la documentación (SRS v1.5 · Modelo de Datos v1.4 · Reglas de Negocio v1.3).  
**Plan revisado:** se elimina G2 (observaciones ya cubiertas por `descripcion`/`notas`), se mantiene `date` para `periodo` y se detalla la descarga masiva con alerta de vencimiento.

---

## Análisis de Brechas (Código actual vs Documentación)

### ✅ Lo que YA cumple

| Requisito (Doc) | Implementación actual |
|---|---|
| Pantalla independiente de carga | `GastoEditor` con ruta propia |
| Distribución en múltiples conceptos | `lineItems[]` con pivot `gasto_concepto_presupuesto` |
| Validación de cuadre de importes | `assertLinesMatchTotal()` en `GastoService` |
| Estado pendiente / pagado (devengado / percibido) | Enum `EstadoGasto` con 2 valores |
| Flujo de pago (fecha + comprobante) | `GastoEditor.save()` |
| Upload de archivos | `WithFileUploads` en `GastoEditor` |
| Ajuste automático al mes siguiente | `syncAjustesDesdeFacturas()` en `GastoService` |
| Alta rápida de proveedor | `createProveedorQuick()` en `GastoEditor` |
| Filtros en listado (consorcio/estado/período/búsqueda) | `GastoIndex` con 4 filtros |
| Visualización de ajuste generado en listado | Columna "Ajuste generado" en `gasto-index.blade.php` |
| Observaciones en comprobantes | ✅ Ya existe `notas` (general) y cada concepto tiene `descripcion` |

### ❌ Brechas a cerrar

| # | Requisito (Doc) | Gap | Sección |
|---|---|---|---|
| **G1** | 3 campos nuevos en `gastos`: `factura_nombre_sistema`, `archivo_disponible_online`, `fecha_archivado_local` | No existen en migración, modelo ni servicio | §2.6.4 |
| **G3** | Nomenclatura automática: patrón `[concepto-slug]_[AAAA-MM]_[consorcio-slug].ext` | `buildAttachmentFilename()` usa patrón distinto y no guarda el nombre en `factura_nombre_sistema` | §2.6.4 |
| **G4** | Búsqueda por período en pantalla de carga: seleccionar mes/año → ver solo conceptos de ese período con su estado (estimado/confirmado) | Los conceptos se listan sin filtro por período y sin mostrar estado | §2.6.1 |
| **G5** | Preview del ajuste ANTES de confirmar (en el editor) | El ajuste solo se ve en el listado post-save | §2.6.2 |
| **G6** | Campo `descripcion` de 500 chars | Migración usa `string` sin tamaño explícito (default 255) | §2.6 tabla |
| **G7** | Campo `periodo` documentado como `string(7)` | El código usa `date` (almacena `YYYY-MM-01`). **Decisión: mantener `date`** y aclarar en la documentación que se normaliza al primer día del mes | §2.6 tabla |
| **G8** | Ciclo de vida de archivos: descarga masiva + alerta de vencimiento a los 12 meses | No existe ninguna lógica de archivado local | §2.6.4 / §4.6 |
| **G9** | Actualización de `05-plan-de-fases.md` | Fase 5 describe la versión anterior | §doc |

---

## Proposed Changes

### Documentación

#### [MODIFY] [03-modelo-de-datos.md](file:///c:/wamp64/www/ConsorciosPro/docs/03-modelo-de-datos.md)

Aclarar que el campo `periodo` en `gastos` es de tipo `date` (almacenado como `YYYY-MM-01`, primer día del mes) aunque la presentación al usuario es `YYYY-MM`. Esto permite queries con comparación de fechas y operaciones con Carbon.

#### [MODIFY] [01-requisitos-del-sistema.md](file:///c:/wamp64/www/ConsorciosPro/docs/01-requisitos-del-sistema.md)

Actualizar la tabla de campos de GASTOS: cambiar `string(7)` → `date` con nota "normalizado a primer día del mes".

#### [MODIFY] [05-plan-de-fases.md](file:///c:/wamp64/www/ConsorciosPro/docs/05-plan-de-fases.md)

Reescribir las tareas de la Fase 5 para incluir todos los nuevos requisitos.

---

### Migración de Base de Datos

#### [NEW] `database/migrations/XXXX_update_gastos_module_fields.php`

```php
// En tabla gastos:
$table->string('descripcion', 500)->change();          // G6: ampliar a 500 chars
$table->string('factura_nombre_sistema', 500)->nullable()->after('factura_archivo'); // G1
$table->boolean('archivo_disponible_online')->default(true)->after('factura_nombre_sistema'); // G1
$table->date('fecha_archivado_local')->nullable()->after('archivo_disponible_online'); // G1
```

> [!NOTE]
> No se agrega campo `observaciones` a `gasto_concepto_presupuesto`: los campos `descripcion` (del comprobante general) y `notas` (texto libre del comprobante) ya cubren ese caso de uso.

> [!NOTE]
> El campo `periodo` se mantiene como `date`. La nota aclaratoria se agrega a la documentación.

---

### Modelo Gasto

#### [MODIFY] [Gasto.php](file:///c:/wamp64/www/ConsorciosPro/app/Models/Gasto.php)

- Agregar a `$fillable`: `factura_nombre_sistema`, `archivo_disponible_online`, `fecha_archivado_local`
- Agregar casts: `'archivo_disponible_online' => 'boolean'`, `'fecha_archivado_local' => 'date'`
- Agregar accesor `getArchivoProximoVencerAttribute()` → `true` si `fecha_factura` tiene más de 11 meses y el archivo aún está online

---

### GastoService

#### [MODIFY] [GastoService.php](file:///c:/wamp64/www/ConsorciosPro/app/Services/GastoService.php)

**Nuevo método `buildSystemFilename()`:**
```
Patrón: [concepto-slug]_[AAAA-MM]_[consorcio-slug].[ext]
- 1 concepto imputado → slug del nombre del concepto
- N conceptos          → "varios"
- consorcio-slug       → Str::slug($consorcio->nombre)
- AAAA-MM              → periodo del gasto
Resultado: factura_nombre_sistema se persiste en la BD
```

**Nuevo método `previewAjustes(array $lineItems, int $consorcioId): array`:**
- Calcula diferencias (monto_factura_real - monto_total) sin persistir
- Devuelve array con: concepto, estimado, real, diferencia
- Lo consume `GastoEditor` para el panel de preview

**Nuevo método `marcarArchivoLocal(Gasto $gasto): void`:**
- Setea `archivo_disponible_online = false`
- Setea `fecha_archivado_local = today()`
- Limpia el path `factura_archivo` (lo pone a null)

**Modificar `save()`:**
- Tras subir el archivo, invocar `buildSystemFilename()` y guardar en `factura_nombre_sistema`

---

### GastoEditor (Livewire)

#### [MODIFY] [GastoEditor.php](file:///c:/wamp64/www/ConsorciosPro/app/Livewire/Gastos/GastoEditor.php)

**Cambio 1 — Filtro por período en conceptos (G4):**
- Modificar `getConceptosDisponiblesProperty()`: cuando `$this->periodo` tenga valor, filtrar conceptos al presupuesto de ese período.
- Retornar también `monto_factura_real` para mostrar el estado estimado/confirmado en el `<option>`.

**Cambio 2 — Preview de ajustes antes de confirmar (G5):**
- Agregar propiedad computada `getAjustePreviewProperty()` que llame a `GastoService::previewAjustes()`.
- La propiedad se recalcula reactivamente cuando cambian `lineItems` o `periodo`.

**Cambio 3 — Persistir nombre de sistema (G3):**
- Modificar `save()` para incluir `factura_nombre_sistema` en el payload al `GastoService::save()`.

---

### GastoIndex (Livewire)

#### [MODIFY] [GastoIndex.php](file:///c:/wamp64/www/ConsorciosPro/app/Livewire/Gastos/GastoIndex.php)

**Descarga masiva y ciclo de vida (G8):**
- Nueva propiedad `$archivoFilter = ''` para filtrar por estado de archivo (`online` / `archivado` / `sin_archivo`).
- Nueva propiedad computada `getArchivosProximosVencerProperty()`: gastos con `archivo_disponible_online = true` y `fecha_factura` entre 11 y 12 meses atrás → alerta visible en la UI.
- Nueva acción `marcarArchivoLocal(int $gastoId)`: llama a `GastoService::marcarArchivoLocal()`.
- Nueva acción `descargarTodosProximosVencer()`: genera un ZIP descargable con todos los archivos próximos a vencer (usando `ZipArchive` o `League\Flysystem`), para que el administrador los archive localmente de una vez.

---

### Vistas

#### [MODIFY] [gasto-editor.blade.php](file:///c:/wamp64/www/ConsorciosPro/resources/views/livewire/gastos/gasto-editor.blade.php)

**Cambio 1 — Conceptos filtrados por período con badge de estado:**
- Cada `<option>` muestra un indicador "Estimado" o "Confirmado" según `monto_factura_real`.

**Cambio 2 — Panel de preview de ajustes:**
- Bloque visible entre el reparto y el botón "Guardar", condicionado a que existan diferencias.
- Tabla: Concepto | Estimado | Real (asignado) | Diferencia | Destino.
- Texto introductorio: "Al guardar, se generarán los siguientes ajustes en el presupuesto de [mes siguiente]."

#### [MODIFY] [gasto-index.blade.php](file:///c:/wamp64/www/ConsorciosPro/resources/views/livewire/gastos/gasto-index.blade.php)

**Cambio 1 — Alerta de vencimiento:**
- Banner informativo en la parte superior si hay archivos próximos a vencer (entre 11 y 12 meses): "Hay N facturas que vencen su período de almacenamiento online en los próximos 30 días. [Descargar todas]"

**Cambio 2 — Filtro por estado de archivo:**
- Nuevo select en los filtros: Todos / Online / Archivado / Sin archivo.

**Cambio 3 — Columna "Archivo" con estado y acción:**
- Reemplazar la columna "Factura" por una columna "Archivo" que muestre:
  - 🟢 Online: botón descargar + botón "Archivar" (marcar como local)
  - 🔴 Archivado: texto "Archivado el [fecha]" (no descargable desde la web)
  - ➖ Sin archivo

---

### Validación

#### [MODIFY] [StoreGastoRequest.php](file:///c:/wamp64/www/ConsorciosPro/app/Http/Requests/StoreGastoRequest.php)

- Cambiar `'descripcion' => ['required', 'string', 'max:191']` → `max:500`

---

## Resumen de archivos

| Archivo | Cambio |
|---|---|
| `database/migrations/XXXX_update_gastos_module_fields.php` | **[NEW]** migración |
| `app/Models/Gasto.php` | +3 fillable, +2 casts, +1 accesor |
| `app/Services/GastoService.php` | +3 métodos, modificar `save()` |
| `app/Livewire/Gastos/GastoEditor.php` | filtro período, preview ajustes, nombre sistema |
| `app/Livewire/Gastos/GastoIndex.php` | descarga masiva, alerta vencimiento, filtro archivo |
| `app/Http/Requests/StoreGastoRequest.php` | max:500 en descripcion |
| `resources/views/livewire/gastos/gasto-editor.blade.php` | badges estado, panel preview |
| `resources/views/livewire/gastos/gasto-index.blade.php` | alerta, filtro, columna archivo |
| `docs/01-requisitos-del-sistema.md` | campo `periodo` → `date` con nota |
| `docs/03-modelo-de-datos.md` | nota sobre `periodo` como `date` normalizado |
| `docs/05-plan-de-fases.md` | reescribir Fase 5 |

---

## Verification Plan

### Automated Tests
```bash
php artisan migrate          # nueva migración sin errores
php artisan test             # suite existente sigue pasando
```

### Manual Verification
1. **Crear gasto con factura** → verificar que `factura_nombre_sistema` se genera con el patrón `[concepto-slug]_[AAAA-MM]_[consorcio-slug].pdf`
2. **Seleccionar período** en el editor → verificar que los conceptos se filtran al mes elegido con badge estimado/confirmado
3. **Cargar factura con diferencia** → verificar que el panel de preview aparece antes de guardar
4. **Archivar un gasto** desde el listado → verificar que `archivo_disponible_online = false` y el archivo deja de descargarse
5. **Simular fecha_factura de hace 11 meses** → verificar que aparece el banner de alerta y el botón "Descargar todas"
6. **Descargar ZIP masivo** → verificar que contiene los archivos correctos con sus nombres de sistema
