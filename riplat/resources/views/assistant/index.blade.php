@extends('layouts.app')

@section('title', 'Chat IA | Riplat')

@section('content')

<header class="topbar">
    <div>
        <p class="eyebrow">Asistente financiero</p>
        <h1>Chat IA</h1>
    </div>
</header>

<section class="panel chat-panel">
    <div class="panel-header">
        <div>
            <p class="eyebrow">Consulta</p>
            <h3>Habla con tu asistente</h3>
        </div>
    </div>

    <div class="chat-thread" id="chat-thread">
        <div class="chat-message bot">
            <strong>Riplat IA</strong>
            <p>Podés pedirme registrar un gasto o ingreso, por ejemplo: “Gasté $1500 en supermercado”.</p>
        </div>
    </div>

    <form id="chat-form" class="chat-form" action="{{ route('assistant.chat') }}">
        <input
            type="hidden"
            name="cuenta_id"
            value="{{ $cuentaId }}"
        >

        <textarea
            id="chat-input"
            name="mensaje"
            rows="3"
            maxlength="255"
            placeholder="Ingresá tu mensaje..."
            required
        ></textarea>

        <button type="submit" class="primary-button">
            Enviar
        </button>
    </form>
</section>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('chat-form');
    const input = document.getElementById('chat-input');
    const thread = document.getElementById('chat-thread');

    input.addEventListener('keydown', (event) => {
        if (event.key !== 'Enter' || event.shiftKey || event.isComposing) {
            return;
        }

        event.preventDefault();
        form.requestSubmit();
    });

    const addMessage = (text, role = 'bot') => {
        const message = document.createElement('div');
        message.className = `chat-message ${role}`;

        if (role === 'bot') {
            message.innerHTML = `
                <strong>Riplat IA</strong>
                <p>${text}</p>
            `;
        } else {
            message.innerHTML = `
                <strong>Vos</strong>
                <p>${text}</p>
            `;
        }

        thread.appendChild(message);
        thread.scrollTop = thread.scrollHeight;
    };

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const mensaje = input.value.trim();

        if (!mensaje) {
            return;
        }

        addMessage(mensaje, 'user');
        input.value = '';

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    mensaje,
                    cuenta_id: Number(form.elements.namedItem('cuenta_id').value)
                })
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'No se pudo contactar con la IA.');
            }

            addMessage(data.message || 'Recibí tu solicitud.');
        } catch (error) {
            addMessage(error.message || 'Ocurrió un error con el asistente.', 'bot');
        }
    });
});
</script>
@endpush
