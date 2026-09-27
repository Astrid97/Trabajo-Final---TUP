@extends('layouts.app')

@section('title', 'Movimientos | Riplat')

@section('content')

<header class="topbar">
    <div>
        <p class="eyebrow">Tu actividad</p>
        <h1>Movimientos</h1>
    </div>
</header>


{{-- MENSAJE DE ÉXITO --}}
@if(session('success'))
    <div class="alert-success">
        {{ session('success') }}
    </div>
@endif


{{-- ERRORES DE VALIDACIÓN --}}
@if($errors->any())
    <div class="alert-error">
        <strong>No pudimos registrar el movimiento.</strong>

        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif


<section class="panel">

    <div class="panel-header">
        <div>
            <p class="eyebrow">Historial</p>
            <h3>Tus movimientos</h3>
        </div>

        <span class="panel-count">
            {{ $movimientos->count() }}
        </span>
    </div>


    <div class="movement-list">

        @forelse($movimientos as $movimiento)

            <div class="movement">

                <div class="movement-left">

                    <div
                        class="movement-icon
                        {{ $movimiento->tipo === 'INGRESO'
                            ? 'movement-income'
                            : 'movement-expense' }}"
                    >
                        {{ $movimiento->tipo === 'INGRESO'
                            ? '↑'
                            : '↓' }}
                    </div>


                    <div>
                        <strong>
                            {{ $movimiento->descripcion
                                ?? $movimiento->categoria->nombre }}
                        </strong>

                        <p>
                            {{ $movimiento->categoria->nombre }}
                            ·
                            {{ $movimiento->fecha->format('d/m/Y') }}
                        </p>
                    </div>

                </div>


                <div class="movement-data">

                    <strong
                        class="movement-amount
                        {{ $movimiento->tipo === 'INGRESO'
                            ? 'amount-income'
                            : 'amount-expense' }}"
                    >
                        {{ $movimiento->tipo === 'INGRESO'
                            ? '+'
                            : '-' }}

                        $ {{ number_format(
                            $movimiento->monto,
                            0,
                            ',',
                            '.'
                        ) }}
                    </strong>


                    @if($movimiento->estado === 'ANULADO')
                        <small class="movement-status">
                            Anulado
                        </small>
                    @endif

                </div>

            </div>

        @empty

            <div class="empty-state">
                Todavía no hay movimientos registrados.
            </div>

        @endforelse

    </div>

</section>


{{-- BOTÓN FLOTANTE --}}
<button
    type="button"
    class="floating-button modal-trigger"
    id="open-movement-modal"
    aria-label="Nuevo movimiento"
    aria-controls="movement-modal"
    aria-expanded="false"
    data-tooltip="Nuevo movimiento"
>
    <i data-lucide="plus" aria-hidden="true"></i>
</button>


{{-- MODAL --}}
<div
    class="modal-overlay"
    id="movement-modal"
    aria-hidden="true"
    data-validation-errors="{{ $errors->any() ? 'true' : 'false' }}"
