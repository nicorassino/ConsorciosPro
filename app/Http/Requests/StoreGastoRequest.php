<?php

namespace App\Http\Requests;

use App\Enums\EstadoGasto;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGastoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'consorcio_id' => ['required', 'integer', 'exists:consorcios,id'],
            'proveedor_id' => ['nullable', 'integer', 'exists:proveedores,id'],
            'nro_orden' => ['required', 'string', 'max:191'],
            'descripcion' => ['required', 'string', 'max:500'],
            'importe' => ['required', 'numeric', 'min:0.01'],
            'fecha_factura' => ['required', 'date'],
            'periodo' => ['required', 'date_format:Y-m'],
            'estado' => ['required', Rule::in(array_column(EstadoGasto::cases(), 'value'))],
            'fecha_pago' => ['nullable', 'date'],
            'notas' => ['nullable', 'string', 'max:65535'],
            'ajuste_destino' => ['required', Rule::in(['siguiente_creacion', 'ultimo_pendiente'])],
            // En Livewire, "present" puede fallar cuando el array llega diferido o reindexado.
            // Con required + array + min:1 validamos intención funcional sin falsos negativos.
            'lineItems' => ['required', 'array', 'min:1'],
            'lineItems.*.concepto_presupuesto_id' => ['required', 'integer', 'exists:concepto_presupuestos,id'],
            'lineItems.*.importe_asignado' => ['required', 'numeric', 'min:0'],
            'factura_archivo' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'comprobante_pago' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'required' => 'El campo :attribute es obligatorio.',
            'integer' => 'El campo :attribute debe ser un número entero.',
            'numeric' => 'El campo :attribute debe ser numérico.',
            'min' => 'El campo :attribute debe tener al menos :min.',
            'exists' => 'El :attribute seleccionado no existe.',
            'date' => 'El campo :attribute debe ser una fecha válida.',
            'date_format' => 'El campo :attribute debe tener el formato :format.',
            'string' => 'El campo :attribute debe ser un texto.',
            'max' => 'El campo :attribute no puede superar :max caracteres.',
            'array' => 'El campo :attribute debe ser una lista.',
            'file' => 'El campo :attribute debe ser un archivo.',
            'mimes' => 'El archivo de :attribute debe ser de tipo: :values.',
            'email' => 'El campo :attribute debe ser un correo electrónico válido.',
            'in' => 'El valor seleccionado para :attribute no es válido.',
            'lineItems.required' => 'Debe cargar al menos una línea de imputación.',
            'lineItems.array' => 'Las líneas de imputación deben enviarse como una lista.',
            'lineItems.min' => 'Debe cargar al menos una línea de imputación con concepto e importe (no dejes la línea en blanco ni solo con $0 si el total del comprobante es mayor).',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'consorcio_id' => 'consorcio',
            'proveedor_id' => 'proveedor',
            'nro_orden' => 'número de orden',
            'descripcion' => 'descripción',
            'importe' => 'importe',
            'fecha_factura' => 'fecha de factura',
            'periodo' => 'período',
            'estado' => 'estado',
            'fecha_pago' => 'fecha de pago',
            'notas' => 'notas',
            'lineItems' => 'líneas de imputación',
            'lineItems.*.concepto_presupuesto_id' => 'concepto',
            'lineItems.*.importe_asignado' => 'importe asignado',
            'ajuste_destino' => 'destino del ajuste',
            'factura_archivo' => 'archivo de factura',
            'comprobante_pago' => 'comprobante de pago',
        ];
    }
}
