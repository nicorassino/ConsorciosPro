# ConsorciosPro — Documento de Requisitos del Sistema (SRS)

**Cliente:** Oliva Administraciones
**Versión:** 1.3
**Fecha:** 2026-03-23
**Última actualización:** 2026-04-13 — Ajustes por devolución cliente (campos legales, cocheras, coeficientes múltiples y cupón SIRO)
**Fuente:** 2 reuniones con el cliente + análisis de prototipos

---

## 1. Visión General

Sistema web de administración de consorcios que permite gestionar edificios, unidades funcionales, presupuestos mensuales, liquidación de expensas y control de gastos/facturas. El sistema es **mono-empresa** (single-tenant) para uso exclusivo de Oliva Administraciones. Pero a futuro se intenta comercializar a otros clientes, por lo que se debe tener en cuenta que el sistema debe ser escalable y adaptable a las necesidades de otros clientes.

---

## 2. Módulos del Sistema

### 2.1 ABM Consorcios

Gestión completa (Alta, Baja, Modificación) de los consorcios administrados.

| Campo | Tipo | Requerido | Notas |
|---|---|---|---|
| Nombre del consorcio | `string(255)` | ✅ | |
| Dirección | `string(500)` | ✅ | Campo amplio |
| CUIT | `string(13)` | ✅ | Formato: XX-XXXXXXXX-X |
| Banco | `string(100)` | No | Default: "ROELA S.A" |
| Nro cuenta bancaria | `string(50)` | No | |
| Convenio | `string(50)` | No | |
| Sucursal | `string(50)` | No | |
| Dígito verificador | `string(5)` | No | |
| CBU | `string(22)` | No | Exactamente 22 dígitos |
| Condición IVA del consorcio | `enum` | No | no_alcanzado / exento / responsable_inscripto |
| Nro cuenta rentas | `string(50)` | No | |
| Nomenclatura catastral | `string(100)` | No | |
| Nro matrícula reglamento | `string(50)` | No | DNI del consorcio |
| Fecha inscripción reglamento | `date` | No | |
| Unidad facturación Aguas Cordobesas | `string(50)` | No | Para casos sin matrícula |
| Tiene cocheras | `boolean` | No | Habilita campos de cochera en UF |
| Encargado — Nombre | `string(100)` | No | |
| Encargado — Apellido | `string(100)` | No | |
| Encargado — Teléfono | `string(100)` | No | Campo amplio (requisito cliente) |
| Encargado — Horarios atención | `text` | No | |
| Encargado — Días | `text` | No | |
| Encargado — Empresa servicio | `string(255)` | No | |
| Nombre administración | `string(255)` | No | Ej: Administracion OLIVA |
| Logo administración | `string(500)` | No | Path/archivo |
| Texto medios de pago | `text` | No | Leyenda de canales habilitados |
| Día 1er vencimiento | `integer` | No | 1-28 |
| Día 2do vencimiento | `integer` | No | 1-28 |
| Recargo 2do vencimiento (%) | `decimal(5,2)` | No | Aplica sobre total del cupón |
| Nota | `text` | No | Texto largo |

### 2.2 ABM Unidades Funcionales (Departamentos/PH)

Cada unidad pertenece a un consorcio. La tabla de datos es extensa y los campos deben tener **capacidad amplia** para datos de contacto (requisito explícito del cliente).

**Datos de la Unidad:**

| Campo | Tipo | Requerido | Notas |
|---|---|---|---|
| Número de unidad | `string(20)` | ✅ | Ej: "3B", "PH A", "Local 5" |
| Número PH | `string(20)` | No | |
| Coeficiente de copropiedad | `decimal(8,6)` | ✅ | Porcentaje (ej: 4.523100) |
| Nomenclatura catastral | `string(100)` | No | |
| Nro cuenta rentas | `string(50)` | No | |
| Tiene cochera | `boolean` | No | |
| Nro cochera | `string(20)` | No | Opcional, si aplica |
| Estado ocupación | `enum` | No | propietario_residente / inquilino / desocupado |
| Nro cupón SIRO | `string(20)` | No | Identificador por unidad |
| Código pago electrónico | `string(50)` | No | Código SIRO |
| Recibos a nombre de | `enum` | ✅ | propietario / inmobiliaria / dueño |
| Condición IVA | `enum` | ✅ | consumidor_final / responsable_inscripto / exento |
| Email expensas ordinarias | `string(500)` | No | Campo amplio, puede tener múltiples |
| Email expensas extraordinarias | `string(500)` | No | Campo amplio, puede tener múltiples |

**Datos del Propietario:**

| Campo | Tipo | Requerido |
|---|---|---|
| Nombre | `string(200)` | ✅ |
| DNI | `string(20)` | No |
| Dirección postal | `string(500)` | No |
| Email | `string(500)` | No |
| Teléfono | `string(200)` | No |

**Datos del Inquilino (opcional):**

| Campo | Tipo | Requerido |
|---|---|---|
| Nombre | `string(200)` | No |
| Apellido | `string(200)` | No |
| Teléfono | `string(200)` | No |
| Email | `string(500)` | No |
| Dirección postal | `string(500)` | No |
| Fecha fin contrato | `date` | No |

**Contactos alternativos (múltiples) para propietario e inquilino:**

| Campo | Tipo | Requerido |
|---|---|---|
| Nombre | `string(200)` | ✅ |
| Teléfono | `string(200)` | No |
| Email | `string(500)` | No |
| Tipo relación | `enum` | ✅ propietario / inquilino |

**Inmobiliaria/Encargado (datos de contacto):**

| Campo | Tipo | Requerido |
|---|---|---|
| Nombre | `string(200)` | No |
| Apellido | `string(200)` | No |
| Teléfono | `string(200)` | No |
| Email | `string(500)` | No |
| Dirección | `string(500)` | No |

### 2.3 Presupuestos Mensuales

El presupuesto es el corazón del flujo financiero. Define los gastos del mes para un consorcio.

**Reglas de negocio:**
1. Se genera un presupuesto por consorcio por mes
2. El primer presupuesto se basa en los gastos conocidos/pendientes
3. Los meses siguientes parten del presupuesto anterior (clonar y modificar)
4. **TODOS los conceptos son estimados por defecto** hasta que se les asigna la factura real. No existe concepto "no estimado" en la carga inicial.
5. Cuando llega la factura real, se asigna al concepto. Si hay diferencia de monto, se genera automáticamente un concepto "Ajuste [nombre]" en el presupuesto del mes siguiente

**Campos del Presupuesto:**

| Campo | Tipo | Notas |
|---|---|---|
| Consorcio | `FK` | Relación con consorcio |
| Mes/Año | `date` | Período del presupuesto |
| Estado | `enum` | borrador / finalizado / liquidado |

**Campos de cada Concepto del Presupuesto:**

| Campo | Tipo | Notas |
|---|---|---|
| Nombre | `string(255)` | Ej: "Luz", "Ajuste factura agua" |
| Rubro | `enum` | servicios / mantenimiento / sueldos / impuestos / seguros / otros |
| Descripción | `text` | Opcional |
| Monto total | `decimal(12,2)` | |
| Cuotas | `integer` | 1 = pago único, >1 = cuotas mensuales |
| Cuota actual | `integer` | Nro de cuota actual (ej: 2 de 3) |
| Tipo | `enum` | ordinario / extraordinario |
| Aplica cocheras | `boolean` | No. Si es true, sugiere conjunto coeficiente "Cocheras" |
| Monto factura real | `decimal(12,2)` | NULL = aún es estimado. Cuando se asigna la factura, se carga el monto real |
| Gasto vinculado | `FK nullable` | Referencia al gasto/factura asociado |

### 2.4 Liquidaciones

La liquidación toma un presupuesto finalizado y calcula cuánto debe pagar cada unidad. Es el proceso más complejo del sistema.

**Métodos de distribución (por concepto):**

| Método | Descripción |
|---|---|
| **Por Coeficiente** | Según el % de copropiedad de cada unidad. Si se excluyen unidades, se **reprorratea** el coeficiente al 100% entre las que sí participan |
| **Partes Iguales** | Monto ÷ cantidad de unidades participantes |
| **Manual** | Se define un % personalizado para cada unidad en ese concepto |

