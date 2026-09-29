<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Secure, on-premise school management system for {{ config('school.name') }}. Student records, fee tracking, and parent SMS — with data stored at the school in full compliance with the Kenya Data Protection Act, 2019.">
    <title>{{ config('school.name') }} — {{ config('school.motto') }} | Safe, On-Site School Management</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'media',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Instrument Sans', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50:  '#FFFBEB',
                            100: '#FEF3C7',
                            500: '#F59E0B',
                            600: '#D97706',
                            700: '#B45309',
                            900: '#78350F',
                        },
                    },
                    container: {
                        center: true,
                        padding: '1.25rem',
                        screens: {
                            '2xl': '1120px',
                        },
                    },
                },
            },
        };
    </script>

    <style>
        /* Small additions Tailwind's CDN build doesn't cover */
        [x-cloak] { display: none !important; }

        /* Carousel keyframes — Tailwind CDN has no keyframe plugin */
        @keyframes carousel-slide {
            0%, 20%   { transform: translateX(0%); }
            25%, 45%  { transform: translateX(-25%); }
            50%, 70%  { transform: translateX(-50%); }
            75%, 95%  { transform: translateX(-75%); }
            100%      { transform: translateX(0%); }
        }

        .carousel-track {
            animation: carousel-slide 24s infinite ease-in-out;
        }

        .carousel-container:hover .carousel-track,
        .carousel-container:focus-within .carousel-track {
            animation-play-state: paused;
        }

        @keyframes carousel-dot {
            0%, 20%   { background: var(--brand-active); transform: scale(1.3); }
            25%, 100% { background: currentColor; opacity: 0.25; transform: scale(1); }
        }

        .carousel-dot:nth-child(1) { animation: carousel-dot 24s infinite; }
        .carousel-dot:nth-child(2) { animation: carousel-dot 24s infinite 6s; }
        .carousel-dot:nth-child(3) { animation: carousel-dot 24s infinite 12s; }
        .carousel-dot:nth-child(4) { animation: carousel-dot 24s infinite 18s; }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: 0.001ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.001ms !important;
                scroll-behavior: auto !important;
            }
        }

        section[id] {
            scroll-margin-top: 5rem;
        }
    </style>
