@extends('layouts.main')
@section('title', 'Procesar Facturas')

@section('content')
    <div class="space-y-6">
        {{-- Header --}}
        <div class="text-center">
            <h1 class="text-3xl font-bold text-gray-900">Procesador de Facturas PDF</h1>
            <p class="mt-2 text-gray-600">Sube un archivo ZIP con tus facturas en PDF para procesarlas automáticamente</p>
        </div>

        <div class="grid md:grid-cols-2 gap-6">
            {{-- Card Upload --}}
            <div class="bg-white rounded-xl shadow-sm border p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                    Subir archivo ZIP
                </h2>

                <form action="{{ route('charges.upload') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
                    @csrf

                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-blue-400 transition-colors" id="dropZone">
                        <input type="file"
                               name="zip_file"
                               id="zip_file"
                               accept=".zip"
                               class="hidden"
                               required>

                        <label for="zip_file" class="cursor-pointer">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p class="mt-2 text-sm text-gray-600">
                                <span class="font-medium text-blue-600 hover:text-blue-500">Haz clic para seleccionar</span>
                                o arrastra un archivo ZIP
                            </p>
                            <p class="text-xs text-gray-500 mt-1">ZIP hasta 100MB</p>
                        </label>

                        <p id="fileName" class="mt-3 text-sm font-medium text-blue-600 hidden"></p>
                    </div>

                    @error('zip_file')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror

                    <button type="submit"
                            id="submitBtn"
                            class="mt-4 w-full bg-blue-600 text-white py-2.5 px-4 rounded-lg font-medium hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                            disabled>
                        <svg id="spinner" class="animate-spin h-5 w-5 hidden" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span id="btnText">Subir ZIP</span>
                    </button>
                </form>
            </div>

            {{-- Card Procesar --}}
            <div class="bg-white rounded-xl shadow-sm border p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                    Procesar PDFs
                </h2>

                <div class="space-y-4">
                    @if($hasPendingFiles)
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                            <div class="flex items-center gap-3">
                                <div class="flex-shrink-0">
                                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium text-green-800">{{ $pendingCount }} archivo(s) PDF pendientes</p>
                                    <p class="text-sm text-green-600">Listos para ser procesados</p>
                                </div>
                            </div>
                        </div>

                        <a href="{{ route('charges.generate') }}"
                           class="block w-full bg-green-600 text-white py-2.5 px-4 rounded-lg font-medium hover:bg-green-700 transition-colors text-center">
                            Procesar Facturas
                        </a>
                    @else
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                            <div class="flex items-center gap-3">
                                <div class="flex-shrink-0">
                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-700">No hay archivos pendientes</p>
                                    <p class="text-sm text-gray-500">Sube un archivo ZIP para comenzar</p>
                                </div>
                            </div>
                        </div>

                        <button disabled
                                class="w-full bg-gray-300 text-gray-500 py-2.5 px-4 rounded-lg font-medium cursor-not-allowed">
                            Procesar Facturas
                        </button>
                    @endif
                </div>
            </div>
        </div>

        {{-- Info Cards --}}
        <div class="grid md:grid-cols-3 gap-4">
            <div class="bg-white rounded-lg shadow-sm border p-4">
                <div class="flex items-center gap-3">
                    <div class="bg-blue-100 rounded-lg p-2">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900">Paso 1</p>
                        <p class="text-xs text-gray-500">Sube tu archivo ZIP</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border p-4">
                <div class="flex items-center gap-3">
                    <div class="bg-green-100 rounded-lg p-2">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900">Paso 2</p>
                        <p class="text-xs text-gray-500">Procesa las facturas</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border p-4">
                <div class="flex items-center gap-3">
                    <div class="bg-purple-100 rounded-lg p-2">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900">Paso 3</p>
                        <p class="text-xs text-gray-500">Revisa los resultados</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const fileInput = document.getElementById('zip_file');
            const fileName = document.getElementById('fileName');
            const submitBtn = document.getElementById('submitBtn');
            const btnText = document.getElementById('btnText');
            const spinner = document.getElementById('spinner');
            const form = document.getElementById('uploadForm');
            const dropZone = document.getElementById('dropZone');

            // Cuando selecciona archivo
            fileInput.addEventListener('change', function() {
                if (this.files.length > 0) {
                    fileName.textContent = this.files[0].name;
                    fileName.classList.remove('hidden');
                    submitBtn.disabled = false;
                    dropZone.classList.add('border-blue-400', 'bg-blue-50');
                } else {
                    fileName.classList.add('hidden');
                    submitBtn.disabled = true;
                    dropZone.classList.remove('border-blue-400', 'bg-blue-50');
                }
            });

            // Cuando envía el formulario
            form.addEventListener('submit', function() {
                submitBtn.disabled = true;
                btnText.textContent = 'Subiendo...';
                spinner.classList.remove('hidden');
            });
        });
    </script>
@endsection