**Reglas de negocio:**
1. Cada concepto define su método de distribución **independientemente**
2. Se pueden aplicar configuraciones globales masivas ("todos por coeficiente") como punto de partida
3. Luego se ajusta concepto por concepto
4. Para cada concepto se puede **excluir unidades** específicas (ej: PB no paga ascensor)
5. Si se excluye una unidad de un concepto por coeficiente, los coeficientes del resto se re-calculan para sumar 100%
6. Los conceptos **ordinarios** generan recibos para inquilinos
7. Los conceptos **extraordinarios** generan recibos para propietarios
8. Se pueden crear **múltiples conjuntos de coeficientes** por consorcio (ej: Reglamento, Sin Locales, Cocheras)
9. Cada concepto distribuido por coeficiente debe permitir elegir el conjunto de coeficientes a usar
10. El conjunto elegido se guarda como snapshot en la liquidación para trazabilidad

### 2.5 Pagos y Recaudación (Plataforma SIRO)

1. **Plataforma de Cobro:** El sistema se integrará o generará información para la plataforma **SIRO** para el cobro de expensas (generación de códigos de barra / botones de pago).
2. **Portal de Autogestión:** Se desarrollará un portal para inquilinos y propietarios donde podrán:
   - Descargar sus cupones de pago de expensas mensuales
   - Ver el historial de pagos realizados
   - Consultar el reglamento interno del consorcio
   - Ver números de emergencia
   - Ver datos de contacto del encargado del edificio
3. **Manejo de Deuda:** Las deudas de meses anteriores **NO se agrupan** en el cupón del mes corriente. Se debe generar un cupón independiente por cada mes adeudado.
4. **Estructura del Cupón:** Debe incluir logo/administración, datos del consorcio (nombre, dirección, CUIT, condición IVA), datos bancarios (CBU, cuenta recaudadora), número SIRO, departamento, período, código de pago electrónico, código de barras y leyenda de medios de pago.
5. **Vencimientos:** Debe mostrar 1er y 2do vencimiento con montos diferenciados, aplicando recargo configurable en 2do vencimiento.
6. **Sin desglose de conceptos:** El cupón no detalla conceptos presupuestados.
7. **Rendición separada:** Los gastos efectivamente pagados se muestran en una vista específica de rendición, separada del cupón.

### 2.6 Gastos y Facturas (definición parcial — pendiente de más detalle)

Flujo actual del cliente (manual):
1. Facturas llegan por correo → se descargan
2. Se anotan en Excel: proveedor, importe, periodo
3. Al pagar, se guarda comprobante de transferencia
4. Factura y pago reciben mismo Nro de Orden
5. Se marcan como pagadas ("R") en carpeta GASTOS
6. Antes de liquidar: actualizar presupuestos con facturas reales

> ⚠️ **REQUIERE MÁS DETALLE**: Es necesaria una tercera reunión para definir completamente este módulo. El sistema debe digitalizar este flujo.

### 2.6 ~~ABM Conceptos~~ — ELIMINADO

Los conceptos se crean directamente dentro del presupuesto. No existe catálogo global de conceptos.

---

## 3. Requisitos No Funcionales

| Requisito | Detalle |
|---|---|
| **Hosting** | Cloud hosting Hostinger |
| **Servidor** | Apache |
| **PHP** | 8.2 |
| **Base de datos** | MySQL 8.0 |
| **Responsive** | Sí, mobile-friendly. Prioridad: desktop |
| **Diseño** | Profesional y moderno. Tarjetas tipo dashboard. Limpio y ordenado |
| **Capacidad de campos** | Campos de contacto amplios (requisito explícito) |
| **Seguridad** | Autenticación con login/password (definir roles en reunión futura) |

---

## 4. Preguntas Pendientes (Fase 2)

1. **Gastos/Facturas:** ¿Qué información exacta necesitan registrar? ¿Se suben PDFs de facturas a la plataforma?
2. **Proveedores:** ¿Se necesita un ABM de proveedores? ¿Qué datos se guardan?
3. **Usuarios y Roles:** ¿Quién usa el sistema administrativamente? ¿Hay distintos niveles de acceso (ej: solo lectura)?
4. **Notificaciones:** ¿Se envían emails automáticos con avisos de expensas, o el usuario solo entra al portal de autogestión?