</head>
<body class="font-sans antialiased bg-[#FDFDFC] text-[#1B1B18] dark:bg-[#0A0A0A] dark:text-[#EDEDEC] min-h-screen">

    {{-- ============================================================
         Header
         ============================================================ --}}
    <header
        x-data="{ open: false }"
        class="sticky top-0 z-50 bg-[#FDFDFC]/85 dark:bg-[#0A0A0A]/85 backdrop-blur-md border-b border-black/5 dark:border-white/10"
    >
        <div class="container flex items-center justify-between gap-3 min-h-[60px] md:min-h-[64px] py-3">

            <a href="/" class="flex items-center gap-2.5 font-semibold tracking-tight min-w-0" aria-label="{{ config('school.name') }} home">
                <span class="w-9 h-9 rounded-lg bg-brand-700 dark:bg-brand-500 text-white grid place-items-center shrink-0">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 14l9-5-9-5-9 5 9 5z"/>
                        <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                    </svg>
                </span>
                <span class="flex flex-col leading-tight min-w-0">
                    <span>{{ config('school.name') }}</span>
                    <small class="text-xs font-normal text-[#706F6C] dark:text-[#A1A09A] hidden md:block">{{ config('school.motto') }}</small>
                </span>
            </a>

            <nav class="hidden md:flex gap-7 text-sm text-[#706F6C] dark:text-[#A1A09A]">
                <a href="#features" class="hover:text-[#1B1B18] dark:hover:text-[#EDEDEC]">What it does</a>
                <a href="#how" class="hover:text-[#1B1B18] dark:hover:text-[#EDEDEC]">How it works</a>
                <a href="#data-protection" class="hover:text-[#1B1B18] dark:hover:text-[#EDEDEC]">Your data</a>
                <a href="#roles" class="hover:text-[#1B1B18] dark:hover:text-[#EDEDEC]">For everyone</a>
                <a href="#pricing" class="hover:text-[#1B1B18] dark:hover:text-[#EDEDEC]">Pricing</a>
                <a href="#contact" class="hover:text-[#1B1B18] dark:hover:text-[#EDEDEC]">Contact</a>
            </nav>

            <div class="flex items-center gap-2 ml-auto shrink-0">
                @auth
                    <a href="{{ url('/admin') }}" class="hidden md:inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-medium bg-[#1B1B18] text-white hover:bg-brand-700 transition">Open Dashboard</a>
                @else
                    <a href="{{ url('/admin/login') }}" class="hidden md:inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-medium bg-[#1B1B18] text-white hover:bg-brand-700 transition">Sign in</a>
                @endauth

                <button
                    type="button"
                    class="md:hidden grid place-items-center w-10 h-10 rounded-md border border-black/15 dark:border-white/20 hover:border-[#1B1B18] dark:hover:border-white transition"
                    @click="open = !open"
                    :aria-expanded="open"
                    aria-label="Toggle menu"
                >
                    <svg x-show="!open" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
                    <svg x-show="open" x-cloak class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
                </button>
            </div>
        </div>

        {{-- Mobile menu --}}
        <div
            x-show="open"
            x-cloak
            x-transition.opacity.duration.200ms
            class="md:hidden absolute top-full left-0 right-0 bg-[#FDFDFC] dark:bg-[#0A0A0A] border-t border-black/5 dark:border-white/10 border-b shadow-lg"
            @click.outside="open = false"
        >
            <div class="container py-4 flex flex-col">
                <a href="#features" @click="open = false" class="py-3.5 px-3 text-base font-medium border-b border-black/5 dark:border-white/10 hover:bg-[#FAFAF8] dark:hover:bg-[#1D1D1B] rounded">What it does</a>
                <a href="#how" @click="open = false" class="py-3.5 px-3 text-base font-medium border-b border-black/5 dark:border-white/10 hover:bg-[#FAFAF8] dark:hover:bg-[#1D1D1B] rounded">How it works</a>
                <a href="#data-protection" @click="open = false" class="py-3.5 px-3 text-base font-medium border-b border-black/5 dark:border-white/10 hover:bg-[#FAFAF8] dark:hover:bg-[#1D1D1B] rounded">Your data</a>
                <a href="#roles" @click="open = false" class="py-3.5 px-3 text-base font-medium border-b border-black/5 dark:border-white/10 hover:bg-[#FAFAF8] dark:hover:bg-[#1D1D1B] rounded">For everyone</a>
                <a href="#pricing" @click="open = false" class="py-3.5 px-3 text-base font-medium border-b border-black/5 dark:border-white/10 hover:bg-[#FAFAF8] dark:hover:bg-[#1D1D1B] rounded">Pricing</a>
                <a href="#contact" @click="open = false" class="py-3.5 px-3 text-base font-medium hover:bg-[#FAFAF8] dark:hover:bg-[#1D1D1B] rounded">Contact</a>

                <div class="pt-4 mt-4 border-t border-black/5 dark:border-white/10">
                    @auth
                        <a href="{{ url('/admin') }}" class="block w-full text-center rounded-xl px-4 py-2.5 text-sm font-medium bg-brand-700 text-white">Open Dashboard</a>
                    @else
                        <a href="{{ url('/admin/login') }}" class="block w-full text-center rounded-xl px-4 py-2.5 text-sm font-medium bg-brand-700 text-white">Sign in</a>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    <main>
        {{-- ============================================================
             Hero
             ============================================================ --}}
        <section class="container pt-14 md:pt-16 pb-8 text-center">
            <span class="inline-flex items-center gap-1.5 text-xs font-medium tracking-wider uppercase text-brand-900 dark:text-amber-200 bg-brand-100 dark:bg-amber-950/50 px-3.5 py-1.5 rounded-full mb-5">
                <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                Installed at your school · No cloud · No monthly fees
            </span>

            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold tracking-tight leading-[1.1] mb-4">
                Everything your school needs,<br>
                <em class="not-italic text-brand-700 dark:text-brand-500">in one safe place.</em>
            </h1>

            <p class="text-lg text-[#706F6C] dark:text-[#A1A09A] max-w-2xl mx-auto mb-8">
                Students, fees, attendance, results, and parent messages — all managed from a single
                dashboard that runs on a server <strong class="text-[#1B1B18] dark:text-[#EDEDEC]">at your school</strong>. Your data never leaves
                the premises. Simple enough for anyone on staff to use.
            </p>

            <div class="flex flex-wrap gap-3 justify-center">
                @auth
                    <a href="{{ url('/admin') }}" class="inline-flex items-center gap-2 rounded-2xl px-6 py-3 text-sm font-medium bg-brand-700 text-white hover:brightness-110 transition">Open the dashboard</a>
                @else
                    <a href="{{ url('/admin/login') }}" class="inline-flex items-center gap-2 rounded-2xl px-6 py-3 text-sm font-medium bg-brand-700 text-white hover:brightness-110 transition">
                        Sign in to your account
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
                    </a>
                @endauth
                <a href="#how" class="inline-flex items-center gap-2 rounded-xl px-6 py-3 text-sm font-medium border border-black/15 dark:border-white/20 hover:border-[#1B1B18] dark:hover:border-white transition">See how it works</a>
            </div>

            <div class="mt-6 flex flex-wrap gap-5 justify-center text-xs text-[#706F6C] dark:text-[#A1A09A]">
                <span class="inline-flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-green-700 dark:text-green-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                    Data stored on school premises
                </span>
                <span class="inline-flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-green-700 dark:text-green-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                    Kenya Data Protection Act 2019 compliant
                </span>
                <span class="inline-flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-green-700 dark:text-green-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                    Works on phone, tablet, and computer
                </span>
            </div>
        </section>

        {{-- ============================================================
             Features (Bento)
             ============================================================ --}}
        <section id="features" class="container py-16">
            <div class="text-center max-w-2xl mx-auto mb-10">
                <h2 class="text-3xl sm:text-4xl font-bold tracking-tight mb-2">What you can do</h2>
                <p class="text-[#706F6C] dark:text-[#A1A09A]">Six things you'll do every day — each one just a click away.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">

                {{-- Feature card: Students --}}
                <article class="md:col-span-8 bg-white dark:bg-[#161615] border border-black/5 dark:border-white/10 rounded-xl overflow-hidden shadow-sm hover:shadow-xl hover:-translate-y-0.5 hover:border-black/15 dark:hover:border-white/20 transition-all grid grid-cols-1 lg:grid-cols-2">
                    <div class="p-7 flex flex-col gap-3 justify-center">
                        <span class="text-xs font-medium uppercase tracking-wider text-teal-700 dark:text-teal-400">Students</span>
                        <h3 class="text-lg font-semibold">Keep every student's record in one place</h3>
                        <p class="text-sm text-[#706F6C] dark:text-[#A1A09A] leading-relaxed">
                            Add a new student in under a minute. Find anyone by name, class, or admission number.
                            Update details, upload documents, and print a profile whenever you need it.
                        </p>
                    </div>
                    <div class="bg-[#FAFAF8] dark:bg-[#1D1D1B] lg:border-l border-t lg:border-t-0 border-black/5 dark:border-white/10 p-5 flex items-center justify-center min-h-[220px]">
                        <div class="w-full max-w-[260px] bg-white dark:bg-[#161615] border border-black/5 dark:border-white/10 rounded-lg p-4 text-xs text-[#706F6C] dark:text-[#A1A09A]">
                            <div class="flex items-center gap-2 mb-3">
                                <span class="w-7 h-7 rounded-full bg-brand-100 dark:bg-amber-950/50 grid place-items-center font-semibold text-brand-900 dark:text-amber-200 text-xs">AM</span>
                                <div class="leading-tight">
                                    <div class="text-[#1B1B18] dark:text-[#EDEDEC] font-semibold">Amina Mwangi</div>
                                    <div>Form 3 East · #2024-0187</div>
                                </div>
                            </div>
                            <div class="h-1.5 bg-[#FAFAF8] dark:bg-[#1D1D1B] rounded mb-1.5"></div>
                            <div class="h-1.5 bg-[#FAFAF8] dark:bg-[#1D1D1B] rounded w-[70%]"></div>
                            <div class="mt-3 pt-3 border-t border-black/5 dark:border-white/10 flex justify-between">
                                <span>Attendance</span><span class="text-green-700 dark:text-green-500 font-semibold">98%</span>
                            </div>
                        </div>
                    </div>
                </article>

                {{-- Fees --}}
                <article class="md:col-span-4 bg-white dark:bg-[#161615] border border-black/5 dark:border-white/10 rounded-xl p-6 shadow-sm hover:shadow-xl hover:-translate-y-0.5 hover:border-black/15 dark:hover:border-white/20 transition-all flex flex-col gap-3">
                    <span class="w-10 h-10 rounded-[10px] grid place-items-center bg-brand-100 dark:bg-amber-950/50 text-brand-900 dark:text-amber-200">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                    </span>
                    <h3 class="text-base font-semibold">Fees & payments</h3>
                    <p class="text-sm text-[#706F6C] dark:text-[#A1A09A] leading-relaxed">Record payments, send reminders, and see who owes what at a glance. No more paper receipts.</p>
                </article>

                {{-- Attendance --}}
                <article class="md:col-span-4 bg-white dark:bg-[#161615] border border-black/5 dark:border-white/10 rounded-xl p-6 shadow-sm hover:shadow-xl hover:-translate-y-0.5 hover:border-black/15 dark:hover:border-white/20 transition-all flex flex-col gap-3">
                    <span class="w-10 h-10 rounded-[10px] grid place-items-center bg-brand-100 dark:bg-amber-950/50 text-brand-900 dark:text-amber-200">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18M9 16l2 2 4-4"/></svg>
                    </span>
                    <h3 class="text-base font-semibold">Attendance</h3>
                    <p class="text-sm text-[#706F6C] dark:text-[#A1A09A] leading-relaxed">Mark a whole class present with one tap. Parents can be notified the same day.</p>
                </article>

                {{-- Results --}}
                <article class="md:col-span-4 bg-white dark:bg-[#161615] border border-black/5 dark:border-white/10 rounded-xl p-6 shadow-sm hover:shadow-xl hover:-translate-y-0.5 hover:border-black/15 dark:hover:border-white/20 transition-all flex flex-col gap-3">
                    <span class="w-10 h-10 rounded-[10px] grid place-items-center bg-brand-100 dark:bg-amber-950/50 text-brand-900 dark:text-amber-200">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                    </span>
                    <h3 class="text-base font-semibold">Results & reports</h3>
                    <p class="text-sm text-[#706F6C] dark:text-[#A1A09A] leading-relaxed">Enter marks once; the system produces report cards, class rankings, and summaries automatically.</p>
                </article>

                {{-- Stats strip --}}
                <article class="md:col-span-6 bg-white dark:bg-[#161615] border border-black/5 dark:border-white/10 rounded-xl p-6 shadow-sm hover:shadow-xl hover:-translate-y-0.5 hover:border-black/15 dark:hover:border-white/20 transition-all">
                    <div class="grid grid-cols-2 gap-5">
                        <div>
                            <div class="text-3xl font-bold tracking-tight text-brand-700 dark:text-brand-500 leading-none">1&nbsp;click</div>
                            <div class="text-xs uppercase tracking-wider font-medium text-[#706F6C] dark:text-[#A1A09A] mt-1.5">to mark attendance</div>
                        </div>
                        <div>
                            <div class="text-3xl font-bold tracking-tight text-brand-700 dark:text-brand-500 leading-none">2&nbsp;min</div>
                            <div class="text-xs uppercase tracking-wider font-medium text-[#706F6C] dark:text-[#A1A09A] mt-1.5">to add a student</div>
                        </div>
                        <div>
                            <div class="text-3xl font-bold tracking-tight text-brand-700 dark:text-brand-500 leading-none">0</div>
                            <div class="text-xs uppercase tracking-wider font-medium text-[#706F6C] dark:text-[#A1A09A] mt-1.5">paper forms needed</div>
                        </div>
                        <div>
                            <div class="text-3xl font-bold tracking-tight text-brand-700 dark:text-brand-500 leading-none">24/7</div>
                            <div class="text-xs uppercase tracking-wider font-medium text-[#706F6C] dark:text-[#A1A09A] mt-1.5">available online</div>
                        </div>
                    </div>
                </article>

                {{-- Communication --}}
                <article class="md:col-span-6 bg-white dark:bg-[#161615] border border-black/5 dark:border-white/10 rounded-xl p-6 shadow-sm hover:shadow-xl hover:-translate-y-0.5 hover:border-black/15 dark:hover:border-white/20 transition-all flex flex-col gap-3">
                    <span class="w-10 h-10 rounded-[10px] grid place-items-center bg-brand-100 dark:bg-amber-950/50 text-brand-900 dark:text-amber-200">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    </span>
                    <h3 class="text-base font-semibold">Messages & announcements</h3>
                    <p class="text-sm text-[#706F6C] dark:text-[#A1A09A] leading-relaxed">
                        Send a message to one parent, one class, or the whole school. Everyone sees it in their
                        dashboard and by email — instantly.
                    </p>
                </article>

                {{-- Carousel --}}
                <article class="md:col-span-12">
                    <div class="carousel-container relative overflow-hidden rounded-xl border border-black/5 dark:border-white/10 bg-white dark:bg-[#161615]">
                        <div class="carousel-track flex w-[400%]">
                            <div class="w-1/4 shrink-0 p-8 flex flex-col gap-4 items-start justify-center min-h-[260px]">
                                <span class="text-xs font-medium uppercase tracking-wider text-teal-700 dark:text-teal-400">For the head teacher</span>
                                <h3 class="text-xl font-semibold">See the whole school in one screen</h3>
                                <p class="text-[#706F6C] dark:text-[#A1A09A]">
                                    Enrolment numbers, fee collection, today's attendance, and staff on duty —
                                    all on the dashboard when you sign in.
                                </p>
                            </div>
                            <div class="w-1/4 shrink-0 p-8 flex flex-col gap-4 items-start justify-center min-h-[260px]">
                                <span class="text-xs font-medium uppercase tracking-wider text-teal-700 dark:text-teal-400">For the class teacher</span>
                                <h3 class="text-xl font-semibold">Mark the register in under a minute</h3>
                                <p class="text-[#706F6C] dark:text-[#A1A09A]">
                                    Open your class, tap the names of absent students, and press save.
                                    Parents are notified automatically.
                                </p>
                            </div>
                            <div class="w-1/4 shrink-0 p-8 flex flex-col gap-4 items-start justify-center min-h-[260px]">
                                <span class="text-xs font-medium uppercase tracking-wider text-teal-700 dark:text-teal-400">For the bursar</span>
                                <h3 class="text-xl font-semibold">Know who has paid, who hasn't</h3>
                                <p class="text-[#706F6C] dark:text-[#A1A09A]">
                                    Each student's fee statement is updated the moment a payment is recorded.
                                    Send reminders with one click.
                                </p>
                            </div>
                            <div class="w-1/4 shrink-0 p-8 flex flex-col gap-4 items-start justify-center min-h-[260px]">
                                <span class="text-xs font-medium uppercase tracking-wider text-teal-700 dark:text-teal-400">For the parent</span>
                                <h3 class="text-xl font-semibold">Follow your child's progress from your phone</h3>
                                <p class="text-[#706F6C] dark:text-[#A1A09A]">
                                    Attendance, results, fee balance, and school announcements — in one place,
                                    whenever you want to check.
                                </p>
                            </div>
                        </div>
                        <div class="flex gap-2 justify-center py-3 border-t border-black/5 dark:border-white/10 text-[#1B1B18] dark:text-[#EDEDEC]" aria-hidden="true">
                            <span class="carousel-dot w-1.5 h-1.5 rounded-full opacity-25"></span>
                            <span class="carousel-dot w-1.5 h-1.5 rounded-full opacity-25"></span>
                            <span class="carousel-dot w-1.5 h-1.5 rounded-full opacity-25"></span>
                            <span class="carousel-dot w-1.5 h-1.5 rounded-full opacity-25"></span>
                        </div>
                    </div>
                </article>
            </div>
        </section>

        {{-- ============================================================
             How it works
             ============================================================ --}}
        <section id="how" class="container py-16">
            <div class="text-center max-w-2xl mx-auto mb-10">
                <h2 class="text-3xl sm:text-4xl font-bold tracking-tight mb-2">How it works</h2>
                <p class="text-[#706F6C] dark:text-[#A1A09A]">Three steps. That's it. No installation, no manual, no training day.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="relative bg-white dark:bg-[#161615] border border-black/5 dark:border-white/10 rounded-xl p-7 pt-8 hover:-translate-y-0.5 hover:shadow-lg transition-all">
                    <span class="absolute -top-3.5 left-6 w-7 h-7 rounded-full bg-brand-700 text-white grid place-items-center text-xs font-semibold ring-4 ring-[#FDFDFC] dark:ring-[#0A0A0A]">1</span>
                    <h3 class="text-base font-semibold mb-2">Sign in</h3>
                    <p class="text-sm text-[#706F6C] dark:text-[#A1A09A]">
                        You'll get a username and a password from the school office. Enter them once —
                        the system remembers you on this device.
                    </p>
                </div>
                <div class="relative bg-white dark:bg-[#161615] border border-black/5 dark:border-white/10 rounded-xl p-7 pt-8 hover:-translate-y-0.5 hover:shadow-lg transition-all">
                    <span class="absolute -top-3.5 left-6 w-7 h-7 rounded-full bg-brand-700 text-white grid place-items-center text-xs font-semibold ring-4 ring-[#FDFDFC] dark:ring-[#0A0A0A]">2</span>
                    <h3 class="text-base font-semibold mb-2">Pick what you want to do</h3>
                    <p class="text-sm text-[#706F6C] dark:text-[#A1A09A]">
                        A simple menu on the left shows everything: students, fees, attendance, results,
                        messages. Click the one you need.
                    </p>
                </div>
                <div class="relative bg-white dark:bg-[#161615] border border-black/5 dark:border-white/10 rounded-xl p-7 pt-8 hover:-translate-y-0.5 hover:shadow-lg transition-all">
                    <span class="absolute -top-3.5 left-6 w-7 h-7 rounded-full bg-brand-700 text-white grid place-items-center text-xs font-semibold ring-4 ring-[#FDFDFC] dark:ring-[#0A0A0A]">3</span>
                    <h3 class="text-base font-semibold mb-2">Click, type, done</h3>
                    <p class="text-sm text-[#706F6C] dark:text-[#A1A09A]">
                        Fill in a short form, press save, and the information is instantly available to
                        everyone who needs it.
                    </p>
                </div>
            </div>
        </section>

        {{-- ============================================================
             Data Protection
             ============================================================ --}}
        <section id="data-protection" class="container py-16">
            <div class="text-center max-w-2xl mx-auto mb-10">
                <h2 class="text-3xl sm:text-4xl font-bold tracking-tight mb-2">Your data stays at your school</h2>
                <p class="text-[#706F6C] dark:text-[#A1A09A]">
                    We built this system for Kenyan schools — and for the Kenya Data Protection
                    Act, 2019. Here's what that means for you.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">

                {{-- Highlight --}}
                <article class="md:col-span-12 bg-white dark:bg-[#161615] border border-black/5 dark:border-white/10 rounded-xl overflow-hidden shadow-sm grid grid-cols-1 lg:grid-cols-2">
                    <div class="p-7 flex flex-col gap-3 justify-center">
                        <span class="text-xs font-medium uppercase tracking-wider text-teal-700 dark:text-teal-400">Compliance</span>
                        <h3 class="text-lg font-semibold">Fully aligned with the Kenya Data Protection Act, 2019</h3>
                        <p class="text-sm text-[#706F6C] dark:text-[#A1A09A] leading-relaxed">
                            The Office of the Data Protection Commissioner (ODPC) requires schools to protect
                            the personal data of students and their families. Our system is designed from the
                            ground up to meet those obligations — so you don't have to become a data
                            protection expert.
                        </p>
                        <p class="text-[0.85rem] text-[#706F6C] dark:text-[#A1A09A]">
                            We'll work with your school to put the required policies, consents, and
                            registrations in place before we go live.
                        </p>
                    </div>
                    <div class="bg-[#FAFAF8] dark:bg-[#1D1D1B] lg:border-l border-t lg:border-t-0 border-black/5 dark:border-white/10 p-5 flex items-center justify-center">
                        <div class="w-full max-w-[280px] bg-white dark:bg-[#161615] border border-black/5 dark:border-white/10 rounded-lg p-5">
                            <div class="flex items-center gap-3 mb-4">
                                <span class="w-9 h-9 rounded-lg bg-brand-100 dark:bg-amber-950/50 grid place-items-center">
                                    <svg class="w-4.5 h-4.5 text-brand-900 dark:text-amber-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                                </span>
                                <div>
                                    <div class="font-semibold text-[#1B1B18] dark:text-[#EDEDEC]">Data Protection</div>
                                    <div class="text-xs text-[#706F6C] dark:text-[#A1A09A]">DPA 2019 · ODPC compliant</div>
                                </div>
                            </div>
                            <div class="text-xs text-[#706F6C] dark:text-[#A1A09A] leading-relaxed">
                                <div class="flex justify-between py-1 border-b border-black/5 dark:border-white/10">
                                    <span>Data storage</span>
                                    <span class="text-green-700 dark:text-green-500 font-semibold">Local · Kenya</span>
                                </div>
                                <div class="flex justify-between py-1 border-b border-black/5 dark:border-white/10">
                                    <span>Encryption</span>
                                    <span class="text-green-700 dark:text-green-500 font-semibold">On</span>
                                </div>
                                <div class="flex justify-between py-1 border-b border-black/5 dark:border-white/10">
                                    <span>Consent tracking</span>
                                    <span class="text-green-700 dark:text-green-500 font-semibold">On</span>
                                </div>
                                <div class="flex justify-between py-1">
                                    <span>Audit log</span>
                                    <span class="text-green-700 dark:text-green-500 font-semibold">On</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </article>

                {{-- Six pillars --}}
                @php
                    $pillars = [
                        ['title' => 'Data stays at your school', 'body' => 'The server is installed at your school and holds all student and parent data. Nothing is stored outside the premises — no cloud, no third-party access.', 'icon' => 'M3 12l9-9 9 9M5 10v10a1 1 0 0 0 1 1h4v-6h4v6h4a1 1 0 0 0 1-1V10'],
                        ['title' => 'Encrypted, always', 'body' => 'Student records and parent contact details are encrypted both when stored and when sent over the network. Backups are encrypted too.', 'icon' => 'M4 11h16v10H4zM8 11V7a4 4 0 0 1 8 0v4'],
                        ['title' => 'Only authorised staff', 'body' => 'Each staff member has their own account. The system tracks who viewed, changed, or sent what — and when.', 'icon' => 'M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2M12 3a4 4 0 1 0 0 8 4 4 0 0 0 0-8z'],
                        ['title' => 'Parental consent', 'body' => 'Parents decide whether to receive SMS updates about their child. Consent is recorded and can be withdrawn at any time.', 'icon' => 'M9 11l3 3L22 4M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11'],
                        ['title' => 'Full audit trail', 'body' => 'Every action — sending a message, editing a record, recording a payment — is logged with the user and timestamp.', 'icon' => 'M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zM12 6v6l4 2'],
                        ['title' => 'Breach procedures in place', 'body' => 'Documented incident-response procedures, including notification to the ODPC within 72 hours if ever required.', 'icon' => 'M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8zM14 2v6h6M16 13H8M16 17H8M10 9H8'],
                    ];
                @endphp

                @foreach ($pillars as $pillar)
                    <article class="md:col-span-4 bg-white dark:bg-[#161615] border border-black/5 dark:border-white/10 rounded-xl p-6 shadow-sm hover:shadow-xl hover:-translate-y-0.5 hover:border-black/15 dark:hover:border-white/20 transition-all flex flex-col gap-3">
                        <span class="w-10 h-10 rounded-[10px] grid place-items-center bg-brand-100 dark:bg-amber-950/50 text-brand-900 dark:text-amber-200">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $pillar['icon'] }}"/></svg>
                        </span>
                        <h3 class="text-base font-semibold">{{ $pillar['title'] }}</h3>
                        <p class="text-sm text-[#706F6C] dark:text-[#A1A09A] leading-relaxed">{{ $pillar['body'] }}</p>
                    </article>
                @endforeach
            </div>
        </section>

        {{-- ============================================================
             Roles
             ============================================================ --}}
        <section id="roles" class="container py-16">
            <div class="text-center max-w-2xl mx-auto mb-10">
                <h2 class="text-3xl sm:text-4xl font-bold tracking-tight mb-2">Built for everyone at the school</h2>
                <p class="text-[#706F6C] dark:text-[#A1A09A]">Different people see different things — exactly what they need, nothing they don't.</p>
            </div>

            @php
                $roles = [
                    ['title' => 'School administrators', 'items' => ['Add and manage staff accounts', 'See school-wide reports', 'Set fee structures and terms', 'Broadcast announcements']],
                    ['title' => 'Teachers', 'items' => ['Mark daily attendance', 'Enter exam marks', 'View their class list', 'Message parents directly']],
                    ['title' => 'Bursar & finance', 'items' => ['Record fee payments', 'Print receipts and statements', 'Track outstanding balances', 'Generate termly reports']],
                    ['title' => 'Parents & guardians', 'items' => ["View their child's attendance", 'See exam results and reports', 'Check the fee balance any time', 'Receive school announcements']],
                ];
            @endphp

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach ($roles as $role)
                    <div class="bg-white dark:bg-[#161615] border border-black/5 dark:border-white/10 rounded-xl p-6 flex flex-col gap-3 hover:border-brand-700 hover:-translate-y-0.5 transition-all">
                        <h3 class="text-base font-semibold">{{ $role['title'] }}</h3>
                        <ul class="flex flex-col gap-2 text-sm text-[#706F6C] dark:text-[#A1A09A]">
                            @foreach ($role['items'] as $item)
                                <li class="flex gap-2 items-start">
                                    <svg class="w-3.5 h-3.5 text-green-700 dark:text-green-500 shrink-0 mt-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                                    {{ $item }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- ============================================================
             Pricing — Perpetual Licence Model
             ============================================================ --}}
        <section id="pricing" class="container py-16">
            <div class="text-center max-w-2xl mx-auto mb-10">
                <h2 class="text-3xl sm:text-4xl font-bold tracking-tight mb-2">You buy it once. It's yours to keep.</h2>
                <p class="text-[#706F6C] dark:text-[#A1A09A]">
                    A perpetual licence with no per-student fees and no monthly subscription.
                    Here's exactly what you pay for.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 items-stretch">

                {{-- Core package --}}
                <article class="relative bg-white dark:bg-[#161615] border-2 border-brand-700 rounded-3xl p-8 flex flex-col gap-5 shadow-md hover:shadow-xl hover:-translate-y-0.5 transition-all">
                    <span class="absolute -top-3 left-1/2 -translate-x-1/2 bg-brand-700 text-white text-xs font-semibold tracking-wider uppercase px-3.5 py-1 rounded-full whitespace-nowrap shadow-md">
                        Complete Package
                    </span>

                    <header>
                        <h3 class="text-xl font-bold tracking-tight mb-1">School Management System</h3>
                        <p class="text-sm text-[#706F6C] dark:text-[#A1A09A]">
                            Everything your school needs, installed on your premises and licensed to you forever.
                        </p>
                    </header>

                    <div class="pb-5 border-b border-black/5 dark:border-white/10">
                        <div class="text-4xl font-bold tracking-tight text-[#1B1B18] dark:text-[#EDEDEC] leading-none">
                            KES 300,000
                        </div>
                        <div class="text-sm text-[#706F6C] dark:text-[#A1A09A] mt-2">
                            One-time · Unlimited students · No recurring charges
                        </div>
                    </div>

                    <ul class="flex flex-col gap-3 text-sm text-[#706F6C] dark:text-[#A1A09A] grow">
                        <li class="flex gap-2.5 items-start"><svg class="w-3.5 h-3.5 text-green-700 dark:text-green-500 shrink-0 mt-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>Hardware kit — server, cabinet, UPS, backup drive (KES 120,000)</li>
                        <li class="flex gap-2.5 items-start"><svg class="w-3.5 h-3.5 text-green-700 dark:text-green-500 shrink-0 mt-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>Perpetual software licence — unlimited students (KES 140,000)</li>
                        <li class="flex gap-2.5 items-start"><svg class="w-3.5 h-3.5 text-green-700 dark:text-green-500 shrink-0 mt-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>Installation, setup & staff training (KES 40,000)</li>
                        <li class="flex gap-2.5 items-start"><svg class="w-3.5 h-3.5 text-green-700 dark:text-green-500 shrink-0 mt-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>Student & staff records, fees, attendance, results</li>
                        <li class="flex gap-2.5 items-start"><svg class="w-3.5 h-3.5 text-green-700 dark:text-green-500 shrink-0 mt-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>Data protection setup (DPA 2019 compliant)</li>
                        <li class="flex gap-2.5 items-start"><svg class="w-3.5 h-3.5 text-green-700 dark:text-green-500 shrink-0 mt-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>Full documentation & user manual</li>
                        <li class="flex gap-2.5 items-start font-semibold text-[#1B1B18] dark:text-[#EDEDEC]"><svg class="w-3.5 h-3.5 text-green-700 dark:text-green-500 shrink-0 mt-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>Unlimited students — no per-student fee, ever</li>
                    </ul>

                    <a href="#contact" class="inline-flex items-center justify-center rounded-2xl shadow-md px-6 py-3 text-sm font-medium bg-brand-700 text-white hover:brightness-110 transition w-full">Request a Quote</a>
                </article>

                {{-- Add-ons --}}
                <article class="bg-white dark:bg-[#161615] border border-black/5 dark:border-white/10 rounded-3xl p-8 flex flex-col gap-5 shadow-sm hover:shadow-xl hover:-translate-y-0.5 transition-all">
                    <header>
                        <h3 class="text-xl font-bold tracking-tight mb-1">Optional add-ons</h3>
                        <p class="text-sm text-[#706F6C] dark:text-[#A1A09A]">
                            Add these when you're ready. They're not required to use the system.
                        </p>
                    </header>

                    <div class="pb-5 border-b border-black/5 dark:border-white/10">
                        <div class="text-4xl font-bold tracking-tight text-[#1B1B18] dark:text-[#EDEDEC] leading-none">
                            From KES 50,000
                        </div>
                        <div class="text-sm text-[#706F6C] dark:text-[#A1A09A] mt-2">
                            Purchased separately when you need them
                        </div>
                    </div>

                    <ul class="flex flex-col gap-3 text-sm text-[#706F6C] dark:text-[#A1A09A] grow">
                        <li class="flex gap-2.5 items-start">
                            <svg class="w-3.5 h-3.5 text-green-700 dark:text-green-500 shrink-0 mt-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                            <div>
                                <strong class="text-[#1B1B18] dark:text-[#EDEDEC]">SMS communication module — KES 50,000</strong>
                                <div class="text-[0.8rem] mt-0.5">Bulk SMS, personalised fee reminders with balances, templates, and delivery logs.</div>
                            </div>
                        </li>
                        <li class="flex gap-2.5 items-start">
                            <svg class="w-3.5 h-3.5 text-green-700 dark:text-green-500 shrink-0 mt-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                            <div>
                                <strong class="text-[#1B1B18] dark:text-[#EDEDEC]">Annual support & maintenance — KES 60,000/year</strong>
                                <div class="text-[0.8rem] mt-0.5">Software updates, bug fixes, remote support, and quarterly health checks. Optional after year one.</div>
                            </div>
                        </li>
                        <li class="flex gap-2.5 items-start">
                            <svg class="w-3.5 h-3.5 text-green-700 dark:text-green-500 shrink-0 mt-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                            <div>
                                <strong class="text-[#1B1B18] dark:text-[#EDEDEC]">SMS credits — billed at cost</strong>
                                <div class="text-[0.8rem] mt-0.5">Approximately KES 0.80 per SMS. Buy in blocks as needed.</div>
                            </div>
                        </li>
                    </ul>

                    <a href="#contact" class="inline-flex items-center justify-center rounded-2xl shadow-md px-6 py-3 text-sm font-medium border dark:border-white/20 hover:border-[#1B1B18] dark:hover:border-white transition w-full">Ask About Add-ons</a>
                </article>
            </div>

            {{-- Itemised breakdown --}}
            <div class="mt-8 bg-white dark:bg-[#161615] border border-black/5 dark:border-white/10 rounded-3xl p-8">
                <h3 class="text-lg font-bold tracking-tight mb-1">What makes up the KES 335,000</h3>
                <p class="text-sm text-[#706F6C] dark:text-[#A1A09A] mb-6">You're buying three distinct things — here's the breakdown.</p>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left">
                                <th class="text-xs uppercase tracking-wider text-[#706F6C] dark:text-[#A1A09A] font-semibold py-3 border-b border-black/5 dark:border-white/10">Item</th>
                                <th class="text-xs uppercase tracking-wider text-[#706F6C] dark:text-[#A1A09A] font-semibold py-3 border-b border-black/5 dark:border-white/10 text-right">Price (KES)</th>
                            </tr>
                        </thead>
                        <tbody class="text-[#706F6C] dark:text-[#A1A09A]">
                            <tr>
                                <td class="py-3.5 border-b border-black/5 dark:border-white/10">
                                    <span class="font-medium text-[#1B1B18] dark:text-[#EDEDEC]">Hardware kit</span>
                                    <span class="block text-xs text-[#706F6C] dark:text-[#A1A09A] mt-0.5">Server, data cabinet, UPS with AVR, encrypted backup drive</span>
                                </td>
                                <td class="py-3.5 border-b border-black/5 dark:border-white/10 text-right font-semibold text-[#1B1B18] dark:text-[#EDEDEC] whitespace-nowrap">120,000</td>
                            </tr>
                            <tr>
                                <td class="py-3.5 border-b border-black/5 dark:border-white/10">
                                    <span class="font-medium text-[#1B1B18] dark:text-[#EDEDEC]">Perpetual software licence</span>
                                    <span class="block text-xs text-[#706F6C] dark:text-[#A1A09A] mt-0.5">One-time licence — unlimited students, no expiry</span>
                                </td>
                                <td class="py-3.5 border-b border-black/5 dark:border-white/10 text-right font-semibold text-[#1B1B18] dark:text-[#EDEDEC] whitespace-nowrap">140,000</td>
                            </tr>
                            <tr>
                                <td class="py-3.5 border-b border-black/5 dark:border-white/10">
                                    <span class="font-medium text-[#1B1B18] dark:text-[#EDEDEC]">Installation & training</span>
                                    <span class="block text-xs text-[#706F6C] dark:text-[#A1A09A] mt-0.5">On-site setup, network config, data protection setup, staff training</span>
                                </td>
                                <td class="py-3.5 border-b border-black/5 dark:border-white/10 text-right font-semibold text-[#1B1B18] dark:text-[#EDEDEC] whitespace-nowrap">40,000</td>
                            </tr>
                            <tr>
                                <td class="py-5 border-t-2 border-black/15 dark:border-white/20 text-base font-bold text-[#1B1B18] dark:text-[#EDEDEC]">Total — Complete Package</td>
                                <td class="py-5 border-t-2 border-black/15 dark:border-white/20 text-right text-base font-bold text-[#1B1B18] dark:text-[#EDEDEC] whitespace-nowrap">300,000</td>
                            </tr>
                            <tr class="italic">
                                <td class="py-3 border-b border-black/5 dark:border-white/10">
                                    SMS module <em class="not-italic font-normal text-xs opacity-70">(optional)</em>
                                    <span class="block text-xs not-italic mt-0.5">Bulk SMS engine with personalisation and delivery logging</span>
                                </td>
                                <td class="py-3 border-b border-black/5 dark:border-white/10 text-right font-semibold whitespace-nowrap">50,000</td>
                            </tr>
                            <tr class="italic">
                                <td class="py-3">
                                    Annual support <em class="not-italic font-normal text-xs opacity-70">(optional, from year 2)</em>
                                    <span class="block text-xs not-italic mt-0.5">Updates, remote support, quarterly health checks</span>
                                </td>
                                <td class="py-3 text-right font-semibold whitespace-nowrap">60,000 / year</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <p class="mt-7 text-center text-sm text-[#706F6C] dark:text-[#A1A09A] max-w-3xl mx-auto leading-relaxed">
                <strong class="text-[#1B1B18] dark:text-[#EDEDEC]">All prices exclude VAT (16%).</strong><br>
                No monthly subscriptions. No per-student fees. No limit on the number of students.<br>
                Payment terms: 60% deposit on acceptance, 40% on completion of installation and training.
            </p>
        </section>

        {{-- ============================================================
             CTA
             ============================================================ --}}
        <section class="container py-16" id="contact">
            <div class="bg-white dark:bg-[#161615] border border-black/5 dark:border-white/10 rounded-3xl p-10 text-center shadow-md">
                <h2 class="text-2xl sm:text-3xl font-bold tracking-tight mb-2">Ready to get started?</h2>
                <p class="text-[#706F6C] dark:text-[#A1A09A] max-w-xl mx-auto mb-6">
                    Already installed at your school? Sign in with the credentials from the office.
                    Interested in bringing this to your school? Request a quote — installation takes
                    about 6 weeks, and we handle the data protection setup with you.
                </p>
                <div class="flex flex-wrap gap-3 justify-center">
                    <a href="{{ url('/admin/login') }}" class="inline-flex items-center justify-center rounded-xl shadow-md px-6 py-3 text-sm font-medium bg-brand-700 text-white hover:brightness-110 transition">Sign in now</a>
                    <a href="mailto:{{ config('school.email') }}" class="inline-flex items-center justify-center rounded-xl shadow-md px-6 py-3 text-sm font-medium border border-black/15 dark:border-white/20 hover:border-[#1B1B18] dark:hover:border-white transition">Request a quote</a>
                </div>
                <p class="text-xs text-[#706F6C] dark:text-[#A1A09A] mt-5 mb-0">
                    <svg class="inline w-3.5 h-3.5 -mt-0.5 text-green-700 dark:text-green-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    Your data is stored at your school and never transferred outside Kenya.
                </p>
            </div>
        </section>
    </main>

    {{-- ============================================================
         Footer
         ============================================================ --}}
    <footer class="border-t border-black/5 dark:border-white/10 py-10 mt-8 text-sm text-[#706F6C] dark:text-[#A1A09A]">
        <div class="container">
            <div class="grid grid-cols-1 md:grid-cols-[2fr_1fr_1fr] gap-6 mb-6">
                <div>
                    <h4 class="text-[#1B1B18] dark:text-[#EDEDEC] text-sm font-semibold mb-3">{{ config('school.name') }}</h4>
                    <p class="max-w-sm">
                        {{ config('school.motto') }}.
                        Manage students, fees, attendance, and communication from one place.
                        Data stored on-site, compliant with the Kenya Data Protection Act, 2019.
                    </p>
                </div>
                <div>
                    <h4 class="text-[#1B1B18] dark:text-[#EDEDEC] text-sm font-semibold mb-3">Quick links</h4>
                    <ul class="flex flex-col gap-1.5">
                        <li><a href="#features" class="hover:text-[#1B1B18] dark:hover:text-[#EDEDEC]">What it does</a></li>
                        <li><a href="#how" class="hover:text-[#1B1B18] dark:hover:text-[#EDEDEC]">How it works</a></li>
                        <li><a href="#data-protection" class="hover:text-[#1B1B18] dark:hover:text-[#EDEDEC]">Your data</a></li>
                        <li><a href="#roles" class="hover:text-[#1B1B18] dark:hover:text-[#EDEDEC]">For everyone</a></li>
                        <li><a href="#pricing" class="hover:text-[#1B1B18] dark:hover:text-[#EDEDEC]">Pricing</a></li>
                        <li><a href="{{ url('/admin/login') }}" class="hover:text-[#1B1B18] dark:hover:text-[#EDEDEC]">Sign in</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-[#1B1B18] dark:text-[#EDEDEC] text-sm font-semibold mb-3">Contact</h4>
                    <ul class="flex flex-col gap-1.5">
                        <li>{{ config('school.address') }}</li>
                        <li><a href="tel:{{ config('school.phone') }}" class="hover:text-[#1B1B18] dark:hover:text-[#EDEDEC]">{{ config('school.phone') }}</a></li>
                        <li><a href="mailto:{{ config('school.email') }}" class="hover:text-[#1B1B18] dark:hover:text-[#EDEDEC]">{{ config('school.email') }}</a></li>
                    </ul>
                </div>
            </div>

            <div class="pt-4 border-t border-black/5 dark:border-white/10 flex flex-wrap justify-between gap-2 text-xs">
                <span>&copy; {{ date('Y') }} {{ config('school.name') }}. All rights reserved.</span>
                <span class="flex gap-4 flex-wrap">
                    <a href="#data-protection" class="hover:text-[#1B1B18] dark:hover:text-[#EDEDEC]">Data Protection</a>
                    <span>SMS v{{ app()->version() }}</span>
                </span>
            </div>
        </div>
    </footer>

    {{-- Alpine.js for the mobile menu (matches Filament's Alpine) --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>