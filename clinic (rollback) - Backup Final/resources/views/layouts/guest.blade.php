<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net" />
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Font Awesome (icons) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" integrity="sha512-..." crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Vite assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- SweetAlert2 CDN (ensure available even if you didn't bundle it) -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        /* Floating button style (always visible) */
        .floating-button {
            position: fixed;
            right: 20px;
            bottom: 24px;
            z-index: 60;
            box-shadow: 0 6px 18px rgba(20, 20, 40, 0.12);
            transition: transform .12s ease, opacity .12s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .floating-button:active { transform: scale(.98); }
        .focus-ring { outline: none; }
    </style>
</head>

<body class="font-sans text-gray-900 antialiased">
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
        <div>
            <a href="/">
                <x-application-logo class="w-40 h-40 fill-current text-gray-500" />
            </a>
        </div>

        <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
            {{ $slot }}
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

    <script>
        // Ensure DOM is loaded before attaching handlers

        //Quickaction chatbot
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
    </script>
</body>
</html>