>

    <div
        class="modal-card"
        role="dialog"
        aria-modal="true"
        aria-labelledby="movement-modal-title"
    >

        <div class="modal-header">

            <div>
                <p class="eyebrow">
                    Carga manual
                </p>

                <h2 id="movement-modal-title">
                    Nuevo movimiento
                </h2>
            </div>

            <button
                type="button"
                class="modal-close"
                id="close-movement-modal"
                aria-label="Cerrar"
            >
                ×
            </button>

        </div>


        <p class="modal-description">
            Ingresá los datos del movimiento que querés registrar.
        </p>


        <form
            action="{{ route('movimientos.store') }}"
            method="POST"
            class="movement-form"
        >
            @csrf

            <div class="form-field">

                <label for="tipo">
                    Tipo
                </label>

                <select
                    id="tipo"
                    name="tipo"
                    required
                >
                    <option value="">
                        Seleccionar
                    </option>

                    <option
                        value="INGRESO"
                        {{ old('tipo') === 'INGRESO' ? 'selected' : '' }}
                    >
                        Ingreso
                    </option>

                    <option
                        value="GASTO"
                        {{ old('tipo') === 'GASTO' ? 'selected' : '' }}
                    >
                        Gasto
                    </option>

                </select>

            </div>


            <div class="form-field">

                <label for="categoria_id">
                    Categoría
                </label>

                <select
                    id="categoria_id"
                    name="categoria_id"
                    required
                >
                    <option value="">
                        Seleccionar
                    </option>

                    @foreach($categorias as $categoria)

                        <option
                            value="{{ $categoria->id }}"
                            data-tipo="{{ $categoria->tipo }}"
                            {{ (string) old('categoria_id') === (string) $categoria->id
                                ? 'selected'
                                : '' }}
                        >
                            {{ $categoria->nombre }}
                        </option>

                    @endforeach

                </select>

            </div>


            <div class="form-field">

                <label for="monto">
                    Monto
                </label>

                <div class="amount-input movement-amount-input">

                    <span>$</span>

                    <input
                        id="monto"
                        name="monto"
                        type="number"
                        min="0.01"
                        step="0.01"
                        value="{{ old('monto') }}"
                        placeholder="30000"
                        required
                    >

                </div>

            </div>


            <div class="form-field">

                <label for="fecha">
                    Fecha
                </label>

                <input
                    id="fecha"
                    name="fecha"
                    type="date"
                    value="{{ old('fecha', now()->format('Y-m-d')) }}"
                >

            </div>


            <div class="form-field">

                <label for="descripcion">
                    Descripción
                </label>

                <input
                    id="descripcion"
                    name="descripcion"
                    type="text"
                    maxlength="255"
                    value="{{ old('descripcion') }}"
                    placeholder="Ej: Supermercado"
                >

            </div>


            <div class="modal-actions">

                <button
                    type="submit"
                    class="primary-button"
                >
                    Registrar
                </button>

            </div>

        </form>

    </div>

</div>

@endsection


@push('scripts')

<script>
document.addEventListener('DOMContentLoaded', () => {

    const modal = document.getElementById('movement-modal');

    const openButton = document.getElementById(
        'open-movement-modal'
    );

    const closeButton = document.getElementById(
        'close-movement-modal'
    );

    const submitButton = modal.querySelector('.movement-form button[type="submit"]');

    function openModal() {
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        openButton.setAttribute('aria-expanded', 'true');
        document.body.classList.add('modal-open');
        closeButton.focus();
    }


    function closeModal() {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        openButton.setAttribute('aria-expanded', 'false');
        document.body.classList.remove('modal-open');
        openButton.focus();
    }


    openButton.addEventListener('click', openModal);

    closeButton.addEventListener('click', closeModal);

    modal.addEventListener('click', (event) => {

        if (event.target === modal) {
            closeModal();
        }

    });


    document.addEventListener('keydown', (event) => {
        if (!modal.classList.contains('is-open')) return;

        if (event.key === 'Escape') {
            closeModal();
        } else if (event.key === 'Tab') {
            if (event.shiftKey && document.activeElement === closeButton) {
                event.preventDefault();
                submitButton.focus();
            } else if (!event.shiftKey && document.activeElement === submitButton) {
                event.preventDefault();
                closeButton.focus();
            }
        }
    });

const hasValidationErrors = modal.dataset.validationErrors === 'true';

if (hasValidationErrors) {
    openModal();
}


/* FILTRAR CATEGORÍAS SEGÚN TIPO */

const typeSelect = document.getElementById('tipo');
const categorySelect = document.getElementById('categoria_id');

function filterCategories() {

    const selectedType = typeSelect.value;

    Array.from(categorySelect.options).forEach((option) => {

        if (!option.value) {
            return;
        }

        const categoryType = option.dataset.tipo;

        option.hidden =
            selectedType !== ''
            && categoryType !== selectedType;
    });


    const selectedCategory =
        categorySelect.options[
            categorySelect.selectedIndex
        ];

    if (
        selectedCategory
        && selectedCategory.value
        && selectedCategory.hidden
    ) {
        categorySelect.value = '';
    }
}


typeSelect.addEventListener(
    'change',
    filterCategories
);

filterCategories();

});
</script>

@endpush