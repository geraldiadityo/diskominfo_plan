<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" 
      x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }"
      x-init="$watch('darkMode', val => localStorage.setItem('darkMode', val))"
      :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? config('app.name', 'Dashboard Publik') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Manrope:wght@500;600&family=Plus+Jakarta+Sans:wght@600;700&display=swap" rel="stylesheet">
    
    <!-- Alpine JS -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-[#f7f9fc] dark:bg-[#0a0a0a] text-[#191c1e] dark:text-[#e0e0e0] antialiased min-h-screen flex flex-col transition-colors duration-200" style="font-family: 'Inter', sans-serif;">
    <div class="min-h-screen flex flex-col">
        <!-- Navigation Header -->
        <nav class="bg-white dark:bg-[#121212] border-b border-[#e0e3e6] dark:border-[#1f1f1f] shadow-sm sticky top-0 z-50 transition-colors duration-200">
            <div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <div class="flex items-center gap-6">
                        <span class="text-2xl font-bold text-[#af101a] tracking-tight" style="font-family: 'Plus Jakarta Sans', sans-serif;">Dashboard Sungai Penuh</span>
                        
                        <!-- Main Menu -->
                        <div class="hidden md:flex gap-2 items-center">
                            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'text-[#af101a] dark:text-[#ffb4ab] border-b-2 border-[#af101a] dark:border-[#ffb4ab] font-bold pb-1' : 'text-[#5b403d] dark:text-[#a0a0a0] hover:text-[#af101a] dark:hover:text-white hover:bg-[#f2f4f7] dark:hover:bg-[#2a2a2a]' }} px-3 py-2 rounded-md font-semibold text-sm transition-all" style="font-family: 'Manrope', sans-serif;">Dashboard</a>
                            
                            <!-- Dropdown Kinerja -->
                            <div x-data="{ open: false }" class="relative">
                                <button @click="open = !open" @click.away="open = false" class="text-[#5b403d] dark:text-[#a0a0a0] hover:text-[#af101a] dark:hover:text-white hover:bg-[#f2f4f7] dark:hover:bg-[#2a2a2a] px-3 py-2 rounded-md font-semibold text-sm transition-all flex items-center gap-1" style="font-family: 'Manrope', sans-serif;">
                                    Kinerja
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </button>
                                <div x-show="open" style="display: none;" x-transition class="absolute left-0 mt-2 w-48 bg-white dark:bg-[#121212] border border-[#e0e3e6] dark:border-[#1f1f1f] rounded-md shadow-lg py-1 z-50">
                                    <a href="{{ route('iku.index') }}" class="block px-4 py-2 text-sm text-[#5b403d] dark:text-[#a0a0a0] hover:bg-[#f2f4f7] dark:hover:bg-[#2a2a2a] hover:text-[#af101a] dark:hover:text-white font-medium">Kinerja Utama</a>
                                    <a href="{{ route('kinerja-program.index') }}" class="block px-4 py-2 text-sm text-[#5b403d] dark:text-[#a0a0a0] hover:bg-[#f2f4f7] dark:hover:bg-[#2a2a2a] hover:text-[#af101a] dark:hover:text-white font-medium">Kinerja Program</a>
                                </div>
                            </div>

                            <a href="#" class="text-[#5b403d] dark:text-[#a0a0a0] hover:text-[#af101a] dark:hover:text-white hover:bg-[#f2f4f7] dark:hover:bg-[#2a2a2a] px-3 py-2 rounded-md font-semibold text-sm transition-all" style="font-family: 'Manrope', sans-serif;">Kebencanaan</a>
                            <a href="#" class="text-[#5b403d] dark:text-[#a0a0a0] hover:text-[#af101a] dark:hover:text-white hover:bg-[#f2f4f7] dark:hover:bg-[#2a2a2a] px-3 py-2 rounded-md font-semibold text-sm transition-all" style="font-family: 'Manrope', sans-serif;">Program Unggulan</a>
                            <a href="#" class="text-[#5b403d] dark:text-[#a0a0a0] hover:text-[#af101a] dark:hover:text-white hover:bg-[#f2f4f7] dark:hover:bg-[#2a2a2a] px-3 py-2 rounded-md font-semibold text-sm transition-all" style="font-family: 'Manrope', sans-serif;">Data Integrasi</a>
                        </div>
                    </div>
                    
                    <!-- Dark Mode Toggle -->
                    <div class="flex items-center gap-4">
                        <button @click="darkMode = !darkMode" class="p-2 rounded-full text-[#5b403d] dark:text-[#a0a0a0] hover:bg-[#f2f4f7] dark:hover:bg-[#2a2a2a] transition-colors focus:outline-none" title="Toggle Dark Mode">
                            <!-- Sun Icon (shows in dark mode) -->
                            <svg x-show="darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            <!-- Moon Icon (shows in light mode) -->
                            <svg x-show="!darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                        </button>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="flex-grow">
            {{ $slot }}
        </main>
        
        <!-- Footer -->
        <footer class="bg-white dark:bg-[#121212] border-t border-[#e0e3e6] dark:border-[#1f1f1f] mt-auto transition-colors duration-200">
            <div class="max-w-[1200px] mx-auto px-4 py-6 sm:px-6 lg:px-8 text-center text-sm text-[#5b403d] dark:text-[#a0a0a0] transition-colors">
                &copy; {{ date('Y') }} {{ config('app.name', 'Sungai Penuh Civic Intelligence') }}. All rights reserved.
            </div>
        </footer>
    </div>

    @livewireScripts
</body>
</html>
