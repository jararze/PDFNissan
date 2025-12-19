@extends('layouts.main')

@section('title', 'Resultados del Procesamiento')

@section('content')
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Resultados del Procesamiento</h1>
                <p class="text-gray-600">Resumen de las facturas procesadas</p>
            </div>
            <a href="{{ route('charges.index') }}"
               class="inline-flex items-center gap-2 bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Volver
            </a>
        </div>

        {{-- Stats Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-6 gap-4">
            <div class="bg-white rounded-xl shadow-sm border p-4">
                <div class="flex items-center gap-3">
                    <div class="bg-blue-100 rounded-lg p-2">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900">{{ $totals['total'] }}</p>
                        <p class="text-xs text-gray-500">Total archivos</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border p-4">
                <div class="flex items-center gap-3">
                    <div class="bg-green-100 rounded-lg p-2">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-green-600">{{ $totals['procesados'] }}</p>
                        <p class="text-xs text-gray-500">Procesados</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border p-4">
                <div class="flex items-center gap-3">
                    <div class="bg-red-100 rounded-lg p-2">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-red-600">{{ $totals['eliminados'] }}</p>
                        <p class="text-xs text-gray-500">Eliminados</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border p-4">
                <div class="flex items-center gap-3">
                    <div class="bg-yellow-100 rounded-lg p-2">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-yellow-600">{{ $totals['existentes'] ?? 0 }}</p>
                        <p class="text-xs text-gray-500">Ya existían</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border p-4">
                <div class="flex items-center gap-3">
                    <div class="bg-orange-100 rounded-lg p-2">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-orange-600">{{ $totals['movidos_no_validos'] ?? 0 }}</p>
                        <p class="text-xs text-gray-500">No válidos</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border p-4">
                <div class="flex items-center gap-3">
                    <div class="bg-gray-100 rounded-lg p-2">
                        <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-600">{{ $totals['omitidos'] ?? 0 }}</p>
                        <p class="text-xs text-gray-500">Omitidos</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Info de PDFs no válidos --}}
        @if(isset($totals['movidos_no_validos']) && $totals['movidos_no_validos'] > 0)
            <div class="bg-orange-50 border border-orange-200 rounded-xl p-4">
                <h3 class="font-semibold text-orange-800 mb-2 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ $totals['movidos_no_validos'] }} PDF(s) movidos a carpeta de no válidos
                </h3>
                <p class="text-sm text-orange-700 mb-2">
                    Estos archivos no contienen la marca "RECEPCIÓNDEFACTURA" y fueron movidos a:
                    <code class="bg-orange-100 px-2 py-0.5 rounded">public/pdf_no_validos/</code>
                </p>
                @if(!empty($no_validos) && count($no_validos) <= 20)
                    <details class="mt-2">
                        <summary class="text-sm text-orange-600 cursor-pointer hover:text-orange-800">Ver archivos</summary>
                        <ul class="mt-2 space-y-1 text-sm text-orange-700 max-h-40 overflow-y-auto">
                            @foreach($no_validos as $archivo)
                                <li class="flex items-center gap-2">
                                    <span class="text-orange-400">•</span>
                                    {{ $archivo }}
                                </li>
                            @endforeach
                        </ul>
                    </details>
                @endif
            </div>
        @endif

        {{-- Errores --}}
        @if(!empty($errors) && count($errors) > 0)
            <div class="bg-red-50 border border-red-200 rounded-xl p-4">
                <h3 class="font-semibold text-red-800 mb-2 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    Errores encontrados ({{ count($errors) }})
                </h3>
                <ul class="space-y-1 text-sm text-red-700">
                    @foreach($errors as $error)
                        <li class="flex items-start gap-2">
                            <span class="text-red-400">•</span>
                            <span><strong>{{ basename($error['file']) }}:</strong> {{ $error['error'] }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Detalle por directorio --}}
        @if(!empty($fileCount))
            <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
                <div class="px-4 py-3 bg-gray-50 border-b">
                    <h3 class="font-semibold text-gray-900">Detalle por Directorio</h3>
                </div>
                <div class="divide-y">
                    @foreach($fileCount as $directory => $counts)
                        <div class="px-4 py-3 flex items-center justify-between flex-wrap gap-2">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                                </svg>
                                <span class="text-sm text-gray-700 font-mono">{{ str_replace(public_path(), '', $directory) ?: '/' }}</span>
                            </div>
                            <div class="flex items-center gap-4 text-sm">
                                <span class="text-gray-500">Total: <strong class="text-gray-900">{{ $counts['total'] }}</strong></span>
                                <span class="text-green-600">Procesados: <strong>{{ $counts['procesados'] }}</strong></span>
                                <span class="text-red-600">Eliminados: <strong>{{ $counts['eliminados'] }}</strong></span>
                                @if(isset($counts['no_validos']) && $counts['no_validos'] > 0)
                                    <span class="text-orange-600">No válidos: <strong>{{ $counts['no_validos'] }}</strong></span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Facturas procesadas --}}
        @if(!empty($data) && count($data) > 0)
            <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
                <div class="px-4 py-3 bg-gray-50 border-b flex items-center justify-between">
                    <h3 class="font-semibold text-gray-900">Facturas Procesadas ({{ count($data) }})</h3>
                    <a href="{{ route('charges.list') }}" class="text-sm text-blue-600 hover:text-blue-800">Ver todas →</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Factura</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Razón Social</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">NIT</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Monto Bs</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Monto $us</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                        @foreach($data as $charge)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $charge->FACTURA }}</td>
                                <td class="px-4 py-3 text-sm text-gray-500">{{ $charge->FECHA?->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ Str::limit($charge->RAZON_SOCIAL, 30) }}</td>
                                <td class="px-4 py-3 text-sm text-gray-500">{{ $charge->NIT }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900 text-right font-mono">{{ $charge->BS }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900 text-right font-mono">{{ $charge->SUS }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- Actions --}}
        <div class="flex gap-4">
            <a href="{{ route('charges.index') }}"
               class="inline-flex items-center gap-2 bg-blue-600 text-white px-6 py-2.5 rounded-lg font-medium hover:bg-blue-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                Subir más archivos
            </a>
            <a href="{{ route('charges.list') }}"
               class="inline-flex items-center gap-2 bg-gray-100 text-gray-700 px-6 py-2.5 rounded-lg font-medium hover:bg-gray-200 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                </svg>
                Ver todas las facturas
            </a>
        </div>
    </div>
@endsection
