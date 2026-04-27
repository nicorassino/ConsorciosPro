<?php

namespace App\Livewire\Unidades;

use App\Enums\CondicionIvaUnidad;
use App\Enums\EstadoOcupacionUnidad;
use App\Enums\ReciboNombreUnidad;
use App\Models\Consorcio;
use App\Models\Unidad;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class UnidadIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $consorcioFilter = '';
    public bool $showModal = false;
    public ?int $editingId = null;

    public ?int $consorcio_id = null;
    public string $numero = '';
    public string $nro_ph = '';
    public ?string $coeficiente = null;
    public string $nomenclatura_catastral = '';
    public string $nro_cuenta_rentas = '';
    public bool $tiene_cochera = false;
    public string $nro_cochera = '';
    public string $estado_ocupacion = '';
    public string $nro_cupon_siro = '';
    public string $codigo_pago_electronico = '';
    public string $recibos_a_nombre_de = '';
    public string $condicion_iva = '';
    public string $email_expensas_ordinarias = '';
    public string $email_expensas_extraordinarias = '';
    public bool $activo = true;

    public string $propietario_nombre = '';
    public string $propietario_dni = '';
    public string $propietario_direccion_postal = '';
    public string $propietario_email = '';
    public string $propietario_telefono = '';

    public string $inquilino_nombre = '';
    public string $inquilino_apellido = '';
    public string $inquilino_telefono = '';
    public string $inquilino_email = '';
    public string $inquilino_direccion_postal = '';
    public ?string $inquilino_fecha_fin_contrato = null;
    public bool $inquilino_activo = true;

    public string $inmobiliaria_nombre = '';
    public string $inmobiliaria_apellido = '';
    public string $inmobiliaria_telefono = '';
    public string $inmobiliaria_email = '';
    public string $inmobiliaria_direccion = '';

    public array $contactos_propietario = [];
    public array $contactos_inquilino = [];

    public function mount(): void
    {
        $this->resetForm();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedConsorcioFilter(): void
    {
        $this->resetPage();
    }

    public function addContactoPropietario(): void
    {
        $this->contactos_propietario[] = ['nombre' => '', 'telefono' => '', 'email' => ''];
    }

    public function removeContactoPropietario(int $index): void
    {
        unset($this->contactos_propietario[$index]);
        $this->contactos_propietario = array_values($this->contactos_propietario);
    }

    public function addContactoInquilino(): void
    {
        $this->contactos_inquilino[] = ['nombre' => '', 'telefono' => '', 'email' => ''];
    }

    public function removeContactoInquilino(int $index): void
    {
        unset($this->contactos_inquilino[$index]);
        $this->contactos_inquilino = array_values($this->contactos_inquilino);
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        if ($this->consorcioFilter !== '') {
            $this->consorcio_id = (int) $this->consorcioFilter;
        }
        $this->showModal = true;
    }

    public function openEditModal(int $id): void
    {
        $unidad = Unidad::query()
            ->with(['propietario.contactosAlternativos', 'inquilino.contactosAlternativos', 'inmobiliaria'])
            ->findOrFail($id);

        $this->editingId = $unidad->id;
        $this->consorcio_id = $unidad->consorcio_id;
        $this->numero = $unidad->numero;
        $this->nro_ph = $unidad->nro_ph ?? '';
        $this->coeficiente = $unidad->coeficiente !== null ? (string) $unidad->coeficiente : null;
        $this->nomenclatura_catastral = $unidad->nomenclatura_catastral ?? '';
        $this->nro_cuenta_rentas = $unidad->nro_cuenta_rentas ?? '';
        $this->tiene_cochera = $unidad->tiene_cochera;
        $this->nro_cochera = $unidad->nro_cochera ?? '';
        $this->estado_ocupacion = $unidad->estado_ocupacion->value;
        $this->nro_cupon_siro = $unidad->nro_cupon_siro ?? '';
        $this->codigo_pago_electronico = $unidad->codigo_pago_electronico ?? '';
        $this->recibos_a_nombre_de = $unidad->recibos_a_nombre_de->value;
        $this->condicion_iva = $unidad->condicion_iva->value;
        $this->email_expensas_ordinarias = $unidad->email_expensas_ordinarias ?? '';
        $this->email_expensas_extraordinarias = $unidad->email_expensas_extraordinarias ?? '';
        $this->activo = $unidad->activo;

        if ($unidad->propietario) {
            $this->propietario_nombre = $unidad->propietario->nombre;
            $this->propietario_dni = $unidad->propietario->dni ?? '';
            $this->propietario_direccion_postal = $unidad->propietario->direccion_postal ?? '';
            $this->propietario_email = $unidad->propietario->email ?? '';
            $this->propietario_telefono = $unidad->propietario->telefono ?? '';
            $this->contactos_propietario = $unidad->propietario->contactosAlternativos
                ->map(fn ($c) => ['nombre' => $c->nombre, 'telefono' => $c->telefono ?? '', 'email' => $c->email ?? ''])
                ->values()
                ->all();
        }

        if ($unidad->inquilino) {
            $this->inquilino_nombre = $unidad->inquilino->nombre ?? '';
            $this->inquilino_apellido = $unidad->inquilino->apellido ?? '';
            $this->inquilino_telefono = $unidad->inquilino->telefono ?? '';
            $this->inquilino_email = $unidad->inquilino->email ?? '';
            $this->inquilino_direccion_postal = $unidad->inquilino->direccion_postal ?? '';
            $this->inquilino_fecha_fin_contrato = $unidad->inquilino->fecha_fin_contrato?->format('Y-m-d');
            $this->inquilino_activo = $unidad->inquilino->activo;
            $this->contactos_inquilino = $unidad->inquilino->contactosAlternativos
                ->map(fn ($c) => ['nombre' => $c->nombre, 'telefono' => $c->telefono ?? '', 'email' => $c->email ?? ''])
                ->values()
                ->all();
        }

        if ($unidad->inmobiliaria) {
            $this->inmobiliaria_nombre = $unidad->inmobiliaria->nombre ?? '';
            $this->inmobiliaria_apellido = $unidad->inmobiliaria->apellido ?? '';
            $this->inmobiliaria_telefono = $unidad->inmobiliaria->telefono ?? '';
            $this->inmobiliaria_email = $unidad->inmobiliaria->email ?? '';
            $this->inmobiliaria_direccion = $unidad->inmobiliaria->direccion ?? '';
        }

        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function save(): void
    {
        $this->validate($this->rules());

        DB::transaction(function (): void {
            $unidadData = [
                'consorcio_id' => $this->consorcio_id,
                'numero' => $this->numero,
                'nro_ph' => $this->nro_ph ?: null,
                'coeficiente' => $this->coeficiente,
                'nomenclatura_catastral' => $this->nomenclatura_catastral ?: null,
                'nro_cuenta_rentas' => $this->nro_cuenta_rentas ?: null,
                'tiene_cochera' => $this->tiene_cochera,
                'nro_cochera' => $this->tiene_cochera ? ($this->nro_cochera ?: null) : null,
                'estado_ocupacion' => $this->estado_ocupacion,
                'nro_cupon_siro' => $this->nro_cupon_siro ?: null,
                'codigo_pago_electronico' => $this->codigo_pago_electronico ?: null,
                'recibos_a_nombre_de' => $this->recibos_a_nombre_de,
                'condicion_iva' => $this->condicion_iva,
                'email_expensas_ordinarias' => $this->email_expensas_ordinarias ?: null,
                'email_expensas_extraordinarias' => $this->email_expensas_extraordinarias ?: null,
                'activo' => $this->activo,
            ];

            if ($this->editingId) {
                $unidad = Unidad::query()->findOrFail($this->editingId);
                $unidad->update($unidadData);
            } else {
                $unidad = Unidad::query()->create($unidadData);
            }

            $propietario = $unidad->propietario()->updateOrCreate([], [
                'nombre' => $this->propietario_nombre,
                'dni' => $this->propietario_dni ?: null,
                'direccion_postal' => $this->propietario_direccion_postal ?: null,
                'email' => $this->propietario_email ?: null,
                'telefono' => $this->propietario_telefono ?: null,
            ]);

            $this->syncContactos($propietario->id, 'propietario', $this->contactos_propietario);

            $hayInquilino = $this->inquilino_nombre !== '' || $this->inquilino_apellido !== '' || $this->inquilino_email !== '' || $this->inquilino_telefono !== '';
            if ($hayInquilino) {
                $inquilino = $unidad->inquilino()->updateOrCreate([], [
                    'nombre' => $this->inquilino_nombre ?: null,
                    'apellido' => $this->inquilino_apellido ?: null,
                    'telefono' => $this->inquilino_telefono ?: null,
                    'email' => $this->inquilino_email ?: null,
                    'direccion_postal' => $this->inquilino_direccion_postal ?: null,
                    'fecha_fin_contrato' => $this->inquilino_fecha_fin_contrato ?: null,
                    'activo' => $this->inquilino_activo,
                ]);
                $this->syncContactos($inquilino->id, 'inquilino', $this->contactos_inquilino);
            } else {
                if ($unidad->inquilino) {
                    $this->syncContactos($unidad->inquilino->id, 'inquilino', []);
                    $unidad->inquilino->delete();
                }
            }

            $hayInmobiliaria = $this->inmobiliaria_nombre !== '' || $this->inmobiliaria_apellido !== '' || $this->inmobiliaria_email !== '' || $this->inmobiliaria_telefono !== '';
            if ($hayInmobiliaria) {
                $unidad->inmobiliaria()->updateOrCreate([], [
                    'nombre' => $this->inmobiliaria_nombre ?: null,
                    'apellido' => $this->inmobiliaria_apellido ?: null,
                    'telefono' => $this->inmobiliaria_telefono ?: null,
                    'email' => $this->inmobiliaria_email ?: null,
                    'direccion' => $this->inmobiliaria_direccion ?: null,
                ]);
            } else {
                $unidad->inmobiliaria()?->delete();
            }
        });

        session()->flash('status', $this->editingId ? 'Unidad actualizada correctamente.' : 'Unidad creada correctamente.');
        $this->closeModal();
        $this->dispatch('unidad-saved');
    }

    public function delete(int $id): void
    {
        Unidad::query()->findOrFail($id)->delete();
        $this->resetPage();
    }

    public function render()
    {
        $consorcios = Consorcio::query()->orderBy('nombre')->get(['id', 'nombre']);

        $unidades = Unidad::query()
            ->with(['consorcio:id,nombre', 'propietario:unidad_id,nombre,email', 'inquilino:unidad_id,nombre,apellido,email'])
            ->when($this->consorcioFilter !== '', fn ($q) => $q->where('consorcio_id', (int) $this->consorcioFilter))
            ->when($this->search !== '', function ($q) {
                $term = '%'.$this->search.'%';
                $q->where(function ($q2) use ($term) {
                    $q2->where('numero', 'like', $term)
                        ->orWhereHas('propietario', fn ($qp) => $qp->where('nombre', 'like', $term))
                        ->orWhereHas('inquilino', fn ($qi) => $qi->where('nombre', 'like', $term)->orWhere('apellido', 'like', $term));
                });
            })
            ->orderBy('consorcio_id')
            ->orderBy('numero')
            ->paginate(10);

        $coeficienteTotal = null;
        if ($this->consorcioFilter !== '') {
            $coeficienteTotal = (float) Unidad::query()
                ->where('consorcio_id', (int) $this->consorcioFilter)
                ->sum('coeficiente');
        }

        return view('livewire.unidades.unidad-index', [
            'unidades' => $unidades,
            'consorcios' => $consorcios,
            'coeficienteTotal' => $coeficienteTotal,
            'estadosOcupacion' => EstadoOcupacionUnidad::cases(),
            'recibosNombre' => ReciboNombreUnidad::cases(),
            'condicionesIva' => CondicionIvaUnidad::cases(),
        ])->layout('layouts.app', ['active' => 'unidades']);
    }

    private function rules(): array
    {
        return [
            'consorcio_id' => ['required', 'exists:consorcios,id'],
            'numero' => ['required', 'string', 'max:20'],
            'nro_ph' => ['nullable', 'string', 'max:20'],
            'coeficiente' => ['required', 'numeric', 'min:0', 'max:100'],
            'nomenclatura_catastral' => ['nullable', 'string', 'max:100'],
            'nro_cuenta_rentas' => ['nullable', 'string', 'max:50'],
            'nro_cochera' => ['nullable', 'string', 'max:20'],
            'estado_ocupacion' => ['required', Rule::enum(EstadoOcupacionUnidad::class)],
            'nro_cupon_siro' => ['nullable', 'string', 'max:20'],
            'codigo_pago_electronico' => ['nullable', 'string', 'max:50'],
            'recibos_a_nombre_de' => ['required', Rule::enum(ReciboNombreUnidad::class)],
            'condicion_iva' => ['required', Rule::enum(CondicionIvaUnidad::class)],
            'email_expensas_ordinarias' => ['nullable', 'string', 'max:500'],
            'email_expensas_extraordinarias' => ['nullable', 'string', 'max:500'],
            'propietario_nombre' => ['required', 'string', 'max:200'],
            'propietario_dni' => ['nullable', 'string', 'max:20'],
            'propietario_direccion_postal' => ['nullable', 'string', 'max:500'],
            'propietario_email' => ['nullable', 'string', 'max:500'],
            'propietario_telefono' => ['nullable', 'string', 'max:200'],
            'inquilino_nombre' => ['nullable', 'string', 'max:200'],
            'inquilino_apellido' => ['nullable', 'string', 'max:200'],
            'inquilino_telefono' => ['nullable', 'string', 'max:200'],
            'inquilino_email' => ['nullable', 'string', 'max:500'],
            'inquilino_direccion_postal' => ['nullable', 'string', 'max:500'],
            'inquilino_fecha_fin_contrato' => ['nullable', 'date'],
            'inmobiliaria_nombre' => ['nullable', 'string', 'max:200'],
            'inmobiliaria_apellido' => ['nullable', 'string', 'max:200'],
            'inmobiliaria_telefono' => ['nullable', 'string', 'max:200'],
            'inmobiliaria_email' => ['nullable', 'string', 'max:500'],
            'inmobiliaria_direccion' => ['nullable', 'string', 'max:500'],
            'contactos_propietario.*.nombre' => ['nullable', 'string', 'max:200'],
            'contactos_propietario.*.telefono' => ['nullable', 'string', 'max:200'],
            'contactos_propietario.*.email' => ['nullable', 'string', 'max:500'],
            'contactos_inquilino.*.nombre' => ['nullable', 'string', 'max:200'],
            'contactos_inquilino.*.telefono' => ['nullable', 'string', 'max:200'],
            'contactos_inquilino.*.email' => ['nullable', 'string', 'max:500'],
        ];
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->consorcio_id = null;
        $this->numero = '';
        $this->nro_ph = '';
        $this->coeficiente = null;
        $this->nomenclatura_catastral = '';
        $this->nro_cuenta_rentas = '';
        $this->tiene_cochera = false;
        $this->nro_cochera = '';
        $this->estado_ocupacion = EstadoOcupacionUnidad::PropietarioResidente->value;
        $this->nro_cupon_siro = '';
        $this->codigo_pago_electronico = '';
        $this->recibos_a_nombre_de = ReciboNombreUnidad::Propietario->value;
        $this->condicion_iva = CondicionIvaUnidad::ConsumidorFinal->value;
        $this->email_expensas_ordinarias = '';
        $this->email_expensas_extraordinarias = '';
        $this->activo = true;

        $this->propietario_nombre = '';
        $this->propietario_dni = '';
        $this->propietario_direccion_postal = '';
        $this->propietario_email = '';
        $this->propietario_telefono = '';

        $this->inquilino_nombre = '';
        $this->inquilino_apellido = '';
        $this->inquilino_telefono = '';
        $this->inquilino_email = '';
        $this->inquilino_direccion_postal = '';
        $this->inquilino_fecha_fin_contrato = null;
        $this->inquilino_activo = true;

        $this->inmobiliaria_nombre = '';
        $this->inmobiliaria_apellido = '';
        $this->inmobiliaria_telefono = '';
        $this->inmobiliaria_email = '';
        $this->inmobiliaria_direccion = '';

        $this->contactos_propietario = [];
        $this->contactos_inquilino = [];
        $this->resetValidation();
    }

    private function syncContactos(int $contactableId, string $type, array $contactos): void
    {
        DB::table('contactos_alternativos')
            ->where('contactable_type', $type)
            ->where('contactable_id', $contactableId)
            ->delete();

        foreach ($contactos as $contacto) {
            $nombre = trim((string) ($contacto['nombre'] ?? ''));
            if ($nombre === '') {
                continue;
            }

            DB::table('contactos_alternativos')->insert([
                'contactable_type' => $type,
                'contactable_id' => $contactableId,
                'nombre' => $nombre,
                'telefono' => trim((string) ($contacto['telefono'] ?? '')) ?: null,
                'email' => trim((string) ($contacto['email'] ?? '')) ?: null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
