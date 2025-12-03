<?php require_once __DIR__ . '/../config/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?php echo $pageTitle ?? APP_NAME; ?> - City Government of Valenzuela</title>
    <meta name="description" content="Legislative Records Management System - City Government of Valenzuela, Metropolitan Manila">
    <meta name="keywords" content="LRMS, Valenzuela, Legislative Records, Document Management">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?php echo BASE_URL; ?>/public/assets/images/logo.png">
    <link rel="apple-touch-icon" href="<?php echo BASE_URL; ?>/public/assets/images/logo.png">
    
    <!-- Prevent dark mode flicker - must run before page renders -->
    <script>
        // Check for dark mode preference immediately
        if (localStorage.getItem('theme') === 'dark') {
            document.documentElement.classList.add('dark');
        }
    </script>
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo asset('css/custom.css'); ?>">
    
    <!-- Application Configuration -->
    <script src="<?php echo asset('js/config.js'); ?>"></script>
    
    <!-- Notification System -->
    <script src="<?php echo asset('js/notifications.js'); ?>" defer></script>
    
    <style type="text/tailwindcss">
        @layer components {
            .btn-primary {
                @apply bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg transition-all duration-200 ease-in-out shadow-md hover:shadow-lg transform hover:-translate-y-0.5 active:translate-y-0;
            }
            
            .btn-secondary {
                @apply bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded-lg transition-all duration-200 ease-in-out shadow-md hover:shadow-lg transform hover:-translate-y-0.5 active:translate-y-0;
            }
            
            .btn-success {
                @apply bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg transition-all duration-200 ease-in-out shadow-md hover:shadow-lg transform hover:-translate-y-0.5 active:translate-y-0;
            }
            
            .btn-danger {
                @apply bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg transition-all duration-200 ease-in-out shadow-md hover:shadow-lg transform hover:-translate-y-0.5 active:translate-y-0;
            }
            
            .btn-warning {
                @apply bg-yellow-600 hover:bg-yellow-700 text-white font-semibold py-2 px-4 rounded-lg transition-all duration-200 ease-in-out shadow-md hover:shadow-lg transform hover:-translate-y-0.5 active:translate-y-0;
            }
            
            .btn-outline {
                @apply border-2 border-red-600 text-red-600 hover:bg-red-600 hover:text-white font-semibold py-2 px-4 rounded-lg transition-all duration-200 ease-in-out shadow-sm hover:shadow-md transform hover:-translate-y-0.5 active:translate-y-0;
            }
            
            .input-field {
                @apply w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all duration-200;
            }
            
            .card {
                @apply bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition duration-200;
            }
            
            .badge {
                @apply inline-flex items-center px-3 py-1 rounded-full text-sm font-medium;
            }
            
            .badge-primary {
                @apply bg-red-100 text-red-800;
            }
            
            .badge-success {
                @apply bg-green-100 text-green-800;
            }
            
            .badge-warning {
                @apply bg-yellow-100 text-yellow-800;
            }
            
            .badge-danger {
                @apply bg-red-100 text-red-800;
            }
            
            .badge-info {
                @apply bg-indigo-100 text-indigo-800;
            }
            
            .table-container {
                @apply overflow-x-auto shadow-md rounded-lg;
            }
            
            .table {
                @apply min-w-full divide-y divide-gray-200;
            }
            
            .table-header {
                @apply bg-gray-50;
            }
            
            .table-th {
                @apply px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider;
            }
            
            .table-td {
                @apply px-6 py-4 whitespace-nowrap text-sm text-gray-900;
            }
            
            /* Loading Skeleton Animations */
            .skeleton {
                @apply bg-gradient-to-r from-gray-200 via-gray-300 to-gray-200 animate-pulse rounded;
            }
            
            .skeleton-text {
                @apply h-4 bg-gray-200 rounded animate-pulse;
            }
            
            .skeleton-circle {
                @apply rounded-full bg-gray-200 animate-pulse;
            }
            
            .skeleton-card {
                @apply bg-white rounded-xl shadow-md p-6;
            }
            
            @keyframes shimmer {
                0% {
                    background-position: -1000px 0;
                }
                100% {
                    background-position: 1000px 0;
                }
            }
            
            .animate-shimmer {
                animation: shimmer 2s infinite linear;
                background: linear-gradient(to right, #f3f4f6 0%, #e5e7eb 20%, #f3f4f6 40%, #f3f4f6 100%);
                background-size: 1000px 100%;
            }
            
            /* Smooth fade in */
            .fade-in {
                animation: fadeIn 0.5s ease-in;
            }
            
            @keyframes fadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
            }
            
            /* Loading spinner */
            .spinner {
                @apply inline-block w-6 h-6 border-4 border-gray-200 border-t-red-600 rounded-full animate-spin;
            }
            
            /* Custom Tailwind Animations */
            @keyframes fade-in {
                from {
                    opacity: 0;
                }
                to {
                    opacity: 1;
                }
            }
            
            @keyframes fade-in-up {
                from {
                    opacity: 0;
                    transform: translateY(20px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            
            @keyframes slide-in-left {
                from {
                    opacity: 0;
                    transform: translateX(-30px);
                }
                to {
                    opacity: 1;
                    transform: translateX(0);
                }
            }
            
            @keyframes slide-in-right {
                from {
                    opacity: 0;
                    transform: translateX(30px);
                }
                to {
                    opacity: 1;
                    transform: translateX(0);
                }
            }
            
            @keyframes bounce-in {
                0% {
                    opacity: 0;
                    transform: scale(0.3);
                }
                50% {
                    opacity: 1;
                    transform: scale(1.05);
                }
                70% {
                    transform: scale(0.9);
                }
                100% {
                    transform: scale(1);
                }
            }
            
            /* Set initial hidden state BEFORE animation starts */
            .animate-fade-in,
            .animate-fade-in-up,
            .animate-slide-in-left,
            .animate-slide-in-right,
            .animate-bounce-in {
                opacity: 0;
            }
            
            .animate-fade-in {
                animation: fade-in 0.6s ease-out forwards;
            }
            
            .animate-fade-in-up {
                transform: translateY(20px);
                animation: fade-in-up 0.6s ease-out forwards;
            }
            
            .animate-slide-in-left {
                transform: translateX(-30px);
                animation: slide-in-left 0.6s ease-out forwards;
            }
            
            .animate-slide-in-right {
                transform: translateX(30px);
                animation: slide-in-right 0.6s ease-out forwards;
            }
            
            .animate-bounce-in {
                transform: scale(0.3);
                animation: bounce-in 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55) forwards;
            }
            
            /* Animation delays - elements stay hidden until their animation starts */
            .animation-delay-100 { animation-delay: 100ms; }
            .animation-delay-200 { animation-delay: 200ms; }
            .animation-delay-300 { animation-delay: 300ms; }
            .animation-delay-400 { animation-delay: 400ms; }
            .animation-delay-500 { animation-delay: 500ms; }
            .animation-delay-600 { animation-delay: 600ms; }
            .animation-delay-700 { animation-delay: 700ms; }
            .animation-delay-800 { animation-delay: 800ms; }
            .animation-delay-900 { animation-delay: 900ms; }
            
            /* Smooth transitions for all interactive elements */
            * {
                @apply transition-colors duration-200;
            }
            
            button, a, input, select, textarea {
                @apply transition-all duration-200;
            }
        }
    </style>
</head>
<body class="bg-gray-100 font-sans antialiased">
    <!-- Page Loading Overlay -->
    <div id="page-loader" class="fixed inset-0 bg-white z-50 flex items-center justify-center hidden">
        <div class="text-center">
            <div class="w-16 h-16 border-4 border-red-200 border-t-red-600 rounded-full animate-spin mx-auto mb-4"></div>
            <p class="text-gray-600 font-semibold">Loading...</p>
        </div>
    </div>
    
    <div class="flex h-screen overflow-hidden">
