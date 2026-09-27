<?php

namespace App\Http\Controllers;

use App\Services\IA\AssistantService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class AssistantController extends Controller
{
    public function __construct(
        private readonly AssistantService $assistantService) {}

    public function index(int $cuentaId): View
    {
        $cuenta = request()->user()->cuenta;
        abort_unless($cuenta && $cuenta->id === $cuentaId, 404);

        return view('assistant.index', [
            'cuentaId' => $cuenta->id,
        ]);
    }

    public function chat(Request $request)
    {
        $request->validate([
            'mensaje' => 'required|string|max:255',
        ]);

        $cuenta = $request->user()->cuenta;

        if (! $cuenta) {
            return response()->json([
                'status' => 'error',
                'message' => 'No tienes una cuenta financiera configurada.',
            ], 403);
        }

        try {
            $resultado = $this->assistantService->processChat(
                $request->input('mensaje'),
                $cuenta
            );

            return response()->json($resultado);
        } catch (Exception $e) {
            Log::error('Error en AssistantController@chat', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()?->id,
                'cuenta_id' => $cuenta->id,
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Hubo un error al procesar tu solicitud. Intentá nuevamente en unos minutos.',
            ], 500);
        }
    }
}
