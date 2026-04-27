<?php

namespace App\Livewire\Consorcios;

use App\Enums\CondicionIvaConsorcio;
use App\Models\Consorcio;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class ConsorcioIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public bool $showModal = false;

    public ?int $editingId = null;

    public string $nombre = '';

    public string $direccion = '';

    public string $cuit = '';

    public string $banco = '';

    public string $nro_cuenta_bancaria = '';

    public string $convenio = '';

    public string $sucursal = '';

    public string $digito_verificador = '';

    public string $cbu = '';

    public string $condicion_iva = '';

    public string $nro_cuenta_rentas = '';

    public string $nomenclatura_catastral = '';

    public string $nro_matricula = '';

    public ?string $fecha_inscripcion_reglamento = null;

    public string $unidad_facturacion_aguas = '';

    public bool $tiene_cocheras = false;

    public string $encargado_nombre = '';

    public string $encargado_apellido = '';

    public string $encargado_telefono = '';

    public string $encargado_horarios = '';

    public string $encargado_dias = '';

    public string $encargado_empresa_servicio = '';

    public string $nombre_administracion = '';

    public string $logo_administracion = '';

    public string $texto_medios_pago = '';

    public ?int $dia_primer_vencimiento = null;

    public ?int $dia_segundo_vencimiento = null;

    public ?string $recargo_segundo_vto = null;

    public string $nota = '';

    public bool $activo = true;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->applyPrototypeDefaults();
        $this->showModal = true;
    }

    public function openEditModal(int $id): void
    {
        $consorcio = Consorcio::query()->findOrFail($id);
        $this->editingId = $consorcio->id;
        $this->nombre = $consorcio->nombre;
        $this->direccion = $consorcio->direccion;
        $this->cuit = $consorcio->cuit;
        $this->banco = $consorcio->banco ?? '';
        $this->nro_cuenta_bancaria = $consorcio->nro_cuenta_bancaria ?? '';
        $this->convenio = $consorcio->convenio ?? '';
        $this->sucursal = $consorcio->sucursal ?? '';
        $this->digito_verificador = $consorcio->digito_verificador ?? '';
        $this->cbu = $consorcio->cbu ?? '';
        $this->condicion_iva = $consorcio->condicion_iva->value;
        $this->nro_cuenta_rentas = $consorcio->nro_cuenta_rentas ?? '';
        $this->nomenclatura_catastral = $consorcio->nomenclatura_catastral ?? '';
        $this->nro_matricula = $consorcio->nro_matricula ?? '';
        $this->fecha_inscripcion_reglamento = $consorcio->fecha_inscripcion_reglamento?->format('Y-m-d');
        $this->unidad_facturacion_aguas = $consorcio->unidad_facturacion_aguas ?? '';
        $this->tiene_cocheras = $consorcio->tiene_cocheras;
        $this->encargado_nombre = $consorcio->encargado_nombre ?? '';
        $this->encargado_apellido = $consorcio->encargado_apellido ?? '';
        $this->encargado_telefono = $consorcio->encargado_telefono ?? '';
        $this->encargado_horarios = $consorcio->encargado_horarios ?? '';
        $this->encargado_dias = $consorcio->encargado_dias ?? '';
        $this->encargado_empresa_servicio = $consorcio->encargado_empresa_servicio ?? '';
        $this->nombre_administracion = $consorcio->nombre_administracion ?? '';
        $this->logo_administracion = $consorcio->logo_administracion ?? '';
        $this->texto_medios_pago = $consorcio->texto_medios_pago ?? '';
        $this->dia_primer_vencimiento = $consorcio->dia_primer_vencimiento;
        $this->dia_segundo_vencimiento = $consorcio->dia_segundo_vencimiento;
        $this->recargo_segundo_vto = $consorcio->recargo_segundo_vto !== null ? (string) $consorcio->recargo_segundo_vto : null;
        $this->nota = $consorcio->nota ?? '';
        $this->activo = $consorcio->activo;
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function save(): void
    {
        $rules = [
            'nombre' => ['required', 'string', 'max:191'],
            'direccion' => ['required', 'string', 'max:191'],
            'cuit' => ['required', 'string', 'max:20', Rule::unique('consorcios', 'cuit')->ignore($this->editingId)],
            'banco' => ['nullable', 'string', 'max:191'],
            'nro_cuenta_bancaria' => ['nullable', 'string', 'max:191'],
            'convenio' => ['nullable', 'string', 'max:191'],
            'sucursal' => ['nullable', 'string', 'max:191'],
            'digito_verificador' => ['nullable', 'string', 'max:191'],
            'cbu' => ['nullable', 'string', 'max:22'],
            'condicion_iva' => ['required', Rule::enum(CondicionIvaConsorcio::class)],
            'nro_cuenta_rentas' => ['nullable', 'string', 'max:191'],
            'nomenclatura_catastral' => ['nullable', 'string', 'max:191'],
            'nro_matricula' => ['nullable', 'string', 'max:191'],
            'fecha_inscripcion_reglamento' => ['nullable', 'date'],
            'unidad_facturacion_aguas' => ['nullable', 'string', 'max:191'],
            'encargado_nombre' => ['nullable', 'string', 'max:191'],
            'encargado_apellido' => ['nullable', 'string', 'max:191'],
            'encargado_telefono' => ['nullable', 'string', 'max:191'],
            'encargado_horarios' => ['nullable', 'string', 'max:65535'],
            'encargado_dias' => ['nullable', 'string', 'max:65535'],
            'encargado_empresa_servicio' => ['nullable', 'string', 'max:191'],
            'nombre_administracion' => ['nullable', 'string', 'max:191'],
            'logo_administracion' => ['nullable', 'string', 'max:191'],
            'texto_medios_pago' => ['nullable', 'string', 'max:65535'],
            'dia_primer_vencimiento' => ['nullable', 'integer', 'min:1', 'max:28'],
            'dia_segundo_vencimiento' => ['nullable', 'integer', 'min:1', 'max:28'],
            'recargo_segundo_vto' => ['nullable', 'numeric', 'min:0', 'max:999.99'],
            'nota' => ['nullable', 'string', 'max:65535'],
        ];

        $this->validate($rules);

        $data = [
            'nombre' => $this->nombre,
            'direccion' => $this->direccion,
            'cuit' => $this->cuit,
            'banco' => $this->banco ?: null,
            'nro_cuenta_bancaria' => $this->nro_cuenta_bancaria ?: null,
            'convenio' => $this->convenio ?: null,
            'sucursal' => $this->sucursal ?: null,
            'digito_verificador' => $this->digito_verificador ?: null,
            'cbu' => $this->cbu ?: null,
            'condicion_iva' => $this->condicion_iva,
            'nro_cuenta_rentas' => $this->nro_cuenta_rentas ?: null,
            'nomenclatura_catastral' => $this->nomenclatura_catastral ?: null,
            'nro_matricula' => $this->nro_matricula ?: null,
            'fecha_inscripcion_reglamento' => $this->fecha_inscripcion_reglamento ?: null,
            'unidad_facturacion_aguas' => $this->unidad_facturacion_aguas ?: null,
            'tiene_cocheras' => $this->tiene_cocheras,
            'encargado_nombre' => $this->encargado_nombre ?: null,
            'encargado_apellido' => $this->encargado_apellido ?: null,
            'encargado_telefono' => $this->encargado_telefono ?: null,
            'encargado_horarios' => $this->encargado_horarios ?: null,
            'encargado_dias' => $this->encargado_dias ?: null,
            'encargado_empresa_servicio' => $this->encargado_empresa_servicio ?: null,
            'nombre_administracion' => $this->nombre_administracion ?: null,
            'logo_administracion' => $this->logo_administracion ?: null,
            'texto_medios_pago' => $this->texto_medios_pago ?: null,
            'dia_primer_vencimiento' => $this->dia_primer_vencimiento,
            'dia_segundo_vencimiento' => $this->dia_segundo_vencimiento,
            'recargo_segundo_vto' => $this->recargo_segundo_vto !== null && $this->recargo_segundo_vto !== '' ? $this->recargo_segundo_vto : null,
            'nota' => $this->nota ?: null,
            'activo' => $this->activo,
        ];

        if ($this->editingId) {
            Consorcio::query()->findOrFail($this->editingId)->update($data);
            session()->flash('status', 'Consorcio actualizado correctamente.');
        } else {
            Consorcio::query()->create($data);
            $this->resetPage();
            $this->search = '';
            session()->flash('status', 'Consorcio creado correctamente.');
        }

        $this->closeModal();
        $this->dispatch('consorcio-saved');
    }

    public function delete(int $id): void
    {
        Consorcio::query()->findOrFail($id)->delete();
        $this->resetPage();
    }

    public function render()
    {
        $consorcios = Consorcio::query()
            ->withCount('unidades')
            ->when($this->search !== '', function ($q) {
                $term = '%'.$this->search.'%';
                $q->where(function ($q2) use ($term) {
                    $q2->where('nombre', 'like', $term)
                        ->orWhere('cuit', 'like', $term)
                        ->orWhere('direccion', 'like', $term);
                });
            })
            ->orderBy('nombre')
            ->paginate(10);

        return view('livewire.consorcios.consorcio-index', [
            'consorcios' => $consorcios,
        ])->layout('layouts.app', ['active' => 'consorcios']);
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->nombre = '';
        $this->direccion = '';
        $this->cuit = '';
        $this->banco = '';
        $this->nro_cuenta_bancaria = '';
        $this->convenio = '';
        $this->sucursal = '';
        $this->digito_verificador = '';
        $this->cbu = '';
        $this->condicion_iva = CondicionIvaConsorcio::NoAlcanzado->value;
        $this->nro_cuenta_rentas = '';
        $this->nomenclatura_catastral = '';
        $this->nro_matricula = '';
        $this->fecha_inscripcion_reglamento = null;
        $this->unidad_facturacion_aguas = '';
        $this->tiene_cocheras = false;
        $this->encargado_nombre = '';
        $this->encargado_apellido = '';
        $this->encargado_telefono = '';
        $this->encargado_horarios = '';
        $this->encargado_dias = '';
        $this->encargado_empresa_servicio = '';
        $this->nombre_administracion = '';
        $this->logo_administracion = '';
        $this->texto_medios_pago = '';
        $this->dia_primer_vencimiento = null;
        $this->dia_segundo_vencimiento = null;
        $this->recargo_segundo_vto = null;
        $this->nota = '';
        $this->activo = true;
        $this->resetValidation();
    }

    private function applyPrototypeDefaults(): void
    {
        $this->banco = 'ROELA S.A.';
        $this->nombre_administracion = 'Administracion OLIVA';
        $this->recargo_segundo_vto = '1.80';
        $this->dia_primer_vencimiento = 10;
        $this->dia_segundo_vencimiento = 28;
        $this->texto_medios_pago = 'Rapipago, PagoFacil, Cobro Express, Pago Mis Cuentas, Banelco, Red Link, MODO y Mercado Pago.';
        $this->activo = true;
        $this->condicion_iva = CondicionIvaConsorcio::NoAlcanzado->value;
    }
}
