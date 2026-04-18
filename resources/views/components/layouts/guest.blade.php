<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Duralux') }} || Login</title>
    
    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('assets/images/favicon.ico') }}">
    
    <!-- Core Stylesheets -->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/vendors/css/vendors.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/theme.min.css') }}">
    
    @livewireStyles

    <style>
        /* Custom Application Colors */
        :root {
            --bs-primary: #ff8c00;
            --bs-success: rgba(var(--c-600), var(--tw-text-opacity, 1));
        }
        /* Button Colors Override */
        .btn-primary {
            background-color: var(--bs-primary) !important;
            border-color: var(--bs-primary) !important;
            color: #fff !important;
        }
        
        .btn-primary:hover, .btn-primary:focus, .btn-primary:active {
            background-color: #e67e22 !important; /* Slightly darker amber on hover */
            border-color: #e67e22 !important;
            color: #fff !important;
        }

        .btn-outline-primary {
            color: var(--bs-primary) !important;
            border-color: var(--bs-primary) !important;
        }

        .btn-outline-primary:hover, .btn-outline-primary:focus, .btn-outline-primary:active {
            background-color: var(--bs-primary) !important;
            color: #fff !important;
        }

        .text-primary {
            color: var(--bs-primary) !important;
        }
        
        .bg-primary {
            background-color: var(--bs-primary) !important;
        }
        
        /* Logo Size Fix for Login Page */
        .logo-lg {
            max-height: 45px !important;
            width: auto !important;
        }
    </style>
</head>
<body>
    {{ $slot }}
    
    <!-- Core Scripts -->
    <script src="{{ asset('assets/vendors/js/vendors.min.js') }}"></script>
    <script src="{{ asset('assets/js/common-init.min.js') }}"></script>
    
    @livewireScripts
</body>
</html>
