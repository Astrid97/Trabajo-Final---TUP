@extends('layouts.app')

@section('title', 'Inicio | Riplat')

@section('content')

<header class="topbar">

    <div>
        <p class="eyebrow">Resumen financiero</p>
        <h1>Hola, Carina</h1>
    </div>

    <div class="avatar">
        C
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

        <div class="category-list">

            @forelse($resumen['gastos_por_categoria'] as $gasto)

                <div class="category-item">

                    <div class="category-info">

                        <div>
                            <strong>
                                {{ $gasto['categoria'] }}
                            </strong>

                            <span>
                                $ {{ number_format(
                                    $gasto['total'],
                                    0,
                                    ',',
                                    '.'
                                ) }}
                            </span>
                        </div>

                        <strong>
                            {{ number_format(
                                $gasto['porcentaje'],
                                1,
                                ',',
                                '.'
                            ) }}%
                        </strong>

                    </div>

                    <div class="category-progress">

                        <div
                            class="category-progress-value"
                            style="width: {{ $gasto['porcentaje'] }}%"
                        ></div>

                    </div>

                </div>

            @empty

                <div class="empty-state">
                    Todavía no hay gastos registrados.
                </div>

            @endforelse

        </div>

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


    <article class="panel financial-panel">
        <div class="financial-illustration">
            <img
                src="{{ asset('images/riplat-logo-light.jpg') }}"
                alt="Riplat"
            >
        </div>

        <div>
            <p class="eyebrow">
                Tu contexto
            </p>

            <h3>
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

</section>

@endsection

<script>
document.addEventListener('DOMContentLoaded', () => {

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
                `/financial-context/1/{{ $cuentaId }}/evaluar-gasto`
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