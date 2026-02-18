<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @php $title = 'Documentation — DM Forge'; @endphp
        @include('partials.head')

        <style>
            html { scroll-behavior: smooth; }

            /* Highlight the targeted section */
            section:target > div:first-child h2 {
                color: oklch(0.769 0.188 70.08); /* amber-400 */
            }

            /* Active sidebar link via :target */
            @supports selector(:target) {
                .sidebar-link { color: oklch(0.551 0.016 285.938); } /* zinc-500 */
            }
        </style>
    </head>
    <body class="min-h-screen bg-zinc-950 text-white antialiased">

        {{-- ============================================================ --}}
        {{-- NAVIGATION (reused from welcome) --}}
        {{-- ============================================================ --}}
        <nav
            x-data="{ mobileOpen: false, scrolled: false }"
            x-on:scroll.window="scrolled = (window.scrollY > 20)"
            :class="scrolled ? 'bg-zinc-950/95 backdrop-blur-md border-b border-zinc-800/60 shadow-lg shadow-black/20' : 'bg-zinc-950/80 backdrop-blur-sm border-b border-zinc-800/40'"
            class="fixed top-0 inset-x-0 z-50 transition-all duration-300"
        >
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">

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

                    <div class="hidden md:flex items-center gap-6">
                        <a href="{{ route('home') }}" class="text-sm text-zinc-400 hover:text-white transition-colors duration-150">Home</a>
                        <span class="text-sm font-medium text-white border-b border-amber-500/60 pb-0.5">Docs</span>
                    </div>

                    <div class="flex items-center gap-3">
                        @if (Route::has('login'))
                            @auth
                                <a href="{{ url('/dashboard') }}" class="hidden sm:inline-flex items-center gap-1.5 rounded-lg px-4 py-2 text-sm font-medium text-zinc-300 hover:text-white hover:bg-zinc-800 transition-colors duration-150">
                                    Dashboard
                                </a>
                            @else
                                <a href="{{ route('login') }}" class="hidden sm:inline-flex items-center rounded-lg px-4 py-2 text-sm font-medium text-zinc-400 hover:text-white transition-colors duration-150">
                                    Log in
                                </a>
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="inline-flex items-center rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-zinc-950 hover:bg-amber-400 transition-colors duration-150">
                                        Get Started
                                    </a>
                                @endif
                            @endauth
                        @endif

                        <button class="md:hidden p-2 rounded-lg text-zinc-400 hover:text-white hover:bg-zinc-800 transition-colors" x-on:click="mobileOpen = !mobileOpen" aria-label="Toggle menu">
                            <svg x-show="!mobileOpen" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" /></svg>
                            <svg x-show="mobileOpen" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" x-cloak><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                </div>
            </div>

            <div x-show="mobileOpen" x-collapse class="md:hidden border-t border-zinc-800 bg-zinc-950/98 backdrop-blur-md">
                <div class="px-4 py-4 flex flex-col gap-1">
                    <a href="{{ route('home') }}" class="px-3 py-2 rounded-lg text-sm text-zinc-400 hover:text-white hover:bg-zinc-800 transition-colors" x-on:click="mobileOpen = false">Home</a>
                    @auth
                        <a href="{{ url('/dashboard') }}" class="mt-2 px-3 py-2 rounded-lg text-sm font-medium text-amber-400 hover:bg-zinc-800 transition-colors">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="mt-2 px-3 py-2 rounded-lg text-sm text-zinc-300 hover:bg-zinc-800 transition-colors">Log in</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="mt-1 px-3 py-2 rounded-lg text-sm font-semibold bg-amber-500 text-zinc-950 text-center hover:bg-amber-400 transition-colors">Get Started</a>
                        @endif
                    @endauth
                </div>
            </div>
        </nav>

        {{-- ============================================================ --}}
        {{-- PAGE HEADER --}}
        {{-- ============================================================ --}}
        <div class="pt-16 border-b border-zinc-800 bg-zinc-900/50">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-10">
                <div class="flex items-center gap-2 text-xs text-zinc-500 mb-3">
                    <a href="{{ route('home') }}" class="hover:text-zinc-300 transition-colors">DM Forge</a>
                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    <span class="text-zinc-400">Documentation</span>
                </div>
                <h1 class="text-3xl sm:text-4xl font-bold text-white mb-2">Documentation</h1>
                <p class="text-zinc-400 max-w-2xl">Everything you need to know about running great D&amp;D campaigns with DM Forge.</p>
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- MOBILE: Jump to section dropdown --}}
        {{-- ============================================================ --}}
        <div class="lg:hidden border-b border-zinc-800 bg-zinc-900 px-4 py-3 sticky top-16 z-40">
            <label for="mobile-section-nav" class="sr-only">Jump to section</label>
            <select
                id="mobile-section-nav"
                onchange="window.location.hash = this.value; this.value = '';"
                class="w-full rounded-lg border border-zinc-700 bg-zinc-800 px-3 py-2 text-sm text-zinc-300 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500"
            >
                <option value="">Jump to section...</option>
                <optgroup label="Getting Started">
                    <option value="#overview">Overview</option>
                    <option value="#how-it-works">How It Works</option>
                </optgroup>
                <optgroup label="Campaigns">
                    <option value="#campaign-builder">Campaign Builder</option>
                    <option value="#campaign-wizard">Campaign Wizard AI</option>
                    <option value="#npcs">NPCs</option>
                    <option value="#locations-factions">Locations &amp; Factions</option>
                    <option value="#world-timeline">World State Timeline</option>
                </optgroup>
                <optgroup label="Sessions">
                    <option value="#session-builder">Session Builder</option>
                    <option value="#session-runner">Session Runner</option>
                    <option value="#combat-tracker">Combat Tracker</option>
                </optgroup>
                <optgroup label="AI Tools">
                    <option value="#ai-overview">AI Overview</option>
                    <option value="#npc-generator">NPC Generator</option>
                    <option value="#session-generator">Session Generator</option>
                    <option value="#image-generation">Image Generation</option>
                    <option value="#session-recap">Session Recap</option>
                </optgroup>
                <optgroup label="Library">
                    <option value="#monster-library">Monster Library</option>
                    <option value="#loot-library">Loot Library</option>
                </optgroup>
            </select>
        </div>

        {{-- ============================================================ --}}
        {{-- MAIN LAYOUT: Sidebar + Content --}}
        {{-- ============================================================ --}}
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex gap-12 py-12">

                {{-- ---------------------------------------------------- --}}
                {{-- SIDEBAR (desktop only, sticky) --}}
                {{-- ---------------------------------------------------- --}}
                {{--
                    TO ADD A NEW SECTION:
                    1. Add a new <li> entry below in the correct group
                    2. Add a matching <section id="your-id"> in the content area
                --}}
                <aside class="hidden lg:block w-56 shrink-0">
                    <div class="sticky top-24 max-h-[calc(100vh-6rem)] overflow-y-auto pr-2">
                        <nav class="space-y-6 text-sm">

                            {{-- Getting Started --}}
                            <div>
                                <div class="mb-2 px-3 text-xs font-semibold uppercase tracking-widest text-zinc-600">Getting Started</div>
                                <ul class="space-y-0.5">
                                    <li><a href="#overview" class="sidebar-link block rounded-lg px-3 py-1.5 text-zinc-400 hover:bg-zinc-800 hover:text-white transition-colors duration-150">Overview</a></li>
                                    <li><a href="#how-it-works" class="sidebar-link block rounded-lg px-3 py-1.5 text-zinc-400 hover:bg-zinc-800 hover:text-white transition-colors duration-150">How It Works</a></li>
                                </ul>
                            </div>

                            {{-- Campaigns --}}
                            <div>
                                <div class="mb-2 px-3 text-xs font-semibold uppercase tracking-widest text-zinc-600">Campaigns</div>
                                <ul class="space-y-0.5">
                                    <li><a href="#campaign-builder" class="sidebar-link block rounded-lg px-3 py-1.5 text-zinc-400 hover:bg-zinc-800 hover:text-white transition-colors duration-150">Campaign Builder</a></li>
                                    <li><a href="#campaign-wizard" class="sidebar-link block rounded-lg px-3 py-1.5 text-zinc-400 hover:bg-zinc-800 hover:text-white transition-colors duration-150">Campaign Wizard AI</a></li>
                                    <li><a href="#npcs" class="sidebar-link block rounded-lg px-3 py-1.5 text-zinc-400 hover:bg-zinc-800 hover:text-white transition-colors duration-150">NPCs</a></li>
                                    <li><a href="#locations-factions" class="sidebar-link block rounded-lg px-3 py-1.5 text-zinc-400 hover:bg-zinc-800 hover:text-white transition-colors duration-150">Locations &amp; Factions</a></li>
                                    <li><a href="#world-timeline" class="sidebar-link block rounded-lg px-3 py-1.5 text-zinc-400 hover:bg-zinc-800 hover:text-white transition-colors duration-150">World State Timeline</a></li>
                                </ul>
                            </div>

                            {{-- Sessions --}}
                            <div>
                                <div class="mb-2 px-3 text-xs font-semibold uppercase tracking-widest text-zinc-600">Sessions</div>
                                <ul class="space-y-0.5">
                                    <li><a href="#session-builder" class="sidebar-link block rounded-lg px-3 py-1.5 text-zinc-400 hover:bg-zinc-800 hover:text-white transition-colors duration-150">Session Builder</a></li>
                                    <li><a href="#session-runner" class="sidebar-link block rounded-lg px-3 py-1.5 text-zinc-400 hover:bg-zinc-800 hover:text-white transition-colors duration-150">Session Runner</a></li>
                                    <li><a href="#combat-tracker" class="sidebar-link block rounded-lg px-3 py-1.5 text-zinc-400 hover:bg-zinc-800 hover:text-white transition-colors duration-150">Combat Tracker</a></li>
                                </ul>
                            </div>

                            {{-- AI Tools --}}
                            <div>
                                <div class="mb-2 px-3 text-xs font-semibold uppercase tracking-widest text-zinc-600">AI Tools</div>
                                <ul class="space-y-0.5">
                                    <li><a href="#ai-overview" class="sidebar-link block rounded-lg px-3 py-1.5 text-zinc-400 hover:bg-zinc-800 hover:text-white transition-colors duration-150">AI Overview</a></li>
                                    <li><a href="#npc-generator" class="sidebar-link block rounded-lg px-3 py-1.5 text-zinc-400 hover:bg-zinc-800 hover:text-white transition-colors duration-150">NPC Generator</a></li>
                                    <li><a href="#session-generator" class="sidebar-link block rounded-lg px-3 py-1.5 text-zinc-400 hover:bg-zinc-800 hover:text-white transition-colors duration-150">Session Generator</a></li>
                                    <li><a href="#image-generation" class="sidebar-link block rounded-lg px-3 py-1.5 text-zinc-400 hover:bg-zinc-800 hover:text-white transition-colors duration-150">Image Generation</a></li>
                                    <li><a href="#session-recap" class="sidebar-link block rounded-lg px-3 py-1.5 text-zinc-400 hover:bg-zinc-800 hover:text-white transition-colors duration-150">Session Recap</a></li>
                                </ul>
                            </div>

                            {{-- Library --}}
                            <div>
                                <div class="mb-2 px-3 text-xs font-semibold uppercase tracking-widest text-zinc-600">Library</div>
                                <ul class="space-y-0.5">
                                    <li><a href="#monster-library" class="sidebar-link block rounded-lg px-3 py-1.5 text-zinc-400 hover:bg-zinc-800 hover:text-white transition-colors duration-150">Monster Library</a></li>
                                    <li><a href="#loot-library" class="sidebar-link block rounded-lg px-3 py-1.5 text-zinc-400 hover:bg-zinc-800 hover:text-white transition-colors duration-150">Loot Library</a></li>
                                </ul>
                            </div>

                        </nav>
                    </div>
                </aside>

                {{-- ---------------------------------------------------- --}}
                {{-- CONTENT AREA --}}
                {{-- ---------------------------------------------------- --}}
                {{--
                    Each section follows this structure:
                    <section id="section-id" class="mb-16 scroll-mt-24">
                        Icon badge + heading
                        Description paragraph
                        Feature bullet list
                        Optional tip/note callout
                    </section>

                    Colors:
                        amber  = campaigns, overview
                        violet = AI tools
                        blue   = sessions
                        red    = combat
                        emerald = library, NPCs

                    To add a new section:
                        1. Add a sidebar link above
                        2. Copy any section below and update id, icon, heading, and content
                --}}
                <main class="flex-1 min-w-0">


                    {{-- ==================================================== --}}
                    {{-- GETTING STARTED --}}
                    {{-- ==================================================== --}}

                    <section id="overview" class="mb-16 scroll-mt-24">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-500/10 border border-amber-500/20">
                                <svg class="h-5 w-5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 001.5-.189m-1.5.189a6.01 6.01 0 01-1.5-.189m3.75 7.478a12.06 12.06 0 01-4.5 0m3.75 2.383a14.406 14.406 0 01-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 10-7.517 0c.85.493 1.509 1.333 1.509 2.316V18" /></svg>
                            </div>
                            <h2 class="text-2xl font-bold text-white">Overview</h2>
                        </div>
                        <p class="text-zinc-400 mb-6 leading-relaxed">DM Forge is a campaign management platform for Dungeon Masters. It covers the full lifecycle of running a D&amp;D campaign — from world-building and session prep to live play and post-session recap — with AI assistance available at every step.</p>
                        <ul class="space-y-3">
                            <li class="flex items-start gap-3">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                <span class="text-zinc-300 text-sm"><strong class="text-white">Campaigns</strong> — Your top-level container. One campaign holds all the lore, NPCs, locations, factions, and sessions for a single story arc.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                <span class="text-zinc-300 text-sm"><strong class="text-white">Sessions</strong> — Each session is a structured play event made up of scenes. Build sessions ahead of time using the Session Builder, then run them live with the Session Runner.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                <span class="text-zinc-300 text-sm"><strong class="text-white">AI Everywhere</strong> — Every major feature has an AI generation option. You can generate manually or use AI to fill in the details — the choice is always yours.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                <span class="text-zinc-300 text-sm"><strong class="text-white">Library</strong> — A shared pool of monsters and loot items (including the full D&amp;D 5e SRD) that you can pull into any encounter or scene.</span>
                            </li>
                        </ul>
                    </section>

                    <section id="how-it-works" class="mb-16 scroll-mt-24">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-500/10 border border-amber-500/20">
                                <svg class="h-5 w-5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/></svg>
                            </div>
                            <h2 class="text-2xl font-bold text-white">How It Works</h2>
                        </div>
                        <p class="text-zinc-400 mb-6 leading-relaxed">DM Forge follows a three-phase workflow designed to match how DMs actually prepare and run games.</p>
                        <div class="space-y-4">
                            <div class="rounded-xl border border-zinc-800 bg-zinc-900 p-5">
                                <div class="flex items-center gap-3 mb-2">
                                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-amber-500 text-xs font-bold text-zinc-950">1</span>
                                    <h3 class="font-semibold text-white">Create Your Campaign</h3>
                                </div>
                                <p class="text-sm text-zinc-400 leading-relaxed pl-10">Set up your campaign with a premise, world rules, tone, and genre. Add factions, locations, and NPCs either manually or with the AI Campaign Wizard. This becomes the context all future AI generation draws from.</p>
                            </div>
                            <div class="rounded-xl border border-zinc-800 bg-zinc-900 p-5">
                                <div class="flex items-center gap-3 mb-2">
                                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-violet-500 text-xs font-bold text-white">2</span>
                                    <h3 class="font-semibold text-white">Build Your Sessions</h3>
                                </div>
                                <p class="text-sm text-zinc-400 leading-relaxed pl-10">Create sessions inside the campaign. Each session has scenes — use the Session Builder to add encounters with monsters, puzzles, loot drops, and branching paths. You can build manually or generate an entire session with AI from your plot notes.</p>
                            </div>
                            <div class="rounded-xl border border-zinc-800 bg-zinc-900 p-5">
                                <div class="flex items-center gap-3 mb-2">
                                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-emerald-500 text-xs font-bold text-zinc-950">3</span>
                                    <h3 class="font-semibold text-white">Run &amp; Track</h3>
                                </div>
                                <p class="text-sm text-zinc-400 leading-relaxed pl-10">Open the Session Runner at the table. Navigate through scenes, launch the Combat Tracker when a fight breaks out, record key decisions, and follow branch paths based on what the players choose. After the session, generate an AI recap for your players.</p>
                            </div>
                        </div>
                    </section>

                    <hr class="border-zinc-800 mb-16">

                    {{-- ==================================================== --}}
                    {{-- CAMPAIGNS --}}
                    {{-- ==================================================== --}}

                    <section id="campaign-builder" class="mb-16 scroll-mt-24">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-500/10 border border-amber-500/20">
                                <svg class="h-5 w-5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498l4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 00-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0z"/></svg>
                            </div>
                            <h2 class="text-2xl font-bold text-white">Campaign Builder</h2>
                        </div>
                        <p class="text-zinc-400 mb-6 leading-relaxed">The campaign is the foundation of everything in DM Forge. It holds your world's identity and serves as context for all AI generation within the campaign.</p>
                        <ul class="space-y-3">
                            <li class="flex items-start gap-3">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                <span class="text-zinc-300 text-sm"><strong class="text-white">Premise &amp; Lore</strong> — A summary of your campaign's core story, setting, and themes. AI tools reference this when generating content.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                <span class="text-zinc-300 text-sm"><strong class="text-white">World Rules</strong> — Define how magic works, what deities exist, how technology fits in, and any special rules for your setting.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                <span class="text-zinc-300 text-sm"><strong class="text-white">Genre &amp; Tone</strong> — Set whether your campaign is high fantasy, dark horror, political intrigue, or anything else. This shapes AI-generated content.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                <span class="text-zinc-300 text-sm"><strong class="text-white">Status Tracking</strong> — Campaigns move from Draft → Prepared → Running → Completed. The status determines what actions are available (e.g., only Running campaigns show the Session Runner).</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                <span class="text-zinc-300 text-sm"><strong class="text-white">AI Image Generation</strong> — Generate a campaign cover image from a prompt to visually represent your world.</span>
                            </li>
                        </ul>
                    </section>

                    <section id="campaign-wizard" class="mb-16 scroll-mt-24">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-violet-500/10 border border-violet-500/20">
                                <svg class="h-5 w-5 text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/></svg>
                            </div>
                            <h2 class="text-2xl font-bold text-white">Campaign Wizard <span class="text-sm font-normal text-violet-400 ml-1">AI</span></h2>
                        </div>
                        <p class="text-zinc-400 mb-6 leading-relaxed">The Campaign Wizard is a multi-step AI agent that builds an entire campaign foundation from a single premise sentence. It's the fastest way to go from idea to playable world.</p>
                        <ul class="space-y-3">
                            <li class="flex items-start gap-3">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-violet-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                <span class="text-zinc-300 text-sm"><strong class="text-white">Step-by-step guidance</strong> — The wizard walks you through setting your premise, genre, tone, and key details before generating.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-violet-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                <span class="text-zinc-300 text-sm"><strong class="text-white">Full skeleton generation</strong> — Generates campaign lore, world rules, starting factions, key locations, and major NPCs all at once.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-violet-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                <span class="text-zinc-300 text-sm"><strong class="text-white">Editable output</strong> — Everything the wizard generates is fully editable. Use it as a starting point and refine it to match your vision.</span>
                            </li>
                        </ul>
                        <div class="mt-6 rounded-xl border border-violet-500/20 bg-violet-500/5 p-4">
                            <p class="text-sm text-violet-300"><strong>Tip:</strong> The more specific your premise, the better the wizard's output. "A dark political intrigue in a steampunk city where the nobility are secretly vampires" generates much richer content than "a fantasy campaign."</p>
                        </div>
                    </section>

                    <section id="npcs" class="mb-16 scroll-mt-24">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-500/10 border border-emerald-500/20">
                                <svg class="h-5 w-5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                            </div>
                            <h2 class="text-2xl font-bold text-white">NPCs</h2>
                        </div>
                        <p class="text-zinc-400 mb-6 leading-relaxed">Non-player characters are the heart of any campaign. DM Forge lets you build rich NPC profiles with personality, backstory, and voice — manually or with AI.</p>
                        <ul class="space-y-3">
                            <li class="flex items-start gap-3">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                <span class="text-zinc-300 text-sm"><strong class="text-white">Rich profiles</strong> — Name, race, class, age, appearance, personality traits, ideals, bonds, and flaws.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                <span class="text-zinc-300 text-sm"><strong class="text-white">Backstory &amp; motivation</strong> — Full narrative backstory and what drives the NPC's actions in the story.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                <span class="text-zinc-300 text-sm"><strong class="text-white">Voice description</strong> — Describe how the NPC speaks so you can roleplay them consistently at the table.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                <span class="text-zinc-300 text-sm"><strong class="text-white">AI image generation</strong> — Generate a portrait image for any NPC.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                <span class="text-zinc-300 text-sm"><strong class="text-white">Encounter attachment</strong> — Attach campaign NPCs directly to encounter scenes in the Session Builder.</span>
                            </li>
                        </ul>
                    </section>

                    <section id="locations-factions" class="mb-16 scroll-mt-24">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-500/10 border border-amber-500/20">
                                <svg class="h-5 w-5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                            </div>
                            <h2 class="text-2xl font-bold text-white">Locations &amp; Factions</h2>
                        </div>
                        <p class="text-zinc-400 mb-6 leading-relaxed">Populate your world with the places and power groups that give it depth and political texture.</p>
                        <ul class="space-y-3">
                            <li class="flex items-start gap-3">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                <span class="text-zinc-300 text-sm"><strong class="text-white">Locations</strong> — Add cities, dungeons, wilderness areas, and points of interest. Each can have a description, notable features, and connected NPCs.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                <span class="text-zinc-300 text-sm"><strong class="text-white">Factions</strong> — Define guilds, noble houses, cults, or any group with shared goals. Include their goals, methods, and alignment.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                <span class="text-zinc-300 text-sm"><strong class="text-white">AI image generation</strong> — Generate visual representations for locations and faction crests.</span>
                            </li>
                        </ul>
                    </section>

                    <section id="world-timeline" class="mb-16 scroll-mt-24">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-zinc-700/50 border border-zinc-600/30">
                                <svg class="h-5 w-5 text-zinc-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <h2 class="text-2xl font-bold text-white">World State Timeline</h2>
                        </div>
                        <p class="text-zinc-400 mb-6 leading-relaxed">The World State Timeline is the living history of your campaign. It records key decisions, consequences, and changes to the world as your sessions unfold.</p>
                        <ul class="space-y-3">
                            <li class="flex items-start gap-3">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                <span class="text-zinc-300 text-sm"><strong class="text-white">Decision log</strong> — Record major player decisions and their immediate outcomes to track the story's evolution.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                <span class="text-zinc-300 text-sm"><strong class="text-white">NPC &amp; faction changes</strong> — Track when an NPC's allegiance shifts or a faction's power rises or falls.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                <span class="text-zinc-300 text-sm"><strong class="text-white">Session-linked entries</strong> — Timeline entries are linked to the session they occurred in for easy reference.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                <span class="text-zinc-300 text-sm"><strong class="text-white">AI context</strong> — The timeline feeds into AI generation, so new sessions and NPCs can be created with awareness of past events.</span>
                            </li>
                        </ul>
                    </section>

                    <hr class="border-zinc-800 mb-16">

                    {{-- ==================================================== --}}
                    {{-- SESSIONS --}}
                    {{-- ==================================================== --}}

                    <section id="session-builder" class="mb-16 scroll-mt-24">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-500/10 border border-blue-500/20">
                                <svg class="h-5 w-5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>
                            </div>
                            <h2 class="text-2xl font-bold text-white">Session Builder</h2>
                        </div>
                        <p class="text-zinc-400 mb-6 leading-relaxed">Sessions are built from scenes — discrete narrative moments with their own encounters, puzzles, and loot. The Session Builder gives you a visual canvas to structure your play.</p>
                        <ul class="space-y-3">
                            <li class="flex items-start gap-3">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                <span class="text-zinc-300 text-sm"><strong class="text-white">Scenes</strong> — Add as many scenes as you need. Each scene has a title, description, and type (exploration, roleplay, combat, etc.).</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                <span class="text-zinc-300 text-sm"><strong class="text-white">Encounters</strong> — Add combat encounters to scenes. Each encounter can include monsters from the Library and NPCs from the campaign.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                <span class="text-zinc-300 text-sm"><strong class="text-white">Puzzles</strong> — Design riddles and puzzles with progressive hints that you can reveal one at a time during play.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                <span class="text-zinc-300 text-sm"><strong class="text-white">Loot</strong> — Attach specific loot items to scenes or encounters. Items come from the Loot Library or custom entries.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                <span class="text-zinc-300 text-sm"><strong class="text-white">Branching paths</strong> — Set up branch options on scenes that connect to other scenes. The Session Runner lets you follow these branches based on player choices.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                <span class="text-zinc-300 text-sm"><strong class="text-white">Difficulty rating</strong> — Tag encounters by difficulty (Easy, Medium, Hard, Deadly) to help with encounter balancing.</span>
                            </li>
                        </ul>
                    </section>

                    <section id="session-runner" class="mb-16 scroll-mt-24">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-500/10 border border-blue-500/20">
                                <svg class="h-5 w-5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z"/></svg>
                            </div>
                            <h2 class="text-2xl font-bold text-white">Session Runner</h2>
                        </div>
                        <p class="text-zinc-400 mb-6 leading-relaxed">The Session Runner is your at-the-table companion. It presents your session scene by scene and gives you the tools to manage everything in real time.</p>
                        <ul class="space-y-3">
                            <li class="flex items-start gap-3">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                <span class="text-zinc-300 text-sm"><strong class="text-white">Scene navigation</strong> — Move forward, backward, or jump directly to any scene. The current scene is always highlighted.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                <span class="text-zinc-300 text-sm"><strong class="text-white">Branch choices</strong> — When players reach a branch point, choose a path and the runner navigates to the destination scene automatically.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                <span class="text-zinc-300 text-sm"><strong class="text-white">Combat Tracker launch</strong> — Open the Combat Tracker directly from an encounter card in the runner. Monsters and NPCs are auto-loaded.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                <span class="text-zinc-300 text-sm"><strong class="text-white">Puzzle hints</strong> — Reveal puzzle hints one by one as players need them without showing all hints at once.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                <span class="text-zinc-300 text-sm"><strong class="text-white">Session state</strong> — The runner remembers your current scene between page loads, so you can leave and come back.</span>
                            </li>
                        </ul>
                        <div class="mt-6 rounded-xl border border-blue-500/20 bg-blue-500/5 p-4">
                            <p class="text-sm text-blue-300"><strong>Tip:</strong> Start a session by clicking "Run Session" from the Campaign page. Sessions must be in "Prepared" or "Running" status to be launched.</p>
                        </div>
                    </section>

                    <section id="combat-tracker" class="mb-16 scroll-mt-24">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-500/10 border border-amber-500/20">
                                <svg class="h-5 w-5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/></svg>
                            </div>
                            <h2 class="text-2xl font-bold text-white">Combat Tracker</h2>
                        </div>
                        <p class="text-zinc-400 mb-6 leading-relaxed">A dedicated live combat tool for tracking initiative order, HP, and combatant status during encounters. No more notebook scribbles or lost sticky notes.</p>
                        <ul class="space-y-3">
                            <li class="flex items-start gap-3">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                <span class="text-zinc-300 text-sm"><strong class="text-white">Initiative order</strong> — Add combatants with initiative rolls and the tracker sorts them automatically. Advance through turns with one click.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                <span class="text-zinc-300 text-sm"><strong class="text-white">HP tracking</strong> — Apply damage or healing to any combatant. HP bars show current vs. max at a glance.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                <span class="text-zinc-300 text-sm"><strong class="text-white">Auto-load from encounter</strong> — When launched from the Session Runner, monsters and NPCs from the active encounter are auto-populated with their stat block values.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                <span class="text-zinc-300 text-sm"><strong class="text-white">Persistent state</strong> — Combat state is saved so you can switch tabs mid-fight and return without losing your place.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                <span class="text-zinc-300 text-sm"><strong class="text-white">Add mid-combat</strong> — Add new combatants at any time during combat — for ambushes, reinforcements, or late arrivals.</span>
                            </li>
                        </ul>
                    </section>

                    <hr class="border-zinc-800 mb-16">

                    {{-- ==================================================== --}}
                    {{-- AI TOOLS --}}
                    {{-- ==================================================== --}}

                    <section id="ai-overview" class="mb-16 scroll-mt-24">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-violet-500/10 border border-violet-500/20">
                                <svg class="h-5 w-5 text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/></svg>
                            </div>
                            <h2 class="text-2xl font-bold text-white">AI Overview</h2>
                        </div>
                        <p class="text-zinc-400 mb-6 leading-relaxed">DM Forge uses AI agents throughout the app to generate content that fits your specific campaign. Every AI tool is context-aware — it reads your campaign's premise, lore, and existing content before generating.</p>
                        <ul class="space-y-3">
                            <li class="flex items-start gap-3">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-violet-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                <span class="text-zinc-300 text-sm"><strong class="text-white">Campaign-aware</strong> — AI tools know your campaign's tone, world rules, and lore. Generated content won't contradict your established world.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-violet-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                <span class="text-zinc-300 text-sm"><strong class="text-white">Always editable</strong> — AI output is a starting point. Everything generated can be edited, tweaked, or completely rewritten.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-violet-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                <span class="text-zinc-300 text-sm"><strong class="text-white">Optional</strong> — AI is a tool, not a requirement. Every feature in DM Forge works fully without AI.</span>
                            </li>
                        </ul>
                    </section>

                    <section id="npc-generator" class="mb-16 scroll-mt-24">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-violet-500/10 border border-violet-500/20">
                                <svg class="h-5 w-5 text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                            </div>
                            <h2 class="text-2xl font-bold text-white">NPC Generator <span class="text-sm font-normal text-violet-400 ml-1">AI</span></h2>
                        </div>
                        <p class="text-zinc-400 mb-6 leading-relaxed">Generate fully fleshed-out NPCs from a simple description. The NPC Generator produces a complete character profile including personality, backstory, voice, and motivations — tailored to your campaign setting.</p>
                        <ul class="space-y-3">
                            <li class="flex items-start gap-3">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-violet-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                <span class="text-zinc-300 text-sm"><strong class="text-white">Prompt-based</strong> — Describe what you need (e.g., "a dwarven blacksmith who knows a dangerous secret") and get a full NPC back.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-violet-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                <span class="text-zinc-300 text-sm"><strong class="text-white">Full profiles</strong> — Gets you name, race, class, appearance, personality traits, bonds, flaws, backstory, motivations, voice style, and secrets.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-violet-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                <span class="text-zinc-300 text-sm"><strong class="text-white">Campaign context</strong> — The generator knows your world's lore, factions, and existing NPCs to create characters that fit naturally.</span>
                            </li>
                        </ul>
                    </section>

                    <section id="session-generator" class="mb-16 scroll-mt-24">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-violet-500/10 border border-violet-500/20">
                                <svg class="h-5 w-5 text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>
                            </div>
                            <h2 class="text-2xl font-bold text-white">Session Generator <span class="text-sm font-normal text-violet-400 ml-1">AI</span></h2>
                        </div>
                        <p class="text-zinc-400 mb-6 leading-relaxed">Turn your plot notes into a complete, structured session. The Session Generator creates scenes, encounters, puzzles, and branching paths — ready to load into the Session Builder.</p>
                        <ul class="space-y-3">
                            <li class="flex items-start gap-3">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-violet-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                <span class="text-zinc-300 text-sm"><strong class="text-white">From plot notes</strong> — Paste in your rough session ideas and get a fully structured session with scenes back.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-violet-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                <span class="text-zinc-300 text-sm"><strong class="text-white">Includes encounters &amp; puzzles</strong> — The generator adds encounters (with appropriate difficulty) and puzzles where they fit the narrative.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-violet-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                <span class="text-zinc-300 text-sm"><strong class="text-white">Branching paths</strong> — Generates branch options at key decision points so your session can adapt to player choices.</span>
                            </li>
                        </ul>
                    </section>

                    <section id="image-generation" class="mb-16 scroll-mt-24">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-violet-500/10 border border-violet-500/20">
                                <svg class="h-5 w-5 text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
                            </div>
                            <h2 class="text-2xl font-bold text-white">Image Generation <span class="text-sm font-normal text-violet-400 ml-1">AI</span></h2>
                        </div>
                        <p class="text-zinc-400 mb-6 leading-relaxed">Generate visual art for your campaign, NPCs, locations, and factions. Images are saved to the record and displayed throughout the app.</p>
                        <ul class="space-y-3">
                            <li class="flex items-start gap-3">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-violet-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                <span class="text-zinc-300 text-sm"><strong class="text-white">Campaign cover art</strong> — Generate a header image for your campaign that represents the world's feel and tone.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-violet-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                <span class="text-zinc-300 text-sm"><strong class="text-white">NPC portraits</strong> — Generate a character portrait for any NPC based on their appearance description.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-violet-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                <span class="text-zinc-300 text-sm"><strong class="text-white">Location &amp; faction art</strong> — Generate imagery for key locations and faction symbols.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-violet-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                <span class="text-zinc-300 text-sm"><strong class="text-white">Prompt control</strong> — Images are generated from a custom prompt you write (or that AI drafts from the record's existing description).</span>
                            </li>
                        </ul>
                    </section>

                    <section id="session-recap" class="mb-16 scroll-mt-24">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-violet-500/10 border border-violet-500/20">
                                <svg class="h-5 w-5 text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125"/></svg>
                            </div>
                            <h2 class="text-2xl font-bold text-white">Session Recap <span class="text-sm font-normal text-violet-400 ml-1">AI</span></h2>
                        </div>
                        <p class="text-zinc-400 mb-6 leading-relaxed">After a session, generate an AI-written narrative recap that summarizes what happened — great for sharing with your players as a "Previously on..." intro to the next session.</p>
                        <ul class="space-y-3">
                            <li class="flex items-start gap-3">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-violet-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                <span class="text-zinc-300 text-sm"><strong class="text-white">Narrative style</strong> — Written in an engaging, story-like format rather than a bullet-point list.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-violet-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                <span class="text-zinc-300 text-sm"><strong class="text-white">Context-aware</strong> — The recap uses your session's scenes, encounters, and the campaign's known world state to write an accurate summary.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-violet-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                <span class="text-zinc-300 text-sm"><strong class="text-white">Editable</strong> — The generated recap is fully editable so you can add details or correct anything before sharing.</span>
                            </li>
                        </ul>
                    </section>

                    <hr class="border-zinc-800 mb-16">

                    {{-- ==================================================== --}}
                    {{-- LIBRARY --}}
                    {{-- ==================================================== --}}

                    <section id="monster-library" class="mb-16 scroll-mt-24">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-500/10 border border-emerald-500/20">
                                <svg class="h-5 w-5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6.429 9.75L2.25 12l4.179 2.25m0-4.5l5.571 3 5.571-3m-11.142 0L2.25 7.5 12 2.25l9.75 5.25-4.179 2.25m0 0L21.75 12l-4.179 2.25m0 0l4.179 2.25L12 21.75 2.25 16.5l4.179-2.25m11.142 0l-5.571 3-5.571-3"/></svg>
                            </div>
                            <h2 class="text-2xl font-bold text-white">Monster Library</h2>
                        </div>
                        <p class="text-zinc-400 mb-6 leading-relaxed">A searchable compendium of monsters available for use in any encounter. Includes the full D&amp;D 5e SRD monster set plus any custom monsters you create.</p>
                        <ul class="space-y-3">
                            <li class="flex items-start gap-3">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                <span class="text-zinc-300 text-sm"><strong class="text-white">Full SRD compendium</strong> — All D&amp;D 5e SRD monsters with stat blocks including AC, HP, speed, ability scores, actions, and special traits.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                <span class="text-zinc-300 text-sm"><strong class="text-white">Custom monsters</strong> — Create your own monsters with full stat blocks. Custom monsters can be AI-generated or built manually.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                <span class="text-zinc-300 text-sm"><strong class="text-white">Filter &amp; search</strong> — Filter by challenge rating, monster type, size, and alignment to quickly find the right creature.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                <span class="text-zinc-300 text-sm"><strong class="text-white">Add to encounters</strong> — Add monsters from the library directly to any encounter in the Session Builder.</span>
                            </li>
                        </ul>
                    </section>

                    <section id="loot-library" class="mb-16 scroll-mt-24">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-500/10 border border-emerald-500/20">
                                <svg class="h-5 w-5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/></svg>
                            </div>
                            <h2 class="text-2xl font-bold text-white">Loot Library</h2>
                        </div>
                        <p class="text-zinc-400 mb-6 leading-relaxed">A catalog of magic items and equipment that can be attached to scenes and encounters. Includes the SRD item set plus custom items you create.</p>
                        <ul class="space-y-3">
                            <li class="flex items-start gap-3">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                <span class="text-zinc-300 text-sm"><strong class="text-white">SRD magic items</strong> — All D&amp;D 5e SRD magic items including weapons, armor, wondrous items, and consumables with their full descriptions.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                <span class="text-zinc-300 text-sm"><strong class="text-white">Rarity tiers</strong> — Items are tagged by rarity (Common, Uncommon, Rare, Very Rare, Legendary) for easy filtering.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                <span class="text-zinc-300 text-sm"><strong class="text-white">Custom items</strong> — Create custom loot with your own name, description, type, and rarity.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                <span class="text-zinc-300 text-sm"><strong class="text-white">Attach to scenes</strong> — Drop loot items onto any scene or encounter in the Session Builder. When the scene is reached in the runner, the loot is listed as available reward.</span>
                            </li>
                        </ul>
                        <div class="mt-6 rounded-xl border border-emerald-500/20 bg-emerald-500/5 p-4">
                            <p class="text-sm text-emerald-300"><strong>Tip:</strong> You can attach loot to encounters (reward for defeating enemies) or directly to scenes (found in a chest, rewarded by an NPC, etc.).</p>
                        </div>
                    </section>

                </main>
            </div>
        </div>


        {{-- ============================================================ --}}
        {{-- FOOTER (reused from welcome) --}}
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
                            <a href="{{ route('docs') }}" class="hover:text-zinc-400 transition-colors duration-150">Docs</a>
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
