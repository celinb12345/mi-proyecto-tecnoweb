<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\Proyecto;
use App\Models\Usuario;
use App\Models\Pago;
use Carbon\Carbon;

class DashboardClienteController extends Controller
{
    public function index()
    {
        /** @var Usuario $u */
        $u = Auth::user();

        if (! $u || $u->role !== 'cliente') {
            return redirect()->route('login');
        }

        // Cargamos relación "cliente" por acceso directo (lazy load)
        $cliente = $u->cliente;

        $proyecto    = null;
        $metrics     = null;
        $propietario = null;
        $totalPagado = 0;

        if ($cliente) {
            $proyecto = Proyecto::where('id_cliente', $cliente->id_cliente)
                ->with('propietario')
                ->orderByDesc('fecha_inicio_pro')
                ->first();

            // total pagado por este cliente (indistinto del proyecto, pero normalmente tendrá uno)
            $totalPagado = Pago::where('id_cliente', $cliente->id_cliente)
                ->sum('monto_total_pago');

            if ($proyecto) {
                $inicio = Carbon::parse($proyecto->fecha_inicio_pro);
                $fin    = Carbon::parse($proyecto->fecha_fin_pro);
                $hoy    = Carbon::today();

                $totalDias = max($inicio->diffInDays($fin) + 1, 1);

                if ($hoy->lt($inicio)) {
                    $diasTranscurridos = 0;
                    $diasRestantes     = $totalDias;
                    $estado            = 'No iniciado';
                } elseif ($hoy->between($inicio, $fin)) {
                    $diasTranscurridos = $inicio->diffInDays($hoy) + 1;
                    $diasRestantes     = max($totalDias - $diasTranscurridos, 0);
                    $estado            = 'En ejecución';
                } else {
                    $diasTranscurridos = $totalDias;
                    $diasRestantes     = 0;
                    $estado            = 'Finalizado';
                }

                $porcentaje = round(($diasTranscurridos / $totalDias) * 100, 1);

                $metrics = [
                    'total_dias'         => $totalDias,
                    'dias_transcurridos' => $diasTranscurridos,
                    'dias_restantes'     => $diasRestantes,
                    'porcentaje'         => $porcentaje,
                    'estado'             => $estado,
                    'hoy'                => $hoy->toDateString(),
                ];

                $propietario = $proyecto->propietario ?? null;
            }
        }

        // nombre que mostraremos en "Bienvenido, ..."
        $nombreCliente = null;
        if ($cliente) {
            $nombreCliente = trim(($cliente->nombre_cli ?? '') . ' ' . ($cliente->apellido_cli ?? ''));
        }
        if (! $nombreCliente) {
            $nombreCliente = $u->name ?? $u->id_usuario;
        }

        return Inertia::render('cliente/Dashboard', [
            'user'                => $u,
            'proyecto'            => $proyecto,
            'metrics'             => $metrics,
            'propietario'         => $propietario,
            'nombreCliente'       => $nombreCliente,
            'totalPagadoCliente'  => $totalPagado,   // 👈 nuevo
        ]);
    }
}
