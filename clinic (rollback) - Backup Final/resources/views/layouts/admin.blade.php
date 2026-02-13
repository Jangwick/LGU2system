<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('logo/clinic-logo.png') }}">
    <title>{{ config('app.name', 'Clinic Management System') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="//unpkg.com/alpinejs" defer></script>

    <style>
        * {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }

        /* Enhanced Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.05);
        }

        ::-webkit-scrollbar-thumb {
            background: rgba(0, 0, 0, 0.2);
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: rgba(0, 0, 0, 0.3);
        }

        /* Sidebar Animations */
        .sidebar-hidden {
            transform: translateX(-100%);
        }

        .sidebar-mini {
            width: 70px !important;
        }

        .sidebar-mini .sidebar-text {
            opacity: 0;
            transform: translateX(-10px);
        }

        .sidebar-mini .mini-icon {
            transform: scale(1.1);
        }

        /* Navigation Enhancements */
        .nav-item {
            position: relative;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .nav-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 4px;
            height: 0;
            background: linear-gradient(135deg, #60a5fa, #3b82f6);
            border-radius: 0 2px 2px 0;
            transition: height 0.3s ease;
        }

        .nav-item.active::before,
        .nav-item:hover::before {
            height: 70%;
        }

        .nav-item:hover {
            background: linear-gradient(135deg, rgba(96, 165, 250, 0.1), rgba(59, 130, 246, 0.05));
            transform: translateX(4px);
        }

        .nav-item.active {
            background: linear-gradient(135deg, rgba(96, 165, 250, 0.15), rgba(59, 130, 246, 0.1));
            border-right: 3px solid #3b82f6;
        }

        /* Glass Effect */
        .glass-effect {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        /* Loading States */
        .loading-pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        /* Floating Action Styles */
        .floating-button {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            z-index: 1000;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            box-shadow: 0 10px 25px rgba(59, 130, 246, 0.3);
            transition: all 0.3s ease;
        }

        .floating-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 35px rgba(59, 130, 246, 0.4);
        }

        /* Mobile Optimizations */
        @media (max-width: 768px) {
            .sidebar-hidden {
                transform: translateX(-100%);
            }
            
            .mobile-optimized {
                padding: 0.75rem;
            }
        }

        /* Enhanced Animations */
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .slide-in-right {
            animation: slideInRight 0.3s ease-out;
        }

        @keyframes slideInRight {
            from { transform: translateX(100%); }
            to { transform: translateX(0); }
        }

        /* Enhanced Focus States */
        .focus-ring:focus {
            outline: 2px solid #3b82f6;
            outline-offset: 2px;
        }

        [x-cloak] { display: none; }
    </style>
</head>

<body class="font-sans antialiased bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen">
    <div class="flex relative">
        <!-- Enhanced Mobile Overlay -->
        <div id="sidebar-overlay" 
             class="fixed inset-0 bg-black/50 backdrop-blur-sm z-40 opacity-0 invisible transition-all duration-300 md:hidden">
        </div>

        <!-- Enhanced Sidebar -->
        <aside id="sidebar"
               class="bg-gradient-to-b from-blue-700 via-blue-800 to-blue-700 text-blue-50 shadow-2xl fixed top-0 left-0 h-screen w-[280px] z-50 transition-all duration-300 ease-in-out transform -translate-x-full md:translate-x-0 border-r border-blue-600/50"
               data-state="visible" aria-label="Main navigation">
            
            <!-- Sidebar Header -->
            <div class="px-6 py-6 border-b border-blue-600/30 bg-blue-800/50">
                <div class="flex justify-between items-center">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-400 to-blue-600 rounded-lg flex items-center justify-center shadow-lg">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                            </svg>
                        </div>
                        <div class="sidebar-text">
                            <h1 class="font-bold text-sm leading-tight">CLINIC CARE</h1>
                            <p class="text-blue-300 text-xs">MANAGEMENT</p>
                        </div>
                    </div>
                    <button id="closeSidebar" 
                            class="md:hidden text-blue-200 hover:text-white hover:bg-blue-600/50 p-2 rounded-lg transition-all duration-200 focus-ring" 
                            aria-label="Close sidebar">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>
            </div>

            <!-- Sidebar Content -->
            <div class="flex-1 px-4 py-4 space-y-1">
                <!-- Main Menu Section -->
                <div>
                    <div class="flex items-center space-x-2 px-4 py-2">
                        <h3 class="text-xs font-semibold text-blue-300 uppercase tracking-wider sidebar-text">Main Menu</h3>
                    </div>

                    <x-side-nav-link href="{{ route('admin.dashboard') }}" 
                                     :active="request()->routeIs('admin.dashboard')" 
                                     class="nav-item flex items-center px-4 py-3 rounded-xl mb-1 transition-all duration-200 group" 
                                     >
                        <div class="flex items-center space-x-3 w-full">
                            <div class="mini-icon w-6 flex justify-center transition-transform duration-200">
                                <i class="fas fa-home text-blue-200 group-hover:text-white"></i>
                            </div>
                            <span class="sidebar-text font-medium text-blue-100 group-hover:text-white transition-colors duration-200">Dashboard</span>
                        </div>
                    </x-side-nav-link>

                    <!-- Inventory Section -->
                    <x-side-nav-link href="{{ route('admin.inventory.index') }}" 
                                     :active="request()->routeIs('admin.inventory.*')" 
                                     class="nav-item flex items-center px-4 py-3 rounded-xl mb-1 transition-all duration-200 group" 
                                     >
                        <div class="flex items-center space-x-3 w-full">
                            <div class="mini-icon w-6 flex justify-center transition-transform duration-200">
                                <i class="fas fa-boxes text-blue-200 group-hover:text-white"></i>
                            </div>
                            <span class="sidebar-text font-medium text-blue-100 group-hover:text-white transition-colors duration-200">Inventory</span>
                        </div>
                    </x-side-nav-link>

                    <!-- Consultation Section -->
                    <x-side-nav-link href="{{ route('admin.consultation.index') }}" 
                                     :active="request()->routeIs('admin.consultation.*')" 
                                     class="nav-item flex items-center px-4 py-3 rounded-xl mb-1 transition-all duration-200 group" 
                                     >
                        <div class="flex items-center space-x-3 w-full">
                            <div class="mini-icon w-6 flex justify-center transition-transform duration-200">
                                <i class="fas fa-user-md text-blue-200 group-hover:text-white"></i>
                            </div>
                            <span class="sidebar-text font-medium text-blue-100 group-hover:text-white transition-colors duration-200">Consultation</span>
                        </div>
                    </x-side-nav-link>

                    <!-- Lab Tests Section -->
                    <x-side-nav-link href="{{ route('admin.labtests.index') }}" 
                                     :active="request()->routeIs('admin.labtests.*')" 
                                     class="nav-item flex items-center px-4 py-3 rounded-xl mb-1 transition-all duration-200 group" 
                                     >
                        <div class="flex items-center space-x-3 w-full">
                            <div class="mini-icon w-6 flex justify-center transition-transform duration-200">
                                <i class="fas fa-flask text-blue-200 group-hover:text-white"></i>
                            </div>
                            <span class="sidebar-text font-medium text-blue-100 group-hover:text-white transition-colors duration-200">Lab Tests</span>
                        </div>
                    </x-side-nav-link>
                </div>

                
            </div>
        </aside>

        <!-- Enhanced Main Content -->
        <div id="main-content" class="flex-1 min-h-screen md:ml-[280px] transition-all duration-300">
            <!-- Enhanced Top Navigation -->
            <nav class="bg-white/95 backdrop-blur-lg shadow-lg sticky top-0 z-30 border-b border-gray-200/50">
                <div class="mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="relative flex items-center justify-between h-16">
                        <!-- Left Side Controls -->
                        <div class="flex items-center space-x-4">
                            <button id="toggleSidebar"
                                    class="md:hidden p-2 rounded-xl text-gray-600 hover:bg-gray-100 transition-all duration-200"
                                    aria-label="Open sidebar">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24"
                                     stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                                </svg>
                            </button>
                            
                            <button id="toggleSidebarDesktop"
                                    class="hidden md:flex p-2 rounded-xl text-gray-600 hover:bg-gray-100 transition-all duration-200"
                                    aria-label="Toggle sidebar">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24"
                                     stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                                </svg>
                            </button>

                            <!-- Breadcrumb or Page Title -->
                            <div class="hidden md:block">
                                <h1 class="text-lg font-semibold text-gray-800">
                                    @yield('page-title', 'Dashboard')
                                </h1>
                            </div>
                        </div>

                        <!-- Right Side Controls -->
                        <div class="flex items-center space-x-4">
                            <!-- User Dropdown -->
                            <div class="relative">
                                <button id="userDropdownButton"
                                        class="flex items-center space-x-3 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-xl transition-all duration-200"
                                        aria-haspopup="true" aria-expanded="false">
                                    <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center shadow-md">
                                        <span class="text-white text-sm font-semibold">
                                            {{ substr(Auth::user()->name, 0, 1) }}
                                        </span>
                                    </div>
                                    <div class="hidden sm:block text-left">
                                        <div class="truncate max-w-32">{{ Auth::user()->name }}</div>
                                        <div class="text-xs text-gray-500 capitalize">{{ str_replace('_', ' ', Auth::user()->role ?? 'admin') }}</div>
                                    </div>
                                    <svg id="dropdownArrow"
                                         class="ml-2 h-4 w-4 transition-transform duration-200 text-gray-400"
                                         xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd"
                                              d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                              clip-rule="evenodd" />
                                    </svg>
                                </button>
                                
                                <div id="userDropdownMenu"
                                     class="glass-effect absolute right-0 top-full mt-2 w-56 rounded-xl shadow-xl py-2 z-50 hidden slide-in-right"
                                     role="menu">
                                    <div class="px-4 py-3 border-b border-gray-200/50">
                                        <p class="text-sm font-medium text-gray-900">{{ Auth::user()->name }}</p>
                                        <p class="text-xs text-gray-500">{{ Auth::user()->email }}</p>
                                    </div>
                                    <a href="{{ route('profile.edit') }}"
                                       class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors duration-200" 
                                       role="menuitem">
                                        <i class="fas fa-user-circle mr-3 text-gray-400"></i>
                                        {{ __('Profile') }}
                                    </a>
                                    <div class="border-t border-gray-200/50 my-1"></div>
                                    <form method="POST" action="{{ route('logout') }}" id="logoutForm">
                                        @csrf
                                        <a href="#" id="logoutButton"
                                           class="flex items-center px-4 py-3 text-sm text-red-600 hover:bg-red-50 transition-colors duration-200" 
                                           role="menuitem">
                                            <i class="fas fa-sign-out-alt mr-3"></i>
                                            {{ __('Log Out') }}
                                        </a>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Enhanced Content Area -->
            <main class="flex-1 bg-gray-50 min-h-screen">
                <div class="py-6">
                    <div class="px-4 sm:px-6 lg:px-8">
                        @yield('content')
                    </div>
                </div>
            </main>
        </div>
    </div>


    <!-- Quick Action Floating Button: ALWAYS VISIBLE -->
    <button
        id="quickAction"
        class="floating-button w-14 h-14 rounded-full bg-indigo-600 text-white flex items-center justify-center focus:ring focus:ring-indigo-300"
        title="Quick Actions"
        aria-label="Quick Actions"
    >
        <i class="fas fa-plus text-xl"></i>
    </button>
    <!-- Enhanced JavaScript -->
    <script>

        //Quickaction for Chatbot
        document.addEventListener('DOMContentLoaded', function () {
            const quickBtn = document.getElementById('quickAction');
            if (!quickBtn) return;

            quickBtn.addEventListener('click', function () {
                // Use SweetAlert2 for the modal content
                Swal.fire({
                    title: 'Quick Actions',
                    html: `
                        <div class="grid grid-cols-2 gap-4 mt-4 text-left" style="font-family: inherit;">
                            <button id="ai-chatbot" 
                                class="quick-action-btn p-4 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors duration-200 w-full text-left" 
                                type="button">
                                <div class="flex items-start gap-3">
                                    <div class="text-blue-600 text-2xl"><i class="fas fa-robot"></i></div>
                                    <div>
                                        <div class="text-sm font-medium text-blue-800">AI Triage Chatbot</div>
                                        <div class="text-xs text-gray-500 mt-1">Access Chatbot</div>
                                    </div>
                                </div>
                            </button>
                            <button id="qa-add-user" class="quick-action-btn p-4 bg-green-50 hover:bg-green-100 rounded-lg transition-colors duration-200 w-full text-left" type="button">
                                <div class="flex items-start gap-3">
                                    <div class="text-green-600 text-2xl"><i class="fas fa-user-plus"></i></div>
                                    <div>
                                        <div class="text-sm font-medium text-green-800">Add User</div>
                                        <div class="text-xs text-gray-500 mt-1">Register a student or staff</div>
                                    </div>
                                </div>
                            </button>
                            <button id="qa-analytics" class="quick-action-btn p-4 bg-purple-50 hover:bg-purple-100 rounded-lg transition-colors duration-200 w-full text-left" type="button">
                                <div class="flex items-start gap-3">
                                    <div class="text-purple-600 text-2xl"><i class="fas fa-chart-bar"></i></div>
                                    <div>
                                        <div class="text-sm font-medium text-purple-800">Analytics</div>
                                        <div class="text-xs text-gray-500 mt-1">View triage & clinic stats</div>
                                    </div>
                                </div>
                            </button>
                            <button id="qa-settings" class="quick-action-btn p-4 bg-orange-50 hover:bg-orange-100 rounded-lg transition-colors duration-200 w-full text-left" type="button">
                                <div class="flex items-start gap-3">
                                    <div class="text-orange-600 text-2xl"><i class="fas fa-cogs"></i></div>
                                    <div>
                                        <div class="text-sm font-medium text-orange-800">Settings</div>
                                        <div class="text-xs text-gray-500 mt-1">Manage system preferences</div>
                                    </div>
                                </div>
                            </button>
                        </div>
                    `,
                    showConfirmButton: false,
                    showCancelButton: true,
                    cancelButtonText: 'Close',
                    customClass: { popup: 'rounded-xl shadow-2xl' },
                    didOpen: () => {
                        // Bind handlers to buttons inside the modal
                        const byId = id => document.getElementById(id);

                        const newReportBtn = byId('ai-chatbot');
                        const addUserBtn = byId('qa-add-user');
                        const analyticsBtn = byId('qa-analytics');
                        const settingsBtn = byId('qa-settings');

                        if (newReportBtn) {
                            newReportBtn.addEventListener('click', () => {
                                // Replace with your real route or use a route helper server-side if preferred
                                window.location.href = "{{ url('/chatbot') }}";
                            });
                        }
                        if (addUserBtn) {
                            addUserBtn.addEventListener('click', () => {
                                window.location.href = "{{ url('/users/create') }}";
                            });
                        }
                        if (analyticsBtn) {
                            analyticsBtn.addEventListener('click', () => {
                                window.location.href = "{{ url('/analytics') }}";
                            });
                        }
                        if (settingsBtn) {
                            settingsBtn.addEventListener('click', () => {
                                window.location.href = "{{ url('/settings') }}";
                            });
                        }
                    }
                });
            });
        });



        $(document).ready(function () {
            // Enhanced Sidebar State Management
            const sidebarState = localStorage.getItem('sidebarState') || 'visible';
            const $sidebar = $('#sidebar');
            const $mainContent = $('#main-content');
            const $overlay = $('#sidebar-overlay');

            // Apply saved state on desktop
            if (window.innerWidth >= 768) {
                if (sidebarState === 'hidden') {
                    $sidebar.addClass('sidebar-hidden').attr('data-state', 'hidden');
                    $mainContent.removeClass('md:ml-[280px]').addClass('md:ml-0');
                }
            }

            // Enhanced Mobile Sidebar Toggle
            $("#toggleSidebar").click(function () {
                $sidebar.removeClass("-translate-x-full");
                $overlay.removeClass("opacity-0 invisible").addClass("opacity-50 visible");
                $("body").addClass("overflow-hidden");
            });

            // Enhanced Desktop Sidebar Toggle
            $("#toggleSidebarDesktop").click(function () {
                const isHidden = $sidebar.hasClass('sidebar-hidden');
                
                if (isHidden) {
                    $sidebar.removeClass('sidebar-hidden').attr('data-state', 'visible');
                    $mainContent.removeClass('md:ml-0').addClass('md:ml-[280px]');
                    localStorage.setItem('sidebarState', 'visible');
                } else {
                    $sidebar.addClass('sidebar-hidden').attr('data-state', 'hidden');
                    $mainContent.removeClass('md:ml-[280px]').addClass('md:ml-0');
                    localStorage.setItem('sidebarState', 'hidden');
                }
            });

            // Enhanced Sidebar Close
            $("#closeSidebar, #sidebar-overlay").click(function () {
                $sidebar.addClass("-translate-x-full");
                $overlay.removeClass("opacity-50 visible").addClass("opacity-0 invisible");
                $("body").removeClass("overflow-hidden");
            });

            // Enhanced Window Resize Handler
            $(window).resize(function () {
                const width = $(window).width();
                if (width >= 768) {
                    // Desktop view
                    $sidebar.removeClass("-translate-x-full");
                    $overlay.removeClass("opacity-50 visible").addClass("opacity-0 invisible");
                    $("body").removeClass("overflow-hidden");
                    
                    const sidebarState = localStorage.getItem('sidebarState') || 'visible';
                    if (sidebarState === 'hidden') {
                        $sidebar.addClass('sidebar-hidden');
                        $mainContent.removeClass('md:ml-[280px]').addClass('md:ml-0');
                    } else {
                        $sidebar.removeClass('sidebar-hidden');
                        $mainContent.removeClass('md:ml-0').addClass('md:ml-[280px]');
                    }
                } else {
                    // Mobile view
                    $sidebar.removeClass('sidebar-hidden').addClass("-translate-x-full");
                    $mainContent.removeClass('md:ml-0 md:ml-[280px]');
                }
            });

            // Enhanced User Dropdown
            $("#userDropdownButton").click(function (event) {
                event.stopPropagation();
                const $menu = $("#userDropdownMenu");
                const $arrow = $("#dropdownArrow");
                const isVisible = !$menu.hasClass("hidden");
                
                if (isVisible) {
                    $menu.addClass("hidden");
                    $arrow.removeClass("rotate-180");
                    $(this).attr('aria-expanded', 'false');
                } else {
                    $menu.removeClass("hidden");
                    $arrow.addClass("rotate-180");
                    $(this).attr('aria-expanded', 'true');
                }
            });

            // Enhanced Click Outside Handler
            $(document).click(function (event) {
                if (!$(event.target).closest("#userDropdownButton, #userDropdownMenu").length) {
                    $("#userDropdownMenu").addClass("hidden");
                    $("#dropdownArrow").removeClass("rotate-180");
                    $("#userDropdownButton").attr('aria-expanded', 'false');
                }
            });

            // Enhanced Logout Handler
            $("#logoutButton").click(function (event) {
                event.preventDefault();
                
                Swal.fire({
                    title: 'Confirm Logout',
                    html: `
                        <div class="text-center">
                            <i class="fas fa-sign-out-alt text-red-500 text-3xl mb-3"></i>
                            <p class="text-gray-600">Are you sure you want to sign out?</p>
                        </div>
                    `,
                    icon: null,
                    showCancelButton: true,
                    confirmButtonText: '<i class="fas fa-sign-out-alt mr-2"></i>Yes, Sign Out',
                    cancelButtonText: '<i class="fas fa-times mr-2"></i>Cancel',
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#6b7280',
                    customClass: {
                        popup: 'rounded-xl shadow-2xl',
                        confirmButton: 'rounded-lg font-medium',
                        cancelButton: 'rounded-lg font-medium'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        $("#logoutLoader").removeClass("hidden").addClass("opacity-100");
                        setTimeout(() => {
                            $("#logoutForm").submit();
                        }, 1500);
                    }
                });
            });

            // Enhanced Form Submission Handler
            document.getElementById("logoutForm").addEventListener("submit", function () {
                document.getElementById("logoutLoader").classList.remove("hidden");
                document.getElementById("logoutLoader").classList.add("opacity-100");
            });

            // Enhanced Navigation Highlighting
            $('.nav-item').hover(
                function() {
                    $(this).addClass('transform scale-[1.02]');
                },
                function() {
                    $(this).removeClass('transform scale-[1.02]');
                }
            );

            // Keyboard Navigation Support
            $(document).keydown(function(e) {
                // Alt + S to toggle sidebar
                if (e.altKey && e.keyCode === 83) {
                    e.preventDefault();
                    if ($(window).width() >= 768) {
                        $("#toggleSidebarDesktop").click();
                    } else {
                        $("#toggleSidebar").click();
                    }
                }
                
                // Esc to close dropdowns/modals
                if (e.keyCode === 27) {
                    $("#userDropdownMenu").addClass("hidden");
                    $("#reportsDropdown").slideUp(200);
                    $("#sidebar-overlay").click();
                }
            });

            // Enhanced Loading States
            $('form').submit(function() {
                const $submitBtn = $(this).find('button[type="submit"]');
                $submitBtn.prop('disabled', true).addClass('loading-pulse');
            });

            // Smooth scroll for internal links
            $('a[href^="#"]').click(function(e) {
                e.preventDefault();
                const target = $($(this).attr('href'));
                if (target.length) {
                    $('html, body').animate({
                        scrollTop: target.offset().top - 100
                    }, 500);
                }
            });

            console.log('🏥 Enhanced Clinic Management System loaded successfully!');
        });
    </script>
</body>
</html>