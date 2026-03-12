<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprobante de Prestación - {{ $movement->reference }}</title>
    <!-- Tailwind CSS (CDN for printing support) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }

            .no-print {
                display: none !important;
            }
        }
    </style>
</head>

<body class="bg-gray-100 p-8 min-h-screen flex items-center justify-center font-sans">

    @php
        $empresa = collect([
            $movement->empresa->razon_social ?? '',
            $movement->empresa->sigla ?? ''
        ])->filter()->implode(' - ');

        $empleado = collect([
            $movement->usuario->primer_nombre ?? '',
            $movement->usuario->otros_nombres ?? '',
            $movement->usuario->primer_apellido ?? '',
            $movement->usuario->segundo_apellido ?? ''
        ])->filter()->implode(' ');

        $isWithdrawal = $movement->movement_type === 'withdrawal';
        $isConsignment = str_contains(strtolower($movement->reference), 'consignacion');

        $brandColor = $isWithdrawal ? 'bg-emerald-600' : 'bg-[#1565C0]';
    @endphp

    <div class="max-w-2xl w-full bg-white shadow-xl mx-auto border border-gray-200" id="comprobante">

        <!-- Header -->
        <div class="px-8 py-6 {{ $brandColor }} text-white flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold tracking-wide uppercase">Comprobante de Prestación</h1>
                <p class="text-sm opacity-90 mt-1">Ref: #{{ str_pad($movement->id, 8, '0', STR_PAD_LEFT) }}</p>
            </div>
            <div class="text-right">
                <h2 class="text-lg font-bold">{{ $empresa ?: 'Nomitech Payroll' }}</h2>
                <p class="text-sm opacity-90">NIT: {{ $movement->empresa->nit ?? 'N/A' }}</p>
            </div>
        </div>

        <!-- Body -->
        <div class="p-8 space-y-8">

            <div class="flex justify-between items-end border-b pb-4">
                <div>
                    <p class="text-gray-500 text-sm font-semibold uppercase">Fecha de Emisión</p>
                    <p class="text-lg text-gray-800 font-medium">{{ $movement->created_at->format('d/m/Y h:i A') }}</p>
                </div>
                <div class="text-right">
                    <p class="text-gray-500 text-sm font-semibold uppercase">Estado</p>
                    <span
                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-sm font-medium
                        {{ $movement->status === 'processed' ? 'bg-blue-100 text-blue-700' :
    ($movement->status === 'reported' ? 'bg-indigo-100 text-indigo-700' : 'bg-amber-100 text-amber-700') }}">
                        <span class="w-2 h-2 rounded-full 
                            {{ $movement->status === 'processed' ? 'bg-blue-500' :
    ($movement->status === 'reported' ? 'bg-indigo-500' : 'bg-amber-500') }}">
                        </span>
                        {{ strtoupper($movement->status ?? 'APLICADO') }}
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-6 bg-gray-50 p-6 rounded-lg border border-gray-100">
                <div>
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Beneficiario / Empleado
                    </h3>
                    <p class="text-base text-gray-800 font-bold">{{ $empleado }}</p>
                    <p class="text-sm text-gray-600">CC: {{ $movement->employee_id }}</p>
                </div>
                <div>
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Destino del Pago</h3>
                    <p class="text-base text-gray-800 font-bold">
                        @if($movement->destination === 'fund')
                            Fondo de Cesantías
                            <span
                                class="block text-sm font-normal text-gray-600">({{ $movement->usuario->fondo_cesantias ?? 'Por Asignar' }})</span>
                        @else
                            Pago Directo al Empleado
                        @endif
                    </p>
                </div>
            </div>

            <div>
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b-2 border-gray-200">
                            <th class="py-3 text-sm font-bold text-gray-600 uppercase">Concepto / Referencia</th>
                            <th class="py-3 text-sm font-bold text-gray-600 uppercase">Tipo</th>
                            <th class="py-3 text-right text-sm font-bold text-gray-600 uppercase">Monto Retenido/Pagado
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr>
                            <td class="py-4 text-gray-800 font-medium">
                                {{ $movement->reference }}
                            </td>
                            <td class="py-4 text-gray-600 capitalize">
                                {{ App\Models\BenefitLedger::benefitTypeLabel($movement->benefit_type) }}
                            </td>
                            <td class="py-4 text-right text-lg font-bold text-gray-900">
                                ${{ number_format(abs($movement->amount), 2, ',', '.') }}
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr class="border-t-2 border-gray-800">
                            <td colspan="2"
                                class="py-4 text-right text-sm font-bold uppercase tracking-wider text-gray-600">
                                Total Aprobado:
                            </td>
                            <td class="py-4 text-right text-2xl font-black text-gray-900">
                                ${{ number_format(abs($movement->amount), 2, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Signatures -->
            <div class="mt-16 pt-8 grid grid-cols-2 gap-12 text-center">
                <div>
                    <div class="border-b border-gray-400 w-full mb-2 h-16"></div>
                    <p class="text-sm font-bold text-gray-800">Firma Empleador</p>
                    <p class="text-xs text-gray-500">{{ $empresa ?: 'Nomitech Payroll' }}</p>
                </div>
                <div>
                    <div class="border-b border-gray-400 w-full mb-2 h-16"></div>
                    <p class="text-sm font-bold text-gray-800">Firma Empleado / Beneficiario</p>
                    <p class="text-xs text-gray-500">CC: {{ $movement->employee_id }}</p>
                </div>
            </div>

            <div class="text-center text-xs text-gray-400 mt-8">
                Generado por Nomitech Payroll • Comprobante válido para requerimientos del empleado o fondo.
            </div>
        </div>

    </div>

    <!-- Actions bar -->
    <div
        class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 p-4 shadow-lg no-print flex justify-center gap-4">
        <button onclick="window.history.back()"
            class="px-6 py-2.5 bg-gray-100 text-gray-700 font-bold rounded-lg hover:bg-gray-200 transition-colors">
            Volver
        </button>
        <button onclick="window.print()"
            class="px-6 py-2.5 bg-[#1565C0] text-white font-bold rounded-lg shadow hover:bg-[#0D47A1] transition-colors flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="6 9 6 2 18 2 18 9"></polyline>
                <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                <rect x="6" y="14" width="12" height="8"></rect>
            </svg>
            Imprimir Comprobante
        </button>
    </div>

</body>

</html>