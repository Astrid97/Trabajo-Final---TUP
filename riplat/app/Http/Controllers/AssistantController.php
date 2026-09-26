<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\IA\AssistantService;
use Illuminate\Support\Facades\Log;
use Exception;

class AssistantController extends Controller
{
    private $assistantService;

    public function __construct(AssistantService $assistantService)
    {
        $this->assistantService = $assistantService;
    }

    public function chat(Request $request)
    {
        // 1. Validamos que el frontend no mande mensajes vacíos o muy largos
        $request->validate([
            'mensaje' => 'required|string|max:255'
        ]);

        // 2. Obtenemos la cuenta única del usuario que está logueado
        $cuenta = $request->user()->cuenta;

        if (!$cuenta) {
            return response()->json([
                'status' => 'error',
                'message' => 'No tienes una cuenta financiera configurada.'
            ], 403);
        }

        try {
            // 3. Le pasamos la papa caliente al orquestador de IA
            $resultado = $this->assistantService->processChat($request->input('mensaje'), $cuenta);

            // 4. Devolvemos el JSON estructurado al frontend (pantalla del chat)
            return response()->json($resultado);
            
        } catch (Exception $e) {
            Log::error('Error en AssistantController@chat', [
            'error' => $e->getMessage(),
            'user_id' => $request->user()->id,
        ]);

        return response()->json([
            'status' => 'error',
            'message' => 'Hubo un error al procesar tu solicitud. Intentá nuevamente en unos minutos.'
        ], 500);
        }
    }
}