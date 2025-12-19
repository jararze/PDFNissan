<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sistema de Facturas')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @stack('styles')
</head>
<body class="bg-gray-100 min-h-screen">
{{-- Navbar --}}
<nav class="bg-white shadow-sm border-b">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <a href="{{ route('charges.index') }}" class="flex items-center gap-2">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span class="font-semibold text-xl text-gray-800">Facturas PDF</span>
                </a>
            </div>
            <div class="flex items-center gap-4">
                <a href="{{ route('charges.index') }}"
                   class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('charges.index') ? 'bg-gray-100' : '' }}">
                    Inicio
                </a>
                <a href="{{ route('charges.list') }}"
                   class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('charges.list') ? 'bg-gray-100' : '' }}">
                    Ver Facturas
                </a>

                {{-- Usuario y Logout --}}
                <div class="flex items-center gap-3 ml-4 pl-4 border-l">
                    <span class="text-sm text-gray-600">{{ Auth::user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-sm text-red-600 hover:text-red-800">
                            Salir
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</nav>

{{-- Flash Messages --}}
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            </div>
        </div>
    @endif

    @if(session('warning'))
        <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded-lg mb-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ session('warning') }}</span>
                </div>
            </div>
        </div>
    @endif
</div>

{{-- Content --}}
<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    @yield('content')
</main>

{{-- Footer --}}
<footer class="bg-white border-t mt-auto">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <p class="text-center text-gray-500 text-sm">
            Sistema de Procesamiento de Facturas &copy; {{ date('Y') }}
        </p>
    </div>
</footer>

@stack('scripts')
</body>
</html>
