<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @php $title = 'DM Forge — AI-Powered D&D Campaign Manager'; @endphp
        @include('partials.head')

        <style>
            .hero-rune-pattern {
                background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.025'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            }

            @keyframes float {
                0%, 100% { transform: translateY(0px) rotate(0deg); }
                50% { transform: translateY(-12px) rotate(4deg); }
            }

            @keyframes float-reverse {
                0%, 100% { transform: translateY(0px) rotate(0deg); }
                50% { transform: translateY(-8px) rotate(-5deg); }
            }

            @keyframes glow-pulse {
                0%, 100% { opacity: 0.4; }
                50% { opacity: 0.8; }
            }

            .float-anim { animation: float 7s ease-in-out infinite; }
            .float-anim-reverse { animation: float-reverse 9s ease-in-out infinite; }
            .glow-pulse { animation: glow-pulse 4s ease-in-out infinite; }

            html { scroll-behavior: smooth; }
        </style>
    </head>
    <body class="min-h-screen bg-zinc-950 text-white antialiased">

        {{-- ============================================================ --}}
        {{-- NAVIGATION --}}
        {{-- ============================================================ --}}
        <nav
            x-data="{ mobileOpen: false, scrolled: false }"
            x-on:scroll.window="scrolled = (window.scrollY > 20)"
            :class="scrolled ? 'bg-zinc-950/95 backdrop-blur-md border-b border-zinc-800/60 shadow-lg shadow-black/20' : 'bg-transparent border-b border-transparent'"
            class="fixed top-0 inset-x-0 z-50 transition-all duration-300"
        >
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">

                    {{-- Logo --}}
                    <a href="{{ route('home') }}" class="flex items-center gap-2.5 group">
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-amber-500/10 border border-amber-500/30 group-hover:bg-amber-500/20 transition-colors duration-200">
                            <svg class="h-5 w-5 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <polygon points="12,2 22,8 22,16 12,22 2,16 2,8" />
                                <polyline points="2,8 12,12 22,8" />
                                <line x1="12" y1="12" x2="12" y2="22" />
                                <line x1="2" y1="8" x2="12" y2="5" />
                                <line x1="22" y1="8" x2="12" y2="5" />
                            </svg>
                        </div>
                        <span class="text-lg font-semibold text-white tracking-tight">DM Forge</span>
                    </a>

                    {{-- Desktop nav links --}}
                    <div class="hidden md:flex items-center gap-8">
                        <a href="#features" class="text-sm text-zinc-400 hover:text-white transition-colors duration-150">Features</a>
                        <a href="#ai" class="text-sm text-zinc-400 hover:text-white transition-colors duration-150">AI Tools</a>
                        <a href="#how-it-works" class="text-sm text-zinc-400 hover:text-white transition-colors duration-150">How It Works</a>
                    </div>

                    {{-- Auth buttons --}}
                    <div class="flex items-center gap-3">
                        @if (Route::has('login'))
                            @auth
                                <a
                                    href="{{ url('/dashboard') }}"
                                    class="hidden sm:inline-flex items-center gap-1.5 rounded-lg px-4 py-2 text-sm font-medium text-zinc-300 hover:text-white hover:bg-zinc-800 transition-colors duration-150"
                                >
                                    Dashboard
                                </a>
                            @else
                                <a
                                    href="{{ route('login') }}"
                                    class="hidden sm:inline-flex items-center rounded-lg px-4 py-2 text-sm font-medium text-zinc-400 hover:text-white transition-colors duration-150"
                                >
                                    Log in
                                </a>

                                @if (Route::has('register'))
                                    <a
                                        href="{{ route('register') }}"
                                        class="inline-flex items-center rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-zinc-950 hover:bg-amber-400 transition-colors duration-150"
                                    >
                                        Get Started
                                    </a>
                                @endif
                            @endauth
                        @endif

                        {{-- Mobile hamburger --}}
                        <button
                            class="md:hidden p-2 rounded-lg text-zinc-400 hover:text-white hover:bg-zinc-800 transition-colors duration-150"
                            x-on:click="mobileOpen = !mobileOpen"
                            aria-label="Toggle menu"
                        >
                            <svg x-show="!mobileOpen" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                            <svg x-show="mobileOpen" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" x-cloak>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Mobile menu --}}
            <div x-show="mobileOpen" x-collapse class="md:hidden border-t border-zinc-800 bg-zinc-950/98 backdrop-blur-md">
                <div class="px-4 py-4 flex flex-col gap-1">
                    <a href="#features" class="px-3 py-2 rounded-lg text-sm text-zinc-400 hover:text-white hover:bg-zinc-800 transition-colors" x-on:click="mobileOpen = false">Features</a>
                    <a href="#ai" class="px-3 py-2 rounded-lg text-sm text-zinc-400 hover:text-white hover:bg-zinc-800 transition-colors" x-on:click="mobileOpen = false">AI Tools</a>
                    <a href="#how-it-works" class="px-3 py-2 rounded-lg text-sm text-zinc-400 hover:text-white hover:bg-zinc-800 transition-colors" x-on:click="mobileOpen = false">How It Works</a>
                    @auth
                        <a href="{{ url('/dashboard') }}" class="mt-2 px-3 py-2 rounded-lg text-sm font-medium text-amber-400 hover:bg-zinc-800 transition-colors">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="mt-2 px-3 py-2 rounded-lg text-sm text-zinc-300 hover:bg-zinc-800 transition-colors">Log in</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="mt-1 px-3 py-2 rounded-lg text-sm font-semibold bg-amber-500 text-zinc-950 text-center hover:bg-amber-400 transition-colors">Get Started Free</a>
                        @endif
                    @endauth
                </div>
            </div>
        </nav>


        {{-- ============================================================ --}}
        {{-- HERO --}}
        {{-- ============================================================ --}}
        <section class="relative min-h-screen flex items-center overflow-hidden hero-rune-pattern">

            {{-- Background gradient layers --}}
            <div class="absolute inset-0 bg-linear-to-b from-zinc-950 via-zinc-950 to-zinc-900 pointer-events-none"></div>

            {{-- Radial glow orbs --}}
            <div class="absolute top-1/3 left-1/2 -translate-x-1/2 -translate-y-1/2 w-175 h-125 rounded-full bg-amber-500/5 blur-3xl pointer-events-none glow-pulse"></div>
            <div class="absolute top-1/4 left-1/4 w-87.5 h-87.5 rounded-full bg-violet-500/4 blur-3xl pointer-events-none"></div>
            <div class="absolute bottom-1/4 right-1/4 w-75 h-75 rounded-full bg-amber-600/4 blur-2xl pointer-events-none"></div>

            {{-- Floating D20 (top-right decoration) --}}
            <div class="absolute top-28 right-6 lg:right-20 opacity-[0.07] float-anim pointer-events-none select-none">
                <svg class="w-36 h-36 text-amber-400" viewBox="0 0 100 100" fill="none" stroke="currentColor" stroke-width="1">
                    <polygon points="50,5 95,27.5 95,72.5 50,95 5,72.5 5,27.5" />
                    <polyline points="5,27.5 50,50 95,27.5" />
                    <line x1="50" y1="50" x2="50" y2="95" />
                    <line x1="5" y1="27.5" x2="50" y2="15" />
                    <line x1="95" y1="27.5" x2="50" y2="15" />
                    <text x="50" y="40" text-anchor="middle" font-size="18" fill="currentColor" stroke="none" font-weight="bold" font-family="serif">20</text>
                </svg>
            </div>

            {{-- Floating rune circle (bottom-left decoration) --}}
            <div class="absolute bottom-28 left-6 lg:left-16 opacity-[0.06] float-anim-reverse pointer-events-none select-none">
                <svg class="w-24 h-24 text-violet-400" viewBox="0 0 80 80" fill="none" stroke="currentColor" stroke-width="1.2">
                    <circle cx="40" cy="40" r="36" />
                    <circle cx="40" cy="40" r="24" />
                    <circle cx="40" cy="40" r="6" />
                    <line x1="40" y1="4" x2="40" y2="16" />
                    <line x1="40" y1="64" x2="40" y2="76" />
                    <line x1="4" y1="40" x2="16" y2="40" />
                    <line x1="64" y1="40" x2="76" y2="40" />
                    <line x1="12" y1="12" x2="20" y2="20" />
                    <line x1="60" y1="60" x2="68" y2="68" />
                    <line x1="68" y1="12" x2="60" y2="20" />
                    <line x1="20" y1="60" x2="12" y2="68" />
                </svg>
            </div>

            {{-- Main hero content --}}
            <div class="relative z-10 mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 py-32 lg:py-40 text-center">

                {{-- Badge chip --}}
                <div class="mb-8 inline-flex items-center gap-2 rounded-full border border-amber-500/30 bg-amber-500/10 px-4 py-1.5 text-sm font-medium text-amber-400">
                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                    </svg>
                    AI-Powered D&amp;D Campaign Manager
                </div>

                {{-- Main headline --}}
                <h1 class="mb-6 text-5xl sm:text-6xl lg:text-7xl font-bold tracking-tight text-white leading-[1.1]">
                    Stop grinding through<br class="hidden sm:block" />
                    <span class="bg-linear-to-r from-amber-400 via-orange-400 to-amber-500 bg-clip-text text-transparent">session prep.</span>
                    <br class="hidden sm:block" />
                    Start running epic campaigns.
                </h1>

                {{-- Subheadline --}}
                <p class="mx-auto mb-10 max-w-2xl text-lg sm:text-xl text-zinc-400 leading-relaxed">
                    DM Forge combines intelligent AI generation with a powerful session toolkit. Build worlds, craft encounters, track combat, and run sessions — all in one place.
                </p>

                {{-- CTA buttons --}}
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                    @if (Route::has('register'))
                        <a
                            href="{{ route('register') }}"
                            class="group inline-flex items-center gap-2 rounded-xl bg-amber-500 px-8 py-3.5 text-base font-semibold text-zinc-950 hover:bg-amber-400 transition-all duration-200 shadow-lg shadow-amber-500/25 hover:shadow-amber-500/40 hover:-translate-y-0.5"
                        >
                            Forge Your Campaign
                            <svg class="h-4 w-4 group-hover:translate-x-1 transition-transform duration-150" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </a>
                    @endif
                    @if (Route::has('login'))
                        <a
                            href="{{ route('login') }}"
                            class="inline-flex items-center gap-2 rounded-xl border border-zinc-700 px-8 py-3.5 text-base font-medium text-zinc-300 hover:border-zinc-500 hover:text-white transition-all duration-200"
                        >
                            Log in
                        </a>
                    @endif
                </div>

                {{-- Stat bar --}}
                <div class="mt-16 flex flex-wrap justify-center gap-6 sm:gap-10">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-white">10+</div>
                        <div class="text-xs text-zinc-500 uppercase tracking-wider mt-0.5">AI Generators</div>
                    </div>
                    <div class="w-px bg-zinc-800 self-stretch hidden sm:block"></div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-white">Full SRD</div>
                        <div class="text-xs text-zinc-500 uppercase tracking-wider mt-0.5">Monster Library</div>
                    </div>
                    <div class="w-px bg-zinc-800 self-stretch hidden sm:block"></div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-white">Live</div>
                        <div class="text-xs text-zinc-500 uppercase tracking-wider mt-0.5">Combat Tracker</div>
                    </div>
                    <div class="w-px bg-zinc-800 self-stretch hidden sm:block"></div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-white">AI</div>
                        <div class="text-xs text-zinc-500 uppercase tracking-wider mt-0.5">Session Recaps</div>
                    </div>
                </div>
            </div>

            {{-- Bottom fade --}}
            <div class="absolute bottom-0 inset-x-0 h-40 bg-linear-to-t from-zinc-950 to-transparent pointer-events-none"></div>
        </section>


        {{-- ============================================================ --}}
        {{-- FEATURE HIGHLIGHTS STRIP --}}
        {{-- ============================================================ --}}
        <section class="border-y border-zinc-800/60 bg-zinc-900/40 py-5">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex flex-wrap items-center justify-center gap-x-8 gap-y-3 text-sm text-zinc-400">
                    @foreach ([
                        'Campaign Wizard',
                        'Scene-by-Scene Session Builder',
                        'Live Combat Tracker',
                        'AI NPC & Monster Generator',
                        'World State Timeline',
                        'Session Recap AI',
                    ] as $feature)
                        <div class="flex items-center gap-2">
                            <svg class="h-3.5 w-3.5 shrink-0 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{ $feature }}
                        </div>
                    @endforeach
                </div>
            </div>
        </section>


        {{-- ============================================================ --}}
        {{-- MAIN FEATURES GRID --}}
        {{-- ============================================================ --}}
        <section id="features" class="py-24 bg-zinc-950">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

                <div class="mb-16 text-center">
                    <div class="mb-4 inline-flex items-center gap-2 rounded-full border border-zinc-700 bg-zinc-800/50 px-4 py-1.5 text-xs font-medium uppercase tracking-widest text-zinc-400">
                        Everything You Need
                    </div>
                    <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-white mb-4">
                        Your complete D&amp;D toolkit
                    </h2>
                    <p class="mx-auto max-w-xl text-zinc-400 text-lg">
                        From campaign creation to the final session recap — every tool a Dungeon Master needs, powered by AI.
                    </p>
                </div>

                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">

                    {{-- Campaign Building --}}
                    <div class="group relative rounded-2xl border border-zinc-800 bg-zinc-900 p-6 hover:border-zinc-700 hover:bg-zinc-900/80 transition-all duration-300 overflow-hidden">
                        <div class="mb-4 inline-flex h-11 w-11 items-center justify-center rounded-xl bg-amber-500/10 border border-amber-500/20">
                            <svg class="h-5 w-5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498l4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 00-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0z" />
                            </svg>
                        </div>
                        <h3 class="mb-2 text-base font-semibold text-white">Campaign Building</h3>
                        <p class="text-sm text-zinc-400 leading-relaxed">Define premise, lore, world rules, factions, and locations. AI-assisted world generation turns your ideas into fully realized campaign foundations.</p>
                        <div class="absolute bottom-0 left-0 right-0 h-px bg-linear-to-r from-transparent via-amber-500/40 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    </div>

                    {{-- AI Generation --}}
                    <div class="group relative rounded-2xl border border-zinc-800 bg-zinc-900 p-6 hover:border-violet-700/50 hover:bg-zinc-900/80 transition-all duration-300 overflow-hidden">
                        <div class="mb-4 inline-flex h-11 w-11 items-center justify-center rounded-xl bg-violet-500/10 border border-violet-500/20">
                            <svg class="h-5 w-5 text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456z" />
                            </svg>
                        </div>
                        <h3 class="mb-2 text-base font-semibold text-white">AI Generation</h3>
                        <p class="text-sm text-zinc-400 leading-relaxed">Generate NPCs with personality and backstory, create custom monsters, design entire sessions, and get AI-written post-session recaps automatically.</p>
                        <div class="absolute bottom-0 left-0 right-0 h-px bg-linear-to-r from-transparent via-violet-500/40 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    </div>

                    {{-- Session Builder --}}
                    <div class="group relative rounded-2xl border border-zinc-800 bg-zinc-900 p-6 hover:border-blue-700/50 hover:bg-zinc-900/80 transition-all duration-300 overflow-hidden">
                        <div class="mb-4 inline-flex h-11 w-11 items-center justify-center rounded-xl bg-blue-500/10 border border-blue-500/20">
                            <svg class="h-5 w-5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                            </svg>
                        </div>
                        <h3 class="mb-2 text-base font-semibold text-white">Session Builder</h3>
                        <p class="text-sm text-zinc-400 leading-relaxed">Craft scenes with encounters, puzzles, branching paths, and loot. Visual scene-by-scene structure with monster and NPC placement for each encounter.</p>
                        <div class="absolute bottom-0 left-0 right-0 h-px bg-linear-to-r from-transparent via-blue-500/40 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    </div>

                    {{-- Combat Tracker --}}
                    <div class="group relative rounded-2xl border border-zinc-800 bg-zinc-900 p-6 hover:border-amber-700/50 hover:bg-zinc-900/80 transition-all duration-300 overflow-hidden">
                        <div class="mb-4 inline-flex h-11 w-11 items-center justify-center rounded-xl bg-amber-500/10 border border-amber-500/20">
                            <svg class="h-5 w-5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                            </svg>
                        </div>
                        <h3 class="mb-2 text-base font-semibold text-white">Combat Tracker</h3>
                        <p class="text-sm text-zinc-400 leading-relaxed">Live initiative tracking with HP management, NPC stat blocks, and automatic combatant loading from your session encounters. No more notebook math.</p>
                        <div class="absolute bottom-0 left-0 right-0 h-px bg-linear-to-r from-transparent via-amber-500/40 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    </div>

                    {{-- World State Timeline --}}
                    <div class="group relative rounded-2xl border border-zinc-800 bg-zinc-900 p-6 hover:border-zinc-600 hover:bg-zinc-900/80 transition-all duration-300 overflow-hidden">
                        <div class="mb-4 inline-flex h-11 w-11 items-center justify-center rounded-xl bg-zinc-700/50 border border-zinc-600/30">
                            <svg class="h-5 w-5 text-zinc-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="mb-2 text-base font-semibold text-white">World State Timeline</h3>
                        <p class="text-sm text-zinc-400 leading-relaxed">Track key decisions, faction movements, NPC changes, and consequences across all sessions. The living history of your campaign world, session by session.</p>
                        <div class="absolute bottom-0 left-0 right-0 h-px bg-linear-to-r from-transparent via-zinc-500/40 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    </div>

                    {{-- NPC & Loot Library --}}
                    <div class="group relative rounded-2xl border border-zinc-800 bg-zinc-900 p-6 hover:border-emerald-700/50 hover:bg-zinc-900/80 transition-all duration-300 overflow-hidden">
                        <div class="mb-4 inline-flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-500/10 border border-emerald-500/20">
                            <svg class="h-5 w-5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                            </svg>
                        </div>
                        <h3 class="mb-2 text-base font-semibold text-white">NPC &amp; Loot Library</h3>
                        <p class="text-sm text-zinc-400 leading-relaxed">Full SRD monster compendium, custom monster creation, magic items and equipment with rarity. Attach loot and monsters directly to encounters and scenes.</p>
                        <div class="absolute bottom-0 left-0 right-0 h-px bg-linear-to-r from-transparent via-emerald-500/40 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    </div>

                </div>
            </div>
        </section>


        {{-- ============================================================ --}}
        {{-- AI SECTION --}}
        {{-- ============================================================ --}}
        <section id="ai" class="py-24 relative overflow-hidden bg-zinc-900">

            <div class="absolute inset-0 bg-linear-to-br from-violet-950/50 via-zinc-900 to-zinc-900 pointer-events-none"></div>
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-225 h-150 rounded-full bg-violet-500/4 blur-3xl pointer-events-none"></div>

            <div class="relative z-10 mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid gap-14 lg:grid-cols-2 lg:items-center">

                    {{-- Left: copy --}}
                    <div>
                        <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-violet-500/30 bg-violet-500/10 px-4 py-1.5 text-sm font-medium text-violet-400">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z" />
                            </svg>
                            AI-Powered Generation
                        </div>
                        <h2 class="mb-6 text-3xl sm:text-4xl lg:text-5xl font-bold text-white leading-tight">
                            Your AI co-DM<br class="hidden lg:block" />
                            is always ready
                        </h2>
                        <p class="mb-8 text-lg text-zinc-400 leading-relaxed">
                            DM Forge's AI agents don't just suggest ideas — they generate complete, usable content. Describe what you need, and get fully fleshed-out NPCs, encounters, and sessions in seconds.
                        </p>

                        <div class="space-y-5">
                            @foreach ([
                                ['Campaign Wizard AI', 'Generate a full campaign skeleton from a single premise sentence'],
                                ['NPC Generator', 'Rich backstories, personality traits, voice descriptions, and motivations'],
                                ['Session Generator', 'Full sessions with scenes, encounters, and branching narrative paths'],
                                ['Recap Narrator', 'Automatic post-session narrative recaps your players will love'],
                            ] as [$name, $desc])
                                <div class="flex items-start gap-3">
                                    <div class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-violet-500/20 border border-violet-500/30">
                                        <svg class="h-3.5 w-3.5 text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="font-medium text-white text-sm">{{ $name }}</div>
                                        <div class="text-xs text-zinc-500 mt-0.5">{{ $desc }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Right: mock NPC generator card --}}
                    <div class="relative">
                        <div class="absolute inset-4 rounded-2xl bg-violet-500/8 blur-2xl"></div>
                        <div class="relative rounded-2xl border border-violet-500/20 bg-zinc-900/90 backdrop-blur-sm p-6 shadow-2xl shadow-violet-900/20">

                            {{-- Window chrome --}}
                            <div class="mb-5 flex items-center gap-2">
                                <div class="h-2.5 w-2.5 rounded-full bg-red-500/80"></div>
                                <div class="h-2.5 w-2.5 rounded-full bg-amber-500/80"></div>
                                <div class="h-2.5 w-2.5 rounded-full bg-emerald-500/80"></div>
                                <span class="ml-2 text-xs text-zinc-600 font-mono">NPC Generator — DM Forge</span>
                            </div>

                            {{-- Prompt --}}
                            <div class="rounded-lg bg-zinc-800/60 border border-zinc-700/50 px-4 py-3 mb-3">
                                <p class="text-xs text-zinc-500 mb-1.5 uppercase tracking-wider font-medium">Your prompt</p>
                                <p class="text-sm text-zinc-300 italic leading-relaxed">"A dwarven blacksmith in Ironhold who secretly knows the location of the lost forge of Moradin, haunted by the death of his apprentice..."</p>
                            </div>

                            {{-- Generating indicator --}}
                            <div class="flex items-center gap-2 text-xs text-violet-400 py-2 px-1">
                                <svg class="h-3.5 w-3.5 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                                </svg>
                                Generating NPC with full backstory...
                            </div>

                            {{-- Generated result --}}
                            <div class="rounded-lg bg-violet-500/5 border border-violet-500/20 px-4 py-4 space-y-3">
                                <div class="flex items-center gap-2">
                                    <svg class="h-3.5 w-3.5 text-violet-400" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z" />
                                    </svg>
                                    <span class="text-xs font-semibold text-violet-400 uppercase tracking-wider">Generated NPC</span>
                                </div>
                                <div>
                                    <p class="text-base font-semibold text-white">Grimund Ashbeard</p>
                                    <p class="text-xs text-zinc-500 mt-0.5">Male dwarf, 187 years old · Blacksmith · Ironhold</p>
                                </div>
                                <p class="text-sm text-zinc-400 leading-relaxed">A gruff exterior that hides profound grief. Lost his apprentice Kira in a cave-in near Moradin's Reach — a place he now avoids. Speaks little about the forge's location, but his hands tremble when travelers mention it...</p>
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach (['Secretive', 'Loyal', 'Haunted', 'Stubborn'] as $trait)
                                        <span class="rounded-full bg-zinc-800 border border-zinc-700 px-2.5 py-0.5 text-xs text-zinc-300">{{ $trait }}</span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>


        {{-- ============================================================ --}}
        {{-- HOW IT WORKS --}}
        {{-- ============================================================ --}}
        <section id="how-it-works" class="py-24 bg-zinc-950">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

                <div class="mb-16 text-center">
                    <div class="mb-4 inline-flex items-center gap-2 rounded-full border border-zinc-700 bg-zinc-800/50 px-4 py-1.5 text-xs font-medium uppercase tracking-widest text-zinc-400">
                        How It Works
                    </div>
                    <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-white mb-4">
                        From concept to session in minutes
                    </h2>
                    <p class="mx-auto max-w-xl text-zinc-400 text-lg">
                        Three steps. That's all it takes to go from blank page to table-ready adventure.
                    </p>
                </div>

                <div class="relative grid gap-10 lg:grid-cols-3">

                    {{-- Connecting line (desktop) --}}
                    <div class="absolute top-11 left-[calc(16.667%+1.75rem)] right-[calc(16.667%+1.75rem)] h-px bg-linear-to-r from-amber-500/40 via-violet-500/40 to-emerald-500/40 hidden lg:block pointer-events-none"></div>

                    {{-- Step 1 --}}
                    <div class="relative flex flex-col items-center lg:items-start text-center lg:text-left">
                        <div class="relative mb-6 flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-500/10 border border-amber-500/30">
                            <svg class="h-7 w-7 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498l4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 00-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0z" />
                            </svg>
                            <span class="absolute -top-2.5 -right-2.5 flex h-6 w-6 items-center justify-center rounded-full bg-amber-500 text-xs font-bold text-zinc-950 shadow-lg shadow-amber-500/30">1</span>
                        </div>
                        <h3 class="mb-3 text-xl font-bold text-white">Create Your Campaign</h3>
                        <p class="text-zinc-400 text-sm leading-relaxed max-w-xs">Describe your world premise and let the Campaign Wizard AI flesh out factions, locations, NPCs, and lore. Your setting builds itself.</p>
                    </div>

                    {{-- Step 2 --}}
                    <div class="relative flex flex-col items-center lg:items-start text-center lg:text-left">
                        <div class="relative mb-6 flex h-14 w-14 items-center justify-center rounded-2xl bg-violet-500/10 border border-violet-500/30">
                            <svg class="h-7 w-7 text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                            </svg>
                            <span class="absolute -top-2.5 -right-2.5 flex h-6 w-6 items-center justify-center rounded-full bg-violet-500 text-xs font-bold text-white shadow-lg shadow-violet-500/30">2</span>
                        </div>
                        <h3 class="mb-3 text-xl font-bold text-white">Build Your Sessions</h3>
                        <p class="text-zinc-400 text-sm leading-relaxed max-w-xs">Craft scenes with encounters, puzzles, and loot in the Session Builder. Or let AI generate a full session structure from your plot notes in seconds.</p>
                    </div>

                    {{-- Step 3 --}}
                    <div class="relative flex flex-col items-center lg:items-start text-center lg:text-left">
                        <div class="relative mb-6 flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-500/10 border border-emerald-500/30">
                            <svg class="h-7 w-7 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                            </svg>
                            <span class="absolute -top-2.5 -right-2.5 flex h-6 w-6 items-center justify-center rounded-full bg-emerald-500 text-xs font-bold text-zinc-950 shadow-lg shadow-emerald-500/30">3</span>
                        </div>
                        <h3 class="mb-3 text-xl font-bold text-white">Run &amp; Track</h3>
                        <p class="text-zinc-400 text-sm leading-relaxed max-w-xs">Open Session Runner at the table. Navigate scenes, launch Combat Tracker, choose branching paths, and let AI write the session recap when you're done.</p>
                    </div>

                </div>
            </div>
        </section>


        {{-- ============================================================ --}}
        {{-- FINAL CTA --}}
        {{-- ============================================================ --}}
        <section class="py-28 bg-zinc-900 relative overflow-hidden">

            <div class="absolute inset-0 bg-linear-to-br from-amber-950/30 via-zinc-900 to-zinc-900 pointer-events-none"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-175 h-100 rounded-full bg-amber-500/6 blur-3xl pointer-events-none glow-pulse"></div>

            {{-- Large decorative D20 --}}
            <div class="absolute right-0 lg:right-12 top-1/2 -translate-y-1/2 opacity-[0.04] pointer-events-none select-none hidden lg:block">
                <svg class="w-72 h-72 text-amber-400" viewBox="0 0 100 100" fill="none" stroke="currentColor" stroke-width="0.6">
                    <polygon points="50,5 95,27.5 95,72.5 50,95 5,72.5 5,27.5" />
                    <polyline points="5,27.5 50,50 95,27.5" />
                    <line x1="50" y1="50" x2="50" y2="95" />
                    <line x1="5" y1="27.5" x2="50" y2="15" />
                    <line x1="95" y1="27.5" x2="50" y2="15" />
                    <text x="50" y="42" text-anchor="middle" font-size="20" fill="currentColor" stroke="none" font-weight="bold" font-family="serif">20</text>
                </svg>
            </div>

            <div class="relative z-10 mx-auto max-w-3xl px-4 sm:px-6 lg:px-8 text-center">
                <h2 class="mb-4 text-3xl sm:text-4xl lg:text-5xl font-bold text-white leading-tight">
                    Your adventure awaits
                </h2>
                <p class="mb-10 text-xl text-zinc-400 leading-relaxed">
                    Join the DMs who prep less, play more, and run the best sessions their tables have ever seen.
                </p>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                    @if (Route::has('register'))
                        <a
                            href="{{ route('register') }}"
                            class="group inline-flex items-center gap-2 rounded-xl bg-amber-500 px-10 py-4 text-base font-semibold text-zinc-950 hover:bg-amber-400 transition-all duration-200 shadow-xl shadow-amber-500/20 hover:shadow-amber-500/35 hover:-translate-y-0.5"
                        >
                            Start Your Campaign
                            <svg class="h-4 w-4 group-hover:translate-x-1 transition-transform duration-150" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </a>
                    @endif
                    @if (Route::has('login'))
                        <a
                            href="{{ route('login') }}"
                            class="inline-flex items-center rounded-xl border border-zinc-700 px-10 py-4 text-base font-medium text-zinc-300 hover:border-zinc-500 hover:text-white transition-all duration-200"
                        >
                            Log in
                        </a>
                    @endif
                </div>
            </div>
        </section>


        {{-- ============================================================ --}}
        {{-- FOOTER --}}
        {{-- ============================================================ --}}
        <footer class="border-t border-zinc-800 bg-zinc-950 py-10">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">

                    <a href="{{ route('home') }}" class="flex items-center gap-2 group">
                        <div class="flex h-7 w-7 items-center justify-center rounded-md bg-amber-500/10 border border-amber-500/20 group-hover:bg-amber-500/20 transition-colors duration-150">
                            <svg class="h-4 w-4 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <polygon points="12,2 22,8 22,16 12,22 2,16 2,8" />
                                <polyline points="2,8 12,12 22,8" />
                                <line x1="12" y1="12" x2="12" y2="22" />
                                <line x1="2" y1="8" x2="12" y2="5" />
                                <line x1="22" y1="8" x2="12" y2="5" />
                            </svg>
                        </div>
                        <span class="text-sm font-semibold text-zinc-500 group-hover:text-zinc-300 transition-colors duration-150">DM Forge</span>
                    </a>

                    <p class="text-xs text-zinc-700">
                        &copy; {{ date('Y') }} DM Forge. Built for Dungeon Masters everywhere.
                    </p>

                    @if (Route::has('login'))
                        <div class="flex items-center gap-5 text-xs text-zinc-600">
                            @auth
                                <a href="{{ url('/dashboard') }}" class="hover:text-zinc-400 transition-colors duration-150">Dashboard</a>
                            @else
                                <a href="{{ route('login') }}" class="hover:text-zinc-400 transition-colors duration-150">Log in</a>
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="hover:text-zinc-400 transition-colors duration-150">Register</a>
                                @endif
                            @endauth
                        </div>
                    @endif
                </div>
            </div>
        </footer>

        @fluxScripts
    </body>
</html>
