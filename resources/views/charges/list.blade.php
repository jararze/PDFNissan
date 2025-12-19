@extends('layouts.main')

@section('title', 'Listado de Facturas')

@section('content')
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Listado de Facturas</h1>
                <p class="text-gray-600">{{ $charges->total() }} facturas registradas</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('charges.export', ['search' => $search, 'mes' => $mes]) }}"
                   class="inline-flex items-center gap-2 bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Exportar Excel
                </a>
                <a href="{{ route('charges.index') }}"
                   class="inline-flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Procesar nuevas
                </a>
            </div>
        </div>

        {{-- Filtros --}}
        <div class="bg-white rounded-xl shadow-sm border p-4">
            <form action="{{ route('charges.list') }}" method="GET" class="flex flex-col md:flex-row gap-4">
                <div class="flex-1">
                    <label for="search" class="sr-only">Buscar</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <input type="text"
                               name="search"
                               id="search"
                               value="{{ $search ?? '' }}"
                               placeholder="Buscar por razón social, NIT, factura o concepto..."
                               class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>

                <div class="w-full md:w-48">
                    <select name="mes" class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Todos los meses</option>
                        @foreach(['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'] as $month)
                            <option value="{{ $month }}" {{ ($mes ?? '') === $month ? 'selected' : '' }}>
                                {{ ucfirst($month) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit"
                        class="bg-gray-100 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-200 transition-colors font-medium">
                    Filtrar
                </button>

                @if($search || $mes)
                    <a href="{{ route('charges.list') }}"
                       class="text-gray-500 hover:text-gray-700 px-4 py-2 flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Limpiar
                    </a>
                @endif
            </form>
        </div>

        {{-- Info de filtros activos --}}
        @if($search || $mes)
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 flex items-center justify-between">
                <div class="flex items-center gap-2 text-sm text-blue-800">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    <span>
                    Filtros activos:
                    @if($search)<strong>"{{ $search }}"</strong>@endif
                        @if($search && $mes) | @endif
                        @if($mes)<strong>{{ ucfirst($mes) }}</strong>@endif
                    — {{ $charges->total() }} resultado(s)
                </span>
                </div>
                <a href="{{ route('charges.export', ['search' => $search, 'mes' => $mes]) }}"
                   class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                    Exportar estos resultados →
                </a>
            </div>
        @endif

        {{-- Tabla --}}
        <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
            @if($charges->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Factura
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Fecha
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Mes
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Razón Social
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                NIT
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Concepto
                            </th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Monto Bs
                            </th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Monto $us
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Código
                            </th>
                        </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($charges as $charge)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="text-sm font-medium text-gray-900">{{ $charge->FACTURA }}</span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                    {{ $charge->FECHA ? \Carbon\Carbon::parse($charge->FECHA)->format('d/m/Y') : '' }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ ucfirst($charge->MES) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700" title="{{ $charge->RAZON_SOCIAL }}">
                                    {{ Str::limit($charge->RAZON_SOCIAL, 25) }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 font-mono">
                                    {{ $charge->NIT }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500" title="{{ $charge->CONCEPTO }}">
                                    {{ Str::limit($charge->CONCEPTO, 30) }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 text-right font-mono">
                                    {{ number_format((float) str_replace(',', '', $charge->BS), 2) }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 text-right font-mono">
                                    {{ $charge->SUS }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                    {{ Str::limit($charge->CODIGO_QUITER, 15) }}
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Paginación --}}
                <div class="px-4 py-3 bg-gray-50 border-t">
                    {{ $charges->withQueryString()->links() }}
                </div>
            @else
                <div class="p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No hay facturas</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        @if($search || $mes)
                            No se encontraron facturas con los filtros aplicados.
                        @else
                            Comienza subiendo un archivo ZIP con facturas.
                        @endif
                    </p>
                    <div class="mt-6">
                        <a href="{{ route('charges.index') }}"
                           class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Subir facturas
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
