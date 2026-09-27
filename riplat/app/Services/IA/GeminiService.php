<?php

namespace App\Services\IA;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    private string $apiKey;

    private string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.key');
        $this->baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-3.8-flash:generateContent';
    }

    public function analyzeIntent(string $prompt, array $tools, ?string $systemInstruction = null)
    {
        $payload = [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => $prompt],
                    ],
                ],
            ],
            'tools' => $tools,
        ];

        if ($systemInstruction !== null) {
            $payload['systemInstruction'] = [
                'parts' => [
                    ['text' => $systemInstruction],
                ],
            ];
        }

        if ($this->apiKey === '') {
            throw new GeminiApiException(
                'El asistente no está configurado. Falta GEMINI_API_KEY en el entorno de Laravel.',
                503
            );
        }

        $response = Http::timeout(20)->withHeaders([
            'Content-Type' => 'application/json',
        ])->post($this->baseUrl.'?key='.$this->apiKey, $payload);

        if ($response->failed()) {
            $status = $response->status();
            $errorStatus = $response->json('error.status');

            Log::error('Error en Gemini API', [
                'status' => $status,
                'provider_status' => $errorStatus,
            ]);

            $message = match (true) {
                $status === 429 => 'Se alcanzó la cuota disponible de Gemini para este proyecto. Revisá la cuota o facturación del proyecto, o intentá cuando se restablezca.',
                $status === 503 => 'Gemini está temporalmente con mucha demanda. Esperá un momento y volvé a intentarlo.',
                in_array($status, [401, 403], true) => 'Gemini rechazó la API key. Verificá que sea válida y que la API esté habilitada en su proyecto.',
                default => 'Gemini no pudo procesar la solicitud en este momento. Intentá nuevamente más tarde.',
            };

            throw new GeminiApiException($message, $status);
        }

        return $response->json();
    }
}
