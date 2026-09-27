@extends('layouts.app')

@section('title', 'Inicio | Riplat')

@section('content')

<header class="topbar">

    <div>
        <p class="eyebrow">Resumen financiero</p>
        <h1>Hola, {{ auth()->user()->username }}</h1>
    </div>

    <div class="avatar">
        {{ strtoupper(substr(auth()->user()->username, 0, 1)) }}
    </div>

</header>


<section class="balance-card">

    <div>
        <p class="balance-label">
            Saldo disponible
        </p>

        <h2>
            $ {{ number_format(
                $resumen['saldo'],
                0,
                ',',
                '.'
            ) }}
        </h2>

        <p class="balance-description">
            Balance actual de tu cuenta
        </p>
    </div>

    <div class="balance-decoration">
        ~
    </div>

</section>


<section class="summary-grid">

    <article class="summary-card">

        <div class="summary-icon income">
            ↑
        </div>

        <div>
            <p>Ingresos</p>

            <strong>
                $ {{ number_format(
                    $resumen['total_ingresos'],
                    0,
                    ',',
                    '.'
                ) }}
            </strong>
        </div>

    </article>


    <article class="summary-card">

        <div class="summary-icon expense">
            ↓
        </div>

        <div>
            <p>Gastos</p>

            <strong>
                $ {{ number_format(
                    $resumen['total_gastos'],
                    0,
                    ',',
                    '.'
                ) }}
            </strong>
        </div>

    </article>

</section>


<section class="dashboard-grid">

        <article class="panel category-panel">

        <div class="panel-header">
            <div>
                <p class="eyebrow">Distribución</p>
                <h3>Gastos por categoría</h3>
            </div>
        </div>

        @if(count($resumen['gastos_por_categoria']))
            <div class="category-breakdown">
                <div class="category-chart-wrap">
                    <canvas
                        id="category-doughnut"
                        role="img"
                        aria-label="Distribución de gastos por categoría"
                    ></canvas>
                    <div class="category-chart-center" aria-hidden="true">
                        <span>Total gastos</span>
                        <strong>$ {{ number_format($resumen['total_gastos'], 0, ',', '.') }}</strong>
                    </div>
                </div>

                <div
                    class="category-list"
                    id="category-list"
                    aria-label="Detalle de gastos por categoría"
                >
                    @foreach($resumen['gastos_por_categoria'] as $gasto)
                        <div class="category-item">
                            <span class="category-swatch" aria-hidden="true"></span>
                            <div class="category-info">
                                <strong>{{ $gasto['categoria'] }}</strong>
                                <span>$ {{ number_format($gasto['total'], 0, ',', '.') }}</span>
                            </div>
                            <strong class="category-percentage">
                                {{ number_format($gasto['porcentaje'], 1, ',', '.') }}%
                            </strong>
                        </div>
                    @endforeach
                </div>
            </div>
            <div
                id="category-chart-data"
                data-categories="{{ json_encode($resumen['gastos_por_categoria']) }}"
                hidden
            ></div>
        @else
            <div class="empty-state">
                Todavía no hay gastos registrados.
            </div>
        @endif

    </article>
    <article class="panel">

        <div class="panel-header">
            

            <div>
                <p class="eyebrow">Actividad</p>
                <h3>Últimos movimientos</h3>
            </div>

            <span class="panel-count">
                {{ count($resumen['ultimos_movimientos']) }}
            </span>

        </div>


        <div class="movement-list">

            @forelse(
                $resumen['ultimos_movimientos']
                as $movimiento
            )

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

                                {{ $movimiento->fecha
                                    ->format('d/m/Y') }}
                            </p>

                        </div>

                    </div>


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

                </div>

            @empty

                <div class="empty-state">
                    Todavía no hay movimientos.
                </div>

            @endforelse

        </div>

    </article>

</section>

<button
    type="button"
    class="floating-button modal-trigger"
    id="open-expense-modal"
    aria-label="¿Puedo gastar?"
    aria-controls="expense-modal"
    aria-expanded="false"
    data-tooltip="¿Puedo gastar?"
>?</button>

<div class="modal-overlay" id="expense-modal" aria-hidden="true">
    <article
        class="panel financial-panel modal-card"
        role="dialog"
        aria-modal="true"
        aria-labelledby="expense-modal-title"
    >
        <button
            type="button"
            class="modal-close"
            id="close-expense-modal"
            aria-label="Cerrar"
        >×</button>
        <div class="financial-illustration">
            <img
                src="{{ asset('images/riplat-logo-light.jpg') }}"
                alt="Riplat"
            >
        </div>

        <div>
            <p class="eyebrow">
                ¿Puedo gastar?
            </p>

            <h3 id="expense-modal-title">
                Evaluá un gasto
            </h3>

            <p class="financial-copy">
                Consultá cómo impactaría un gasto sobre tu saldo
                antes de realizarlo.
            </p>
        </div>

        <form
            id="expense-evaluation-form"
            class="expense-form"
            data-evaluation-url="{{ route('financial-context.evaluate', $cuentaId) }}"
        >
            <label for="expense-amount">
                Monto a evaluar
            </label>

            <div class="amount-input">
                <span>$</span>

                <input
                    id="expense-amount"
                    name="monto"
                    type="number"
                    min="1"
                    step="0.01"
                    placeholder="100000"
                    required
                >
            </div>

            <button
                class="primary-button"
                type="submit"
                id="evaluate-button"
            >
                Evaluar gasto
            </button>
        </form>


        <div
            id="financial-result"
            class="financial-result"
            hidden
        >
            <div class="result-header">

                <span
                    id="result-indicator"
                    class="result-indicator"
                ></span>

                <strong id="result-title"></strong>

            </div>

            <div class="result-values">

                <div>
                    <span>Saldo actual</span>
                    <strong id="current-balance"></strong>
                </div>

                <div>
                    <span>Después del gasto</span>
                    <strong id="future-balance"></strong>
                </div>

                <div>
                    <span>Saldo mínimo</span>
                    <strong id="minimum-balance"></strong>
                </div>

            </div>

            <p
                id="result-message"
                class="result-message"
            ></p>

        </div>

        <p
            id="financial-error"
            class="financial-error"
            hidden
        ></p>

    </article>

