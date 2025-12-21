{{-- Step 0: Mode Sélection et Catalogue --}}
<div class="max-w-3xl mx-auto">
    <div class="text-center mb-8">
        <div class="text-6xl mb-4">🌌</div>
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
            Bienvenue dans le Target Planner
        </h2>
        <p class="text-gray-600 dark:text-gray-400">
            Choisissez comment vous souhaitez créer votre cible d'observation
        </p>
    </div>

    {{-- Bouton retour au catalogue si une target est sélectionnée --}}
    <div x-show="creationMode === 'assisted' && target.target_name" x-transition class="mb-6">
        <button
            @click="cancelTemplateSelection()"
            class="flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg transition-all"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span class="font-medium">Retour au catalogue</span>
        </button>
    </div>

    {{-- Mode Selection --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        {{-- Mode Assisté --}}
        <button
            @click="setMode('assisted')"
            class="group relative bg-gradient-to-br from-blue-50 to-purple-50 dark:from-blue-900/20 dark:to-purple-900/20 rounded-xl p-6 border-2 hover:border-blue-500 dark:hover:border-blue-400 transition-all cursor-pointer"
            :class="creationMode === 'assisted' ? 'border-blue-500 dark:border-blue-400 ring-4 ring-blue-500/20' : 'border-gray-200 dark:border-gray-700'"
        >
            <div class="flex flex-col items-center text-center">
                <div class="w-16 h-16 rounded-full bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Mode Assisté</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                    Parfait pour les débutants
                </p>
                <ul class="text-xs text-left text-gray-700 dark:text-gray-300 space-y-1">
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        Catalogue d'objets populaires
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        Paramètres préconfigurés
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        Explications détaillées
                    </li>
                </ul>
            </div>
        </button>

        {{-- Mode Manuel --}}
        <button
            @click="setMode('manual')"
            class="group relative bg-gradient-to-br from-gray-50 to-slate-50 dark:from-gray-800 dark:to-slate-800 rounded-xl p-6 border-2 hover:border-purple-500 dark:hover:border-purple-400 transition-all cursor-pointer"
            :class="creationMode === 'manual' ? 'border-purple-500 dark:border-purple-400 ring-4 ring-purple-500/20' : 'border-gray-200 dark:border-gray-700'"
        >
            <div class="flex flex-col items-center text-center">
                <div class="w-16 h-16 rounded-full bg-purple-100 dark:bg-purple-900/40 flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Mode Manuel</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                    Pour les utilisateurs expérimentés
                </p>
                <ul class="text-xs text-left text-gray-700 dark:text-gray-300 space-y-1">
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-purple-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        Contrôle total des paramètres
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-purple-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        Coordonnées personnalisées
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-purple-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        Configuration avancée
                    </li>
                </ul>
            </div>
        </button>
    </div>

    {{-- Catalogue d'objets (Mode Assisté) --}}
    <div x-show="creationMode === 'assisted'" x-transition class="mt-8">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 text-center">
            📖 Choisissez votre cible
        </h3>

        {{-- Filtres par difficulté --}}
        <div class="flex justify-center gap-2 mb-6">
            <button
                @click="catalogFilter = 'all'"
                class="px-4 py-2 rounded-lg text-sm font-medium transition"
                :class="catalogFilter === 'all' ? 'bg-blue-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'"
            >
                Tous
            </button>
            <button
                @click="catalogFilter = 'beginner'"
                class="px-4 py-2 rounded-lg text-sm font-medium transition"
                :class="catalogFilter === 'beginner' ? 'bg-green-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'"
            >
                🌱 Débutant
            </button>
            <button
                @click="catalogFilter = 'intermediate'"
                class="px-4 py-2 rounded-lg text-sm font-medium transition"
                :class="catalogFilter === 'intermediate' ? 'bg-yellow-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'"
            >
                ⭐ Intermédiaire
            </button>
            <button
                @click="catalogFilter = 'advanced'"
                class="px-4 py-2 rounded-lg text-sm font-medium transition"
                :class="catalogFilter === 'advanced' ? 'bg-red-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'"
            >
                🚀 Avancé
            </button>
        </div>

        {{-- Liste des cibles avec design moderne et spacieux --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 max-h-[800px] overflow-y-auto pr-2 custom-scrollbar">
            <template x-for="(target, index) in getFilteredCatalog()" :key="target.id">
                <div
                    x-data="{ isHovered: false }"
                    @mouseenter="isHovered = true"
                    @mouseleave="isHovered = false"
                    class="target-card-wrapper"
                    :style="{ 'animation-delay': (index * 0.1) + 's' }"
                >
                    <button
                        @click="loadTemplateTarget(target)"
                        class="relative w-full text-left group"
                    >
                        {{-- Main card with glassmorphism effect --}}
                        <div class="relative bg-white/90 dark:bg-gray-800/90 backdrop-blur-xl rounded-2xl overflow-hidden border border-gray-200/50 dark:border-gray-700/50 shadow-lg transition-all duration-500 ease-out"
                             :class="{ 'shadow-2xl scale-105 -translate-y-2 border-blue-500/50': isHovered }"
                             style="transform-style: preserve-3d;">

                            {{-- Shimmer effect on hover --}}
                            <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none overflow-hidden">
                                <div class="absolute inset-0 shimmer-effect"></div>
                            </div>

                            {{-- Image container with advanced effects - PLUS GRANDE --}}
                            <div
                                x-show="target.thumbnail_image || target.preview_image"
                                class="relative h-80 bg-gradient-to-br from-gray-900 via-gray-800 to-black overflow-hidden"
                            >
                                {{-- Background stars pattern --}}
                                <div class="absolute inset-0 stars-bg opacity-30"></div>

                                {{-- Main image with parallax-like effect --}}
                                <img
                                    :src="target.thumbnail_image || target.preview_image"
                                    :alt="target.name"
                                    class="absolute inset-0 w-full h-full object-cover transition-all duration-700 ease-out"
                                    :class="{ 'scale-110 rotate-1': isHovered }"
                                    loading="lazy"
                                />

                                {{-- Multi-layer gradient overlays --}}
                                <div class="absolute inset-0 bg-gradient-to-t from-black via-black/40 to-transparent opacity-80"></div>
                                <div class="absolute inset-0 bg-gradient-to-br from-blue-500/10 via-purple-500/10 to-pink-500/10 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>

                                {{-- Animated glow effect --}}
                                <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-700">
                                    <div class="absolute -top-1/2 -left-1/2 w-full h-full bg-blue-500/20 blur-3xl animate-pulse-slow"></div>
                                    <div class="absolute -bottom-1/2 -right-1/2 w-full h-full bg-purple-500/20 blur-3xl animate-pulse-slower"></div>
                                </div>

                                {{-- Content on image --}}
                                <div class="absolute inset-0 flex flex-col justify-between p-4">
                                    {{-- Top badges --}}
                                    <div class="flex justify-between items-start">
                                        {{-- Difficulty badge with glow --}}
                                        <span
                                            class="px-3 py-1.5 rounded-full text-xs font-bold backdrop-blur-md border transition-all duration-300"
                                            :class="{
                                                'bg-green-500/90 text-white border-green-400/50 shadow-lg shadow-green-500/50': target.difficulty === 'beginner',
                                                'bg-yellow-500/90 text-white border-yellow-400/50 shadow-lg shadow-yellow-500/50': target.difficulty === 'intermediate',
                                                'bg-red-500/90 text-white border-red-400/50 shadow-lg shadow-red-500/50': target.difficulty === 'advanced'
                                            }"
                                            x-text="target.difficulty === 'beginner' ? '🌱 Débutant' : target.difficulty === 'intermediate' ? '⭐ Inter.' : '🚀 Avancé'"
                                        ></span>

                                        {{-- Type badge --}}
                                        <span class="px-3 py-1.5 rounded-full text-xs font-medium bg-white/20 dark:bg-black/30 text-white backdrop-blur-md border border-white/30"
                                              x-text="target.type"
                                        ></span>
                                    </div>

                                    {{-- Bottom title area with slide-up animation --}}
                                    <div class="transform transition-all duration-500"
                                         :class="{ 'translate-y-0': !isHovered, '-translate-y-1': isHovered }">
                                        <h4 class="text-white font-bold text-lg mb-1 drop-shadow-lg leading-tight"
                                            x-text="target.name"></h4>
                                        <p class="text-white/90 text-sm drop-shadow-md flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            </svg>
                                            <span x-text="target.constellation"></span>
                                        </p>
                                    </div>
                                </div>

                                {{-- Scan line effect on hover --}}
                                <div class="absolute inset-0 overflow-hidden opacity-0 group-hover:opacity-100 transition-opacity duration-700">
                                    <div class="scan-line"></div>
                                </div>
                            </div>

                            {{-- No image fallback with modern design - PLUS GRAND --}}
                            <div
                                x-show="!target.thumbnail_image && !target.preview_image"
                                class="relative h-80 bg-gradient-to-br from-indigo-500/20 via-purple-500/20 to-pink-500/20 flex items-center justify-center overflow-hidden"
                            >
                                <div class="absolute inset-0 stars-bg opacity-20"></div>
                                <div class="relative text-center z-10">
                                    <div class="text-7xl mb-3 animate-float">🌌</div>
                                    <h4 class="text-gray-900 dark:text-white font-bold text-lg" x-text="target.name"></h4>
                                    <p class="text-gray-600 dark:text-gray-400 text-sm" x-text="target.constellation"></p>
                                </div>
                            </div>

                            {{-- Card content with enhanced design - PLUS SPACIEUX ET LISIBLE --}}
                            <div class="p-6 relative">
                                {{-- Animated border top --}}
                                <div class="absolute top-0 left-0 right-0 h-0.5 bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500 transform origin-left scale-x-0 group-hover:scale-x-100 transition-transform duration-500"></div>

                                {{-- Description courte --}}
                                <div class="mb-4">
                                    <h5 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">À propos</h5>
                                    <p class="text-base text-gray-700 dark:text-gray-300 leading-relaxed transition-all duration-300"
                                       :class="{ 'text-gray-900 dark:text-gray-100': isHovered }"
                                       x-text="target.description"></p>
                                </div>

                                {{-- Description complète si disponible --}}
                                <div x-show="target.full_description" class="mb-4 pb-4 border-b border-gray-200 dark:border-gray-700">
                                    <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed"
                                       x-text="target.full_description"></p>
                                </div>

                                {{-- Conseils si disponibles --}}
                                <div x-show="target.tips" class="mb-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                                    <div class="flex items-start gap-2">
                                        <span class="text-lg">💡</span>
                                        <div class="flex-1">
                                            <p class="text-xs font-semibold text-blue-900 dark:text-blue-100 mb-1">Conseil</p>
                                            <p class="text-sm text-blue-800 dark:text-blue-200" x-text="target.tips"></p>
                                        </div>
                                    </div>
                                </div>

                                {{-- Stats row with icons - PLUS GRANDS --}}
                                <div class="flex items-center justify-between text-sm mb-5">
                                    <div class="flex items-center gap-1.5 text-gray-500 dark:text-gray-400 transition-colors duration-300"
                                         :class="{ 'text-blue-600 dark:text-blue-400': isHovered }">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <span class="font-medium" x-text="target.estimated_time || 'N/A'"></span>
                                    </div>
                                    <div x-show="target.best_months && target.best_months.length > 0"
                                         class="flex items-center gap-1.5 text-gray-500 dark:text-gray-400 transition-colors duration-300"
                                         :class="{ 'text-purple-600 dark:text-purple-400': isHovered }">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        <span class="font-medium" x-text="target.best_months.slice(0, 2).join(', ')"></span>
                                    </div>
                                </div>

                                {{-- Action button with gradient --}}
                                <div class="relative overflow-hidden rounded-lg">
                                    <div class="absolute inset-0 bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                                    <div class="relative px-4 py-2.5 bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500 group-hover:from-blue-600 group-hover:via-purple-600 group-hover:to-pink-600 transition-all duration-500 flex items-center justify-center gap-2 text-white font-semibold text-sm">
                                        <svg class="w-4 h-4 transition-transform duration-300"
                                             :class="{ 'translate-x-1': isHovered }"
                                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                        </svg>
                                        <span>Sélectionner cette cible</span>
                                        <svg class="w-4 h-4 transition-transform duration-300"
                                             :class="{ 'translate-x-1': isHovered }"
                                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Floating particles effect --}}
                        <div class="absolute inset-0 pointer-events-none overflow-hidden rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-700">
                            <div class="particle particle-1"></div>
                            <div class="particle particle-2"></div>
                            <div class="particle particle-3"></div>
                        </div>
                    </button>
                </div>
            </template>
        </div>

        {{-- Custom CSS for animations --}}
        <style>
            /* Card entrance animation */
            .target-card-wrapper {
                animation: slideInUp 0.6s ease-out forwards;
                opacity: 0;
            }

            @keyframes slideInUp {
                from {
                    opacity: 0;
                    transform: translateY(30px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            /* Shimmer effect */
            .shimmer-effect {
                background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
                animation: shimmer 2s infinite;
            }

            @keyframes shimmer {
                0% { transform: translateX(-100%); }
                100% { transform: translateX(100%); }
            }

            /* Scan line effect */
            .scan-line {
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                height: 2px;
                background: linear-gradient(90deg, transparent, rgba(59, 130, 246, 0.8), transparent);
                animation: scan 3s linear infinite;
            }

            @keyframes scan {
                0% { transform: translateY(0); }
                100% { transform: translateY(320px); }
            }

            /* Stars background */
            .stars-bg {
                background-image:
                    radial-gradient(2px 2px at 20px 30px, white, transparent),
                    radial-gradient(2px 2px at 60px 70px, white, transparent),
                    radial-gradient(1px 1px at 50px 50px, white, transparent),
                    radial-gradient(1px 1px at 130px 80px, white, transparent),
                    radial-gradient(2px 2px at 90px 10px, white, transparent);
                background-size: 200px 200px;
                animation: twinkle 4s ease-in-out infinite;
            }

            @keyframes twinkle {
                0%, 100% { opacity: 0.3; }
                50% { opacity: 0.6; }
            }

            /* Floating animation */
            @keyframes float {
                0%, 100% { transform: translateY(0px); }
                50% { transform: translateY(-10px); }
            }

            .animate-float {
                animation: float 3s ease-in-out infinite;
            }

            /* Pulse animations */
            @keyframes pulse-slow {
                0%, 100% { opacity: 0.3; }
                50% { opacity: 0.5; }
            }

            @keyframes pulse-slower {
                0%, 100% { opacity: 0.2; }
                50% { opacity: 0.4; }
            }

            .animate-pulse-slow {
                animation: pulse-slow 4s ease-in-out infinite;
            }

            .animate-pulse-slower {
                animation: pulse-slower 6s ease-in-out infinite;
            }

            /* Floating particles */
            .particle {
                position: absolute;
                width: 4px;
                height: 4px;
                background: white;
                border-radius: 50%;
                opacity: 0;
            }

            .particle-1 {
                top: 20%;
                left: 20%;
                animation: float-particle 3s ease-in-out infinite;
            }

            .particle-2 {
                top: 60%;
                right: 30%;
                animation: float-particle 4s ease-in-out infinite 0.5s;
            }

            .particle-3 {
                bottom: 30%;
                left: 70%;
                animation: float-particle 5s ease-in-out infinite 1s;
            }

            @keyframes float-particle {
                0%, 100% {
                    opacity: 0;
                    transform: translate(0, 0);
                }
                50% {
                    opacity: 0.6;
                    transform: translate(10px, -20px);
                }
            }

            /* Custom scrollbar */
            .custom-scrollbar::-webkit-scrollbar {
                width: 8px;
            }

            .custom-scrollbar::-webkit-scrollbar-track {
                background: rgba(0, 0, 0, 0.1);
                border-radius: 10px;
            }

            .custom-scrollbar::-webkit-scrollbar-thumb {
                background: linear-gradient(180deg, #3B82F6, #8B5CF6);
                border-radius: 10px;
            }

            .custom-scrollbar::-webkit-scrollbar-thumb:hover {
                background: linear-gradient(180deg, #2563EB, #7C3AED);
            }

            /* Line clamp utility */
            .line-clamp-2 {
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
                overflow: hidden;
            }
        </style>

        <div class="mt-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                <div class="text-sm text-blue-800 dark:text-blue-200">
                    <p class="font-semibold mb-1">💡 Conseil</p>
                    <p>Sélectionnez une cible du catalogue pour charger automatiquement ses coordonnées et les paramètres d'acquisition recommandés. Vous pourrez ensuite les personnaliser si nécessaire.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Bouton Continuer --}}
    <div class="mt-8 text-center">
        <button
            @click="startCreation()"
            :disabled="!creationMode"
            class="px-8 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium disabled:opacity-50 disabled:cursor-not-allowed"
        >
            <span x-show="creationMode === 'assisted'">Commencer avec le mode assisté →</span>
            <span x-show="creationMode === 'manual'">Créer manuellement →</span>
            <span x-show="!creationMode">Choisissez un mode pour continuer</span>
        </button>
    </div>
</div>
