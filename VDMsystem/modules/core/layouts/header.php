<?php require_once __DIR__ . '/../config/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes, viewport-fit=cover">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="theme-color" content="#1e40af">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title><?php echo $pageTitle ?? APP_NAME; ?> - City Government of Valenzuela</title>
    <meta name="description" content="Voting and Decision-Making System - City Government of Valenzuela">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?php echo BASE_URL; ?>/public/assets/images/logo.png">
    
    <!-- Dark mode detection -->
    <script>
        if (localStorage.getItem('theme') === 'dark') {
            document.documentElement.classList.add('dark');
        }
    </script>
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo asset('css/custom.css'); ?>">
    
    <!-- Application Configuration -->
    <script src="<?php echo asset('js/config.js'); ?>"></script>
    
    <style type="text/tailwindcss">
        @layer components {
            .btn-primary {
                @apply bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-all duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5 active:translate-y-0;
            }
            
            .btn-secondary {
                @apply bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded-lg transition-all duration-200 shadow-md hover:shadow-lg;
            }
            
            .btn-success {
                @apply bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg transition-all duration-200 shadow-md hover:shadow-lg;
            }
            
            .btn-danger {
                @apply bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg transition-all duration-200 shadow-md hover:shadow-lg;
            }
            
            .btn-warning {
                @apply bg-yellow-600 hover:bg-yellow-700 text-white font-semibold py-2 px-4 rounded-lg transition-all duration-200 shadow-md hover:shadow-lg;
            }
            
            .btn-outline {
                @apply border-2 border-blue-600 text-blue-600 hover:bg-blue-600 hover:text-white font-semibold py-2 px-4 rounded-lg transition-all duration-200;
            }
            
            .input-field {
                @apply w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200;
            }
            
            .card {
                @apply bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition duration-200;
            }
            
            .badge {
                @apply inline-flex items-center px-3 py-1 rounded-full text-sm font-medium;
            }
            
            .badge-primary { @apply bg-blue-100 text-blue-800; }
            .badge-success { @apply bg-green-100 text-green-800; }
            .badge-warning { @apply bg-yellow-100 text-yellow-800; }
            .badge-danger { @apply bg-red-100 text-red-800; }
            .badge-info { @apply bg-indigo-100 text-indigo-800; }
        }
    </style>
    
    <style>
        /* Animation Keyframes */
        @keyframes fade-in {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes fade-in-up {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes slide-in-left {
            from { opacity: 0; transform: translateX(-20px); }
            to { opacity: 1; transform: translateX(0); }
        }
        @keyframes slide-in-right {
            from { opacity: 0; transform: translateX(20px); }
            to { opacity: 1; transform: translateX(0); }
        }
        .animate-fade-in { animation: fade-in 0.5s ease-out forwards; }
        .animate-fade-in-up { animation: fade-in-up 0.5s ease-out forwards; }
        .animate-slide-in-left { animation: slide-in-left 0.5s ease-out forwards; }
        .animate-slide-in-right { animation: slide-in-right 0.5s ease-out forwards; }
        .animation-delay-100 { animation-delay: 100ms; }
        .animation-delay-200 { animation-delay: 200ms; }
        .animation-delay-300 { animation-delay: 300ms; }
        .animation-delay-400 { animation-delay: 400ms; }
        .animation-delay-500 { animation-delay: 500ms; }
    </style>
</head>
<body class="bg-gray-100 font-sans antialiased">
    <div class="flex h-screen overflow-hidden">