</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {

    const modal = document.getElementById('expense-modal');
    const openButton = document.getElementById('open-expense-modal');
    const closeButton = document.getElementById('close-expense-modal');

    function closeModal() {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        openButton.setAttribute('aria-expanded', 'false');
        document.body.classList.remove('modal-open');
        openButton.focus();
    }

    openButton.addEventListener('click', () => {
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        openButton.setAttribute('aria-expanded', 'true');
        document.body.classList.add('modal-open');
        closeButton.focus();
    });

    closeButton.addEventListener('click', closeModal);
    modal.addEventListener('click', (event) => {
        if (event.target === modal) closeModal();
    });
    document.addEventListener('keydown', (event) => {
        if (!modal.classList.contains('is-open')) return;

        if (event.key === 'Escape') {
            closeModal();
        } else if (event.key === 'Tab') {
            if (event.shiftKey && document.activeElement === closeButton) {
                event.preventDefault();
                button.focus();
            } else if (!event.shiftKey && document.activeElement === button) {
                event.preventDefault();
                closeButton.focus();
            }
        }
    });

    const form = document.getElementById(
        'expense-evaluation-form'
    );

    const amountInput = document.getElementById(
        'expense-amount'
    );

    const button = document.getElementById(
        'evaluate-button'
    );

    const result = document.getElementById(
        'financial-result'
    );

    const error = document.getElementById(
        'financial-error'
    );

    const resultIndicator = document.getElementById(
        'result-indicator'
    );

    const resultTitle = document.getElementById(
        'result-title'
    );

    const currentBalance = document.getElementById(
        'current-balance'
    );

    const futureBalance = document.getElementById(
        'future-balance'
    );

    const minimumBalance = document.getElementById(
        'minimum-balance'
    );

    const resultMessage = document.getElementById(
        'result-message'
    );


    const formatMoney = (value) => {
        return new Intl.NumberFormat(
            'es-AR',
            {
                style: 'currency',
                currency: 'ARS',
                maximumFractionDigits: 0
            }
        ).format(value);
    };


    form.addEventListener('submit', async (event) => {

        event.preventDefault();

        result.hidden = true;
        error.hidden = true;

        const amount = Number(amountInput.value);

        if (!amount || amount <= 0) {
            error.textContent =
                'Ingresá un monto mayor a cero.';

            error.hidden = false;

            return;
        }


        button.disabled = true;
        button.textContent = 'Evaluando...';


        try {

            const url =
                `${form.dataset.evaluationUrl}`
                + `?monto=${encodeURIComponent(amount)}`;


            const response = await fetch(url, {
                headers: {
                    'Accept': 'application/json'
                }
            });


            const data = await response.json();


            if (!response.ok) {
                throw new Error(
                    data.message
                    ?? 'No se pudo evaluar el gasto.'
                );
            }


            currentBalance.textContent =
                formatMoney(data.saldo_actual);

            futureBalance.textContent =
                formatMoney(data.saldo_posterior);


            if (data.evaluacion.aplica) {

                minimumBalance.textContent =
                    formatMoney(
                        data.evaluacion.saldo_minimo
                    );


                if (data.evaluacion.cumple) {

                    result.classList.remove(
                        'result-warning'
                    );

                    result.classList.add(
                        'result-success'
                    );

                    resultIndicator.textContent = '✓';

                    resultTitle.textContent =
                        'El gasto respeta tu regla';


                    resultMessage.textContent =
                        `Después del gasto quedarías `
                        + `${formatMoney(
                            data.evaluacion.diferencia
                        )} por encima de tu saldo mínimo.`;

                } else {

                    result.classList.remove(
                        'result-success'
                    );

                    result.classList.add(
                        'result-warning'
                    );

                    resultIndicator.textContent = '!';

                    resultTitle.textContent =
                        'Revisá este gasto';


                    resultMessage.textContent =
                        `Después del gasto quedarías `
                        + `${formatMoney(
                            Math.abs(
                                data.evaluacion.diferencia
                            )
                        )} por debajo del saldo mínimo `
                        + `que configuraste.`;
                }

            } else {

                minimumBalance.textContent =
                    'Sin configurar';

                result.classList.remove(
                    'result-warning',
                    'result-success'
                );

                resultIndicator.textContent = 'i';

                resultTitle.textContent =
                    'Sin regla configurada';

                resultMessage.textContent =
                    'No tenés un saldo mínimo activo '
                    + 'para evaluar este gasto.';
            }


            result.hidden = false;


        } catch (exception) {

            error.textContent = exception.message;
            error.hidden = false;

        } finally {

            button.disabled = false;
            button.textContent = 'Evaluar gasto';

        }

    });

});
</script>
@endpush