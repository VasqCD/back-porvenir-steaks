<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>El Porvenir Steaks - Disfruta de los mejores cortes</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700|instrument-sans:400,500,600,700" rel="stylesheet" />

        <!-- Styles / Scripts -->
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <style>
                /* Estilos fallback si no está configurado Vite */
                body {
                    font-family: 'Figtree', sans-serif;
                    color: #1F2937;
                    background-color: #FDFDFC;
                    margin: 0;
                    padding: 0;
                }
                
                @media (prefers-color-scheme: dark) {
                    body {
                        background-color: #0A0A0A;
                        color: #E5E7EB;
                    }
                }
            </style>
        @endif

        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                darkMode: 'class',
                theme: {
                    extend: {
                        colors: {
                            primary: {"50":"#FFF5F0","100":"#FFEADF","200":"#FFD5C0","300":"#FFB694","400":"#FF8F5E","500":"#FF6F34","600":"#FF500A","700":"#CC3B00","800":"#993100","900":"#662A00","950":"#331500"},
                            secondary: {"50":"#F7F7F7","100":"#EFEFEF","200":"#DFDFDF","300":"#CACACA","400":"#A8A8A8","500":"#888888","600":"#6D6D6D","700":"#5D5D5D","800":"#4F4F4F","900":"#454545","950":"#262626"},
                        },
                        fontFamily: {
                            sans: ['Figtree', 'sans-serif'],
                            display: ['Instrument Sans', 'sans-serif'],
                        },
                    }
                }
            }
        </script>
        <style type="text/tailwindcss">
            @layer utilities {
                .text-shadow {
                    text-shadow: 0px 2px 8px rgba(0, 0, 0, 0.25);
                }
                .section-padding {
                    @apply py-12 md:py-20;
                }
                .btn-primary {
                    @apply bg-primary-600 hover:bg-primary-700 text-white font-medium py-3 px-6 rounded-md transition-all shadow-md hover:shadow-lg;
                }
                .btn-secondary {
                    @apply bg-secondary-800 border-2 border-primary-500 hover:bg-secondary-900 text-white font-medium py-3 px-6 rounded-md transition-all shadow-md hover:shadow-lg;
                }
                .card {
                    @apply bg-white dark:bg-secondary-900 rounded-xl overflow-hidden shadow-md hover:shadow-xl transition-all;
                }
                .card-body {
                    @apply p-6;
                }
            }
        </style>
    </head>
    <body class="antialiased dark:bg-secondary-950">
        <!-- Navegación -->
        <header class="fixed top-0 left-0 right-0 z-50 bg-white dark:bg-secondary-900 shadow-md">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <div class="flex items-center">
                        <span class="text-2xl font-display font-bold text-primary-600 dark:text-primary-500">El Porvenir Steaks</span>
                    </div>
                    
                    <nav class="hidden md:flex space-x-8 text-sm">
                        <a href="#inicio" class="font-medium text-secondary-700 hover:text-primary-600 dark:text-secondary-300 dark:hover:text-primary-400 transition-colors">Inicio</a>
                        <a href="#menu" class="font-medium text-secondary-700 hover:text-primary-600 dark:text-secondary-300 dark:hover:text-primary-400 transition-colors">Menú</a>
                        <a href="#beneficios" class="font-medium text-secondary-700 hover:text-primary-600 dark:text-secondary-300 dark:hover:text-primary-400 transition-colors">Beneficios</a>
                        <a href="#testimonios" class="font-medium text-secondary-700 hover:text-primary-600 dark:text-secondary-300 dark:hover:text-primary-400 transition-colors">Testimonios</a>
                        <a href="#app" class="font-medium text-secondary-700 hover:text-primary-600 dark:text-secondary-300 dark:hover:text-primary-400 transition-colors">App</a>
                    </nav>
                    
                    @if (Route::has('login'))
                        <div class="hidden md:flex space-x-4">
                            @auth
                                <a href="{{ url('/dashboard') }}" class="btn-primary">
                                    Dashboard
                                </a>
                            @else
                                <a href="{{ route('login') }}" class="font-medium text-secondary-700 hover:text-primary-600 dark:text-secondary-300 dark:hover:text-primary-400 transition-colors">
                                    Iniciar Sesión
                                </a>

                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="btn-primary">
                                        Registrarse
                                    </a>
                                @endif
                            @endauth
                        </div>
                    @endif
                    
                    <!-- Menú móvil botón -->
                    <div class="md:hidden">
                        <button id="mobile-menu-button" type="button" class="text-secondary-700 dark:text-secondary-300 hover:text-primary-600 dark:hover:text-primary-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
                            </svg>
                        </button>
                    </div>
                </div>
                
                <!-- Menú móvil (oculto por defecto) -->
                <div id="mobile-menu" class="md:hidden hidden px-2 pt-2 pb-4 space-y-1">
                    <a href="#inicio" class="block px-3 py-2 rounded-md text-base font-medium text-secondary-700 hover:text-primary-600 hover:bg-secondary-100 dark:text-secondary-300 dark:hover:text-primary-400 dark:hover:bg-secondary-800">Inicio</a>
                    <a href="#menu" class="block px-3 py-2 rounded-md text-base font-medium text-secondary-700 hover:text-primary-600 hover:bg-secondary-100 dark:text-secondary-300 dark:hover:text-primary-400 dark:hover:bg-secondary-800">Menú</a>
                    <a href="#beneficios" class="block px-3 py-2 rounded-md text-base font-medium text-secondary-700 hover:text-primary-600 hover:bg-secondary-100 dark:text-secondary-300 dark:hover:text-primary-400 dark:hover:bg-secondary-800">Beneficios</a>
                    <a href="#testimonios" class="block px-3 py-2 rounded-md text-base font-medium text-secondary-700 hover:text-primary-600 hover:bg-secondary-100 dark:text-secondary-300 dark:hover:text-primary-400 dark:hover:bg-secondary-800">Testimonios</a>
                    <a href="#app" class="block px-3 py-2 rounded-md text-base font-medium text-secondary-700 hover:text-primary-600 hover:bg-secondary-100 dark:text-secondary-300 dark:hover:text-primary-400 dark:hover:bg-secondary-800">App</a>
                    
                    @if (Route::has('login'))
                        <div class="pt-4 pb-3 border-t border-secondary-200 dark:border-secondary-700">
                            @auth
                                <a href="{{ url('/dashboard') }}" class="block px-3 py-2 rounded-md text-base font-medium text-white bg-primary-600 hover:bg-primary-700">Dashboard</a>
                            @else
                                <a href="{{ route('login') }}" class="block px-3 py-2 rounded-md text-base font-medium text-secondary-700 hover:text-primary-600 hover:bg-secondary-100 dark:text-secondary-300 dark:hover:text-primary-400 dark:hover:bg-secondary-800">Iniciar Sesión</a>
                                
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="block mt-2 px-3 py-2 rounded-md text-base font-medium text-white bg-primary-600 hover:bg-primary-700">Registrarse</a>
                                @endif
                            @endauth
                        </div>
                    @endif
                </div>
            </div>
        </header>

        <!-- Hero Section -->
        <section id="inicio" class="pt-24 pb-20 md:pb-28 md:pt-32 relative bg-secondary-100 dark:bg-secondary-900 overflow-hidden">
            <div class="absolute inset-0 z-0">
                <img src="https://images.ctfassets.net/trvmqu12jq2l/6FV4Opt7wUyR91t2FXyOIr/f32972fce10fc87585e831b334ea17ef/header.jpg?q=70&w=1208&h=1080&f=faces&fit=fill" 
                     alt="Background" 
                     class="object-cover object-center w-full h-full opacity-30 dark:opacity-20">
                <div class="absolute inset-0 bg-gradient-to-r from-secondary-900/60 to-secondary-900/40 dark:from-secondary-950/90 dark:to-secondary-950/80"></div>
            </div>
            
            <div class="container mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="max-w-3xl mx-auto text-center">
                    <h1 class="text-4xl md:text-5xl lg:text-6xl font-display font-bold text-secondary-900 dark:text-white leading-tight mb-6 text-shadow">
                        Los Mejores Cortes de Carne a Tu Puerta
                    </h1>
                    <p class="text-xl text-secondary-800 dark:text-secondary-200 mb-8 text-shadow">
                        Disfruta de la experiencia premium de carne de la mejor calidad, preparada por expertos y entregada directamente a tu ubicación.
                    </p>
                    <div class="flex flex-col sm:flex-row justify-center gap-4">
                        <a href="#menu" class="btn-primary">
                            Ver nuestro menú
                        </a>
                        <a href="#app" class="btn-secondary">
                            Descargar App
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="absolute bottom-0 w-full">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 150" class="text-white dark:text-secondary-950 fill-current">
                    <path d="M0,128L48,117.3C96,107,192,85,288,90.7C384,96,480,128,576,133.3C672,139,768,117,864,101.3C960,85,1056,75,1152,80C1248,85,1344,107,1392,117.3L1440,128L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path>
                </svg>
            </div>
        </section>

        <!-- Menu Destacado -->
        <section id="menu" class="section-padding bg-white dark:bg-secondary-950">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h2 class="text-3xl md:text-4xl font-display font-bold mb-4 text-secondary-900 dark:text-white">Nuestros Cortes Destacados</h2>
                    <p class="text-lg text-secondary-700 dark:text-secondary-300 max-w-3xl mx-auto">
                        Seleccionamos los mejores cortes de carne para brindarte una experiencia gastronómica única.
                    </p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <!-- Producto 1 -->
                    <div class="card group">
                        <div class="relative overflow-hidden">
                            <img src="https://www.batihanvadi.com/content-image/galleries/auto-auto/113.jpg" alt="T-Bone Steak" class="h-64 w-full object-cover transform group-hover:scale-105 transition-transform duration-300">
                            <div class="absolute top-2 right-2 bg-primary-600 text-white rounded-full px-3 py-1 text-sm">
                                Premium
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="text-xl font-bold text-secondary-900 dark:text-white">T-Bone Steak</h3>
                                    <p class="text-secondary-700 dark:text-secondary-300 mt-1">Corte premium de 16oz</p>
                                </div>
                                <div class="text-primary-600 dark:text-primary-500 font-bold">
                                    L 350.00
                                </div>
                            </div>
                            <div class="mt-4">
                                <button class="w-full text-center py-2 border-2 border-primary-600 text-primary-600 dark:border-primary-500 dark:text-primary-500 rounded-md font-medium hover:bg-primary-600 hover:text-white dark:hover:bg-primary-500 dark:hover:text-white transition-colors">
                                    Agregar al pedido
                                </button>
                            </div>
                        </div>
                        <p class="text-secondary-700 dark:text-secondary-300">
                            "¡Los mejores cortes que he probado! La carne llegó en perfecto estado y la app es muy fácil de usar. El repartidor fue muy amable."
                        </p>
                        <div class="mt-4 text-primary-600 dark:text-primary-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                        </div>
                    </div>

                    <!-- Producto 2 -->
                    <div class="card group">
                        <div class="relative overflow-hidden">
                            <img src="https://images.ctfassets.net/trvmqu12jq2l/6FV4Opt7wUyR91t2FXyOIr/f32972fce10fc87585e831b334ea17ef/header.jpg?q=70&w=1208&h=1080&f=faces&fit=fill" alt="Ribeye Steak" class="h-64 w-full object-cover transform group-hover:scale-105 transition-transform duration-300">
                        </div>
                        <div class="card-body">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="text-xl font-bold text-secondary-900 dark:text-white">Ribeye Steak</h3>
                                    <p class="text-secondary-700 dark:text-secondary-300 mt-1">Corte jugoso de 12oz</p>
                                </div>
                                <div class="text-primary-600 dark:text-primary-500 font-bold">
                                    L 280.00
                                </div>
                            </div>
                            <div class="mt-4">
                                <button class="w-full text-center py-2 border-2 border-primary-600 text-primary-600 dark:border-primary-500 dark:text-primary-500 rounded-md font-medium hover:bg-primary-600 hover:text-white dark:hover:bg-primary-500 dark:hover:text-white transition-colors">
                                    Agregar al pedido
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Producto 3 -->
                    <div class="card group">
                        <div class="relative overflow-hidden">
                            <img src="https://www.batihanvadi.com/content-image/galleries/auto-auto/113.jpg" alt="New York Steak" class="h-64 w-full object-cover transform group-hover:scale-105 transition-transform duration-300">
                            <div class="absolute top-2 right-2 bg-secondary-600 text-white rounded-full px-3 py-1 text-sm">
                                Popular
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="text-xl font-bold text-secondary-900 dark:text-white">New York Steak</h3>
                                    <p class="text-secondary-700 dark:text-secondary-300 mt-1">Corte fino de 10oz</p>
                                </div>
                                <div class="text-primary-600 dark:text-primary-500 font-bold">
                                    L 260.00
                                </div>
                            </div>
                            <div class="mt-4">
                                <button class="w-full text-center py-2 border-2 border-primary-600 text-primary-600 dark:border-primary-500 dark:text-primary-500 rounded-md font-medium hover:bg-primary-600 hover:text-white dark:hover:bg-primary-500 dark:hover:text-white transition-colors">
                                    Agregar al pedido
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-12">
                    <a href="#" class="btn-primary inline-block">
                        Ver todo el menú
                    </a>
                </div>
            </div>
        </section>

        <!-- Sección de Beneficios -->
        <section id="beneficios" class="section-padding bg-secondary-100 dark:bg-secondary-900 relative">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h2 class="text-3xl md:text-4xl font-display font-bold mb-4 text-secondary-900 dark:text-white">¿Por qué elegir El Porvenir Steaks?</h2>
                    <p class="text-lg text-secondary-700 dark:text-secondary-300 max-w-3xl mx-auto">
                        Te ofrecemos una experiencia completa con beneficios que marcan la diferencia
                    </p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                    <!-- Beneficio 1 -->
                    <div class="bg-white dark:bg-secondary-800 rounded-xl p-6 shadow-md hover:shadow-xl transition-shadow">
                        <div class="text-center mb-4">
                            <div class="w-16 h-16 bg-primary-100 dark:bg-primary-900/30 rounded-full flex items-center justify-center mx-auto">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-primary-600 dark:text-primary-500" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
                                    <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>
                        <h3 class="text-xl font-bold text-secondary-900 dark:text-white text-center">Menú Premium</h3>
                        <p class="text-secondary-700 dark:text-secondary-300 text-center mt-2">
                            Selección de cortes de la más alta calidad, preparados por chefs profesionales.
                        </p>
                    </div>
                    
                    <!-- Beneficio 2 -->
                    <div class="bg-white dark:bg-secondary-800 rounded-xl p-6 shadow-md hover:shadow-xl transition-shadow">
                        <div class="text-center mb-4">
                            <div class="w-16 h-16 bg-primary-100 dark:bg-primary-900/30 rounded-full flex items-center justify-center mx-auto">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-primary-600 dark:text-primary-500" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" />
                                    <path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1v-5h2v5a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H19a1 1 0 001-1v-5a1 1 0 00-.3-.7l-4-4A1 1 0 0015 5h-2a1 1 0 00-1-1H3z" />
                                </svg>
                            </div>
                        </div>
                        <h3 class="text-xl font-bold text-secondary-900 dark:text-white text-center">Entrega Rápida</h3>
                        <p class="text-secondary-700 dark:text-secondary-300 text-center mt-2">
                            Seguimiento en tiempo real de tu pedido hasta la puerta de tu casa.
                        </p>
                    </div>
                    
                    <!-- Beneficio 3 -->
                    <div class="bg-white dark:bg-secondary-800 rounded-xl p-6 shadow-md hover:shadow-xl transition-shadow">
                        <div class="text-center mb-4">
                            <div class="w-16 h-16 bg-primary-100 dark:bg-primary-900/30 rounded-full flex items-center justify-center mx-auto">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-primary-600 dark:text-primary-500" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>
                        <h3 class="text-xl font-bold text-secondary-900 dark:text-white text-center">Calidad Garantizada</h3>
                        <p class="text-secondary-700 dark:text-secondary-300 text-center mt-2">
                            Satisfacción garantizada o te devolvemos tu dinero sin complicaciones.
                        </p>
                    </div>
                    
                    <!-- Beneficio 4 -->
                    <div class="bg-white dark:bg-secondary-800 rounded-xl p-6 shadow-md hover:shadow-xl transition-shadow">
                        <div class="text-center mb-4">
                            <div class="w-16 h-16 bg-primary-100 dark:bg-primary-900/30 rounded-full flex items-center justify-center mx-auto">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-primary-600 dark:text-primary-500" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z" />
                                </svg>
                            </div>
                        </div>
                        <h3 class="text-xl font-bold text-secondary-900 dark:text-white text-center">Notificaciones</h3>
                        <p class="text-secondary-700 dark:text-secondary-300 text-center mt-2">
                            Te mantenemos informado en cada etapa de preparación y entrega de tu pedido.
                        </p>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Sección Descargar App -->
        <section id="app" class="section-padding bg-primary-600 relative">
            <div class="absolute inset-0 overflow-hidden">
                <img src="https://www.batihanvadi.com/content-image/galleries/auto-auto/113.jpg" alt="Background" class="object-cover w-full h-full opacity-10">
            </div>
            <div class="container mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="flex flex-col md:flex-row items-center">
                    <div class="w-full md:w-1/2 mb-8 md:mb-0">
                        <h2 class="text-3xl md:text-4xl font-display font-bold mb-4 text-white">Descarga nuestra aplicación</h2>
                        <p class="text-lg text-white/90 mb-6">
                            Realiza tus pedidos de forma rápida y sencilla, rastrea tus entregas en tiempo real y recibe notificaciones con la app de El Porvenir Steaks.
                        </p>
                        <ul class="mb-8 space-y-4">
                            <li class="flex items-center text-white/90">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-white" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                Menú completo con fotos y descripciones
                            </li>
                            <li class="flex items-center text-white/90">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-white" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                Seguimiento en tiempo real de tu pedido
                            </li>
                            <li class="flex items-center text-white/90">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-white" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                Notificaciones sobre el estado de tu pedido
                            </li>
                            <li class="flex items-center text-white/90">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-white" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                Guarda tus direcciones favoritas
                            </li>
                        </ul>
                        <div class="flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4">
                            <a href="#" class="bg-white hover:bg-secondary-100 text-primary-600 font-medium py-3 px-6 rounded-md shadow-md hover:shadow-lg transition-all flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M17.5185 12.0001C17.5146 10.812 18.0615 9.76483 19.0048 9.06742C18.3288 8.09412 17.2733 7.463 16.0643 7.39699C14.8101 7.25779 13.7129 8.084 13.1348 8.084C12.5231 8.084 11.5905 7.41591 10.5869 7.43814C9.18061 7.47083 7.90216 8.31111 7.25795 9.625C5.8936 12.3129 6.93443 16.2474 8.26233 18.3015C8.91205 19.3078 9.66455 20.4344 10.638 20.3967C11.5879 20.3562 11.9404 19.7559 13.0836 19.7559C14.2054 19.7559 14.5328 20.3967 15.5335 20.3738C16.5614 20.3562 17.2149 19.3569 17.8398 18.3397C18.3366 17.5687 18.7127 16.7131 18.9574 15.8126C17.7913 15.2966 17.5188 14.0025 17.5185 12.0001Z" />
                                    <path d="M15.1222 6.20014C15.6894 5.51355 15.9989 4.64387 15.9946 3.75101C15.123 3.79909 14.304 4.15371 13.6953 4.7481C13.098 5.32762 12.7598 6.12709 12.766 6.98444C13.6414 6.99212 14.5564 6.73632 15.1222 6.20014Z" />
                                </svg>
                                <span>App Store</span>
                            </a>
                            <a href="#" class="bg-white hover:bg-secondary-100 text-primary-600 font-medium py-3 px-6 rounded-md shadow-md hover:shadow-lg transition-all flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M13.0007 5.5H11.0007V8.5H8.00073V10.5H11.0007V13.5H13.0007V10.5H16.0007V8.5H13.0007V5.5ZM12.0007 2C6.48073 2 2.00073 6.48 2.00073 12C2.00073 17.52 6.48073 22 12.0007 22C17.5207 22 22.0007 17.52 22.0007 12C22.0007 6.48 17.5207 2 12.0007 2ZM12.0007 20C7.59073 20 4.00073 16.41 4.00073 12C4.00073 7.59 7.59073 4 12.0007 4C16.4107 4 20.0007 7.59 20.0007 12C20.0007 16.41 16.4107 20 12.0007 20Z" />
                                </svg>
                                <span>Google Play</span>
                            </a>
                        </div>
                    </div>
                </div>
                </div>
            </div>
        </section>

        

        <!-- Testimonios -->
        <section id="testimonios" class="section-padding bg-white dark:bg-secondary-950">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h2 class="text-3xl md:text-4xl font-display font-bold mb-4 text-secondary-900 dark:text-white">Lo que dicen nuestros clientes</h2>
                    <p class="text-lg text-secondary-700 dark:text-secondary-300 max-w-3xl mx-auto">
                        Personas que ya han disfrutado de la experiencia El Porvenir Steaks
                    </p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <!-- Testimonio 1 -->
                    <div class="bg-secondary-50 dark:bg-secondary-800 p-6 rounded-xl shadow-md">
                        <div class="flex items-center mb-4">
                            <div class="mr-4">
                                <div class="w-12 h-12 bg-primary-100 dark:bg-primary-900/30 rounded-full flex items-center justify-center">
                                    <span class="text-primary-600 dark:text-primary-500 font-bold">LP</span>
                                </div>
                            </div>
                            <div>
                                <h4 class="font-bold text-secondary-900 dark:text-white">Laura Padilla</h4>
                                <p class="text-sm text-secondary-500 dark:text-secondary-400">Cliente frecuente</p>
                            </div>
                        </div>
                        <p class="text-secondary-700 dark:text-secondary-300">
                            "La calidad de la carne es excepcional. Nunca había probado un ribeye tan jugoso y tierno. ¡Definitivamente volveré por más!"
                        </p>
                    </div>
                    <!-- Testimonio 2 -->
                    <div class="bg-secondary-50 dark:bg-secondary-800 p-6 rounded-xl shadow-md">
                        <div class="flex items-center mb-4">
                            <div class="mr-4">
                                <div class="w-12 h-12 bg-primary-100 dark:bg-primary-900/30 rounded-full flex items-center justify-center">
                                    <span class="text-primary-600 dark:text-primary-500 font-bold">CM</span>
                                </div>
                            </div>
                            <div>
                                <h4 class="font-bold text-secondary-900 dark:text-white">Carlos Mendoza</h4>
                                <p class="text-sm text-secondary-500 dark:text-secondary-400">Nuevo cliente</p>
                            </div>
                        </div>
                        <p class="text-secondary-700 dark:text-secondary-300">
                            "Me encantó poder seguir en tiempo real la entrega de mi pedido. La calidad de la carne superó mis expectativas. ¡Totalmente recomendado!"
                        </p>
                        <div class="mt-4 text-primary-600 dark:text-primary-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                        </div>
                    </div>
                    
                    <!-- Testimonio 3 -->
                    <div class="bg-secondary-50 dark:bg-secondary-800 p-6 rounded-xl shadow-md">
                        <div class="flex items-center mb-4">
                            <div class="mr-4">
                                <div class="w-12 h-12 bg-primary-100 dark:bg-primary-900/30 rounded-full flex items-center justify-center">
                                    <span class="text-primary-600 dark:text-primary-500 font-bold">MR</span>
                                </div>
                            </div>
                            <div>
                                <h4 class="font-bold text-secondary-900 dark:text-white">María Rodríguez</h4>
                                <p class="text-sm text-secondary-500 dark:text-secondary-400">Cliente frecuente</p>
                            </div>
                        </div>
                        <p class="text-secondary-700 dark:text-secondary-300">
                            "Excelente servicio, rápido y eficiente. Las notificaciones me mantuvieron informada en todo momento. La app es muy intuitiva y fácil de usar."
                        </p>
                        <div class="mt-4 text-primary-600 dark:text-primary-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block text-secondary-300 dark:text-secondary-600" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Footer -->
        <footer class="bg-secondary-900 dark:bg-secondary-950 text-white">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
                    <div class="col-span-1 md:col-span-1">
                        <h3 class="text-xl font-display font-bold mb-4 text-primary-500">El Porvenir Steaks</h3>
                        <p class="text-secondary-300 mb-4">Los mejores cortes de carne premium entregados directamente en la puerta de tu casa.</p>
                        <div class="flex space-x-4">
                            <a href="#" class="text-secondary-300 hover:text-primary-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M22 12C22 6.48 17.52 2 12 2C6.48 2 2 6.48 2 12C2 16.84 5.44 20.87 10 21.8V15H8V12H10V9.5C10 7.57 11.57 6 13.5 6H16V9H14C13.45 9 13 9.45 13 10V12H16V15H13V21.95C18.05 21.45 22 17.19 22 12Z" />
                                </svg>
                            </a>
                            <a href="#" class="text-secondary-300 hover:text-primary-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M7.8 2H16.2C19.4 2 22 4.6 22 7.8V16.2C22 19.4 19.4 22 16.2 22H7.8C4.6 22 2 19.4 2 16.2V7.8C2 4.6 4.6 2 7.8 2ZM16.2 20C18.3 20 20 18.3 20 16.2V7.8C20 5.7 18.3 4 16.2 4H7.8C5.7 4 4 5.7 4 7.8V16.2C4 18.3 5.7 20 7.8 20H16.2ZM12 7C14.8 7 17 9.2 17 12C17 14.8 14.8 17 12 17C9.2 17 7 14.8 7 12C7 9.2 9.2 7 12 7ZM12 15C13.7 15 15 13.7 15 12C15 10.3 13.7 9 12 9C10.3 9 9 10.3 9 12C9 13.7 10.3 15 12 15Z" />
                                </svg>
                            </a>
                            <a href="#" class="text-secondary-300 hover:text-primary-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M22.46 6C21.69 6.35 20.86 6.58 20 6.69C20.88 6.16 21.56 5.32 21.88 4.31C21.05 4.81 20.13 5.16 19.16 5.36C18.37 4.5 17.26 4 16 4C13.65 4 11.73 5.92 11.73 8.29C11.73 8.63 11.77 8.96 11.84 9.27C8.28 9.09 5.11 7.38 3 4.79C2.63 5.42 2.42 6.16 2.42 6.94C2.42 8.43 3.17 9.75 4.33 10.5C3.62 10.5 2.96 10.3 2.38 10V10.03C2.38 12.11 3.86 13.85 5.82 14.24C5.19 14.41 4.53 14.44 3.9 14.31C4.16 15.15 4.68 15.88 5.39 16.39C6.09 16.9 6.94 17.15 7.81 17.12C6.07 18.44 3.93 19.13 1.79 19.12C1.45 19.12 1.1 19.1 0.76 19.06C2.94 20.42 5.47 21.13 8.04 21.13C16 21.13 20.33 14.5 20.33 8.74C20.33 8.55 20.33 8.36 20.32 8.17C21.16 7.56 21.88 6.8 22.46 5.94V6Z" />
                                </svg>
                            </a>
                        </div>
                    </div>
                    <div>
                        <h4 class="text-lg font-bold mb-4">Enlaces rápidos</h4>
                        <ul class="space-y-2">
                            <li><a href="#inicio" class="text-secondary-300 hover:text-primary-500 transition-colors">Inicio</a></li>
                            <li><a href="#menu" class="text-secondary-300 hover:text-primary-500 transition-colors">Menú</a></li>
                            <li><a href="#beneficios" class="text-secondary-300 hover:text-primary-500 transition-colors">Beneficios</a></li>
                            <li><a href="#testimonios" class="text-secondary-300 hover:text-primary-500 transition-colors">Testimonios</a></li>
                            <li><a href="#app" class="text-secondary-300 hover:text-primary-500 transition-colors">App</a></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="text-lg font-bold mb-4">Contacto</h4>
                        <ul class="space-y-2">
                            <li class="flex items-start">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 mt-0.5 text-primary-500" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                                </svg>
                                <span class="text-secondary-300">Av. Principal #123, Tegucigalpa, Honduras</span>
                            </li>
                            <li class="flex items-start">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 mt-0.5 text-primary-500" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z" />
                                </svg>
                                <span class="text-secondary-300">+504 2222-3333</span>
                            </li>
                            <li class="flex items-start">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 mt-0.5 text-primary-500" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                                    <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                                </svg>
                                <span class="text-secondary-300">info@elporvenirsteaks.com</span>
                            </li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="text-lg font-bold mb-4">Horario</h4>
                        <ul class="space-y-2">
                            <li class="text-secondary-300">Lunes - Viernes: 10:00 AM - 10:00 PM</li>
                            <li class="text-secondary-300">Sábado - Domingo: 11:00 AM - 11:00 PM</li>
                        </ul>
                    </div>
                </div>
                <div class="border-t border-secondary-800 pt-8">
                    <p class="text-center text-secondary-400">&copy; {{ date('Y') }} El Porvenir Steaks. Todos los derechos reservados.</p>
                </div>
            </div>
        </footer>

        <!-- Script para el menú móvil -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const mobileMenuButton = document.getElementById('mobile-menu-button');
                const mobileMenu = document.getElementById('mobile-menu');
                
                mobileMenuButton.addEventListener('click', function() {
                    mobileMenu.classList.toggle('hidden');
                });
                
                // Cerrar menú al hacer clic en un enlace
                document.querySelectorAll('#mobile-menu a').forEach(link => {
                    link.addEventListener('click', function() {
                        mobileMenu.classList.add('hidden');
                    });
                });
            });
        </script>
    </body>
</html>