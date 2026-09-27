@extends('layouts.app')

@section('title', 'Perfil | Riplat')

@section('content')
<header class="topbar">
    <div>
        <p class="eyebrow">Tu espacio</p>
        <h1>Perfil</h1>
    </div>
</header>

<section class="panel profile-panel">
    <div class="profile-identity">
        <div class="avatar">
            {{ strtoupper(substr(auth()->user()->username, 0, 1)) }}
        </div>
        <div>
            <h2>{{ auth()->user()->username }}</h2>
            <p>{{ auth()->user()->email }}</p>
        </div>
    </div>

    <section class="profile-settings" aria-labelledby="minimum-balance-title">
        <p class="eyebrow">Reglas financieras</p>
        <h2 id="minimum-balance-title">Saldo mínimo</h2>
        <p class="profile-settings-copy">
            Definí cuánto querés conservar en tu cuenta. Riplat tendrá en cuenta este monto al evaluar un gasto.
        </p>

        @if(session('status'))
            <p class="profile-success" role="status">{{ session('status') }}</p>
        @endif

        <form
            class="profile-rule-form"
            method="POST"
            action="{{ route('financial-context.minimum-balance.update', $cuentaId) }}"
        >
            @csrf
            @method('PUT')

            <div class="form-field">
                <label for="minimum-balance-amount">Monto mínimo a conservar</label>
                <div class="profile-amount-input">
                    <span>$</span>
                    <input
                        id="minimum-balance-amount"
                        name="monto"
                        type="number"
                        min="0.01"
                        step="0.01"
                        value="{{ old('monto', $saldoMinimo) }}"
                        placeholder="400000"
                        required
                        aria-describedby="minimum-balance-error"
                    >
                </div>
                @error('monto')
                    <p class="auth-error" id="minimum-balance-error">{{ $message }}</p>
                @enderror
            </div>

            <button class="primary-button profile-save-button" type="submit">
                {{ $saldoMinimo === null ? 'Guardar saldo mínimo' : 'Actualizar saldo mínimo' }}
            </button>
        </form>
    </section>

    <form method="POST" action="{{ route('logout') }}" class="profile-logout-form">
        @csrf
        <button class="secondary-button" type="submit">Cerrar sesión</button>
    </form>
</section>
@endsection