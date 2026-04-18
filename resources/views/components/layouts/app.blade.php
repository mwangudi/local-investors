<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="x-ua-compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>{{ $title ?? config('app.name', 'Local Investors') }} | Dashboard</title>
    
    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('assets/images/favicon.ico') }}" />
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/bootstrap.min.css') }}" />
    
    <!-- Vendors CSS -->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/vendors/css/vendors.min.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/vendors/css/daterangepicker.min.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/vendors/css/select2.min.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/vendors/css/select2-theme.min.css') }}" />
    
    <!-- Font Awesome -->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/vendors/css/fontawesome.min.css') }}" />

    <!-- Theme CSS -->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/theme.min.css') }}" />
    
    @livewireStyles
    @stack('styles')
    
    <style>
        /* Custom Application Colors */
        :root {
            --bs-primary: #ff8c00;
            --bs-success: rgba(var(--c-600), var(--tw-text-opacity, 1));
        }

        /* Sidebar Header Logo Area */
        .m-header, .sidebar-header {
            background-color: #FDF2E2 !important;
            padding: 10px !important;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .m-header .logo-lg {
            max-height: 75px !important;
            width: auto !important;
            position: relative;
            z-index: 2;
            filter: none !important;
            -webkit-backface-visibility: hidden;
            backface-visibility: hidden;
            transform: translateZ(0);
            image-rendering: -webkit-optimize-contrast;
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

        /* Tailwind utility classes needed for Livewire components */
        .hidden {
            display: none !important;
        }
        /* Hide any Livewire offline indicators */
        [wire\:offline],
        [wire\:offline\.class\.remove] {
            display: none !important;
        }
        /* Sticky footer - push footer to bottom when content is short */
        .nxl-container {
            min-height: calc(100vh - 70px); /* Subtract header height */
            display: flex;
            flex-direction: column;
        }
        .nxl-container .nxl-content {
            flex: 1;
        }
        
        /* Custom Select2 styles */
        .select2-container--default .select2-selection--single {
            height: calc(1.5em + 0.75rem + 2px) !important; /* Match Bootstrap form-control */
            display: flex;
            align-items: center;
            border: 1px solid #ced4da;
            border-radius: 0.375rem;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #444;
            line-height: 1 !important;
            padding-left: 12px;
            width: 100%;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 100% !important;
            position: absolute;
            top: 0;
            right: 1px;
            width: 28px;
        }
        .select2-container--default .select2-selection--single .select2-selection__clear {
            font-size: 1rem;
            line-height: 1;
        }
    </style>
</head>

<body>
    <!-- Navigation Menu -->
    <x-layouts.navigation />

    <!-- Header -->
    <x-layouts.header />

    <!-- Main Content -->
    <main class="nxl-container">
        <div class="nxl-content">
            <!-- Page Header -->
            @hasSection('pageHeader')
            <div class="page-header">
                @yield('pageHeader')
            </div>
            @endif
            
            <!-- Main Content -->
            <div class="main-content">
                {{ $slot }}
            </div>
        </div>
        
        <!-- Footer -->
        <footer class="footer">
            <p class="fs-11 text-muted fw-medium text-uppercase mb-0 copyright">
                <span>Copyright ©</span>
                <script>document.write(new Date().getFullYear());</script>
            </p>
            <div class="d-flex align-items-center gap-4">
                <a href="javascript:void(0);" class="fs-11 fw-semibold text-uppercase">Help</a>
                <a href="javascript:void(0);" class="fs-11 fw-semibold text-uppercase">Terms</a>
                <a href="javascript:void(0);" class="fs-11 fw-semibold text-uppercase">Privacy</a>
            </div>
        </footer>
    </main>

    <!-- Theme Customizer -->
    <x-layouts.theme-customizer />

    <!-- Vendors JS -->
    <script src="{{ asset('assets/vendors/js/vendors.min.js') }}"></script>
    <script src="{{ asset('assets/vendors/js/daterangepicker.min.js') }}"></script>
    <script src="{{ asset('assets/vendors/js/apexcharts.min.js') }}"></script>
    <script src="{{ asset('assets/vendors/js/circle-progress.min.js') }}"></script>
    <script src="{{ asset('assets/vendors/js/select2.min.js') }}"></script>
    
    <!-- Select2 Initialization -->
    <script>
        function initSelect2() {
            // Initialize all select elements with class 'select2'
            if (typeof $.fn.select2 !== 'undefined') {
                $('.select2').each(function() {
                    var $el = $(this);
                    
                    // Clean up any existing Select2 instance before initializing
                    if ($el.hasClass('select2-hidden-accessible')) {
                        try {
                            $el.select2('destroy');
                        } catch (e) {
                            // If destroy fails, manually clean up
                            $el.removeClass('select2-hidden-accessible');
                            $el.removeAttr('data-select2-id');
                        }
                        // Remove any orphaned Select2 containers
                        $el.next('.select2-container').remove();
                    }
                    
                    // Initialize Select2
                    $el.select2({
                        theme: 'default',
                        placeholder: $el.data('placeholder') || 'Select an option',
                        allowClear: $el.data('allow-clear') !== false,
                        width: '100%',
                        dropdownParent: $el.closest('.modal').length ? $el.closest('.modal') : $('body')
                    });
                    
                    // Sync Select2 change to Livewire
                    $el.on('select2:select select2:clear', function(e) {
                        var value = $(this).val();
                        // Trigger native change event for onchange handler
                        this.dispatchEvent(new Event('change', { bubbles: true }));
                    });
                });
            }
        }
        
        // Initialize on page load (after DOM is ready)
        $(document).ready(function() {
            initSelect2();
        });
        
        // Reinitialize after Livewire page navigation
        document.addEventListener('livewire:navigated', function() {
            setTimeout(initSelect2, 100);
        });
    </script>
    
    <!-- Apps Init -->
    <script src="{{ asset('assets/js/common-init.min.js') }}"></script>
    
    <!-- Theme Customizer -->
    <script src="{{ asset('assets/js/theme-customizer-init.min.js') }}"></script>
    
    @livewireScripts
    @stack('scripts')
    
    <!-- Toast Notifications Container -->
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;">
        <!-- Success Toast -->
        <div id="successToast" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="feather-check-circle me-2"></i>
                    <span id="successToastMessage">Operation completed successfully!</span>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
        
        <!-- Error Toast -->
        <div id="errorToast" class="toast align-items-center text-bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="feather-alert-circle me-2"></i>
                    <span id="errorToastMessage">An error occurred!</span>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
        
        <!-- Info Toast -->
        <div id="infoToast" class="toast align-items-center text-bg-info border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="feather-info me-2"></i>
                    <span id="infoToastMessage">Information</span>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>
    
    <script>
        // Clear validation errors immediately when user types in a red field (event delegation)
        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('is-invalid')) {
                e.target.classList.remove('is-invalid');
                var feedback = e.target.nextElementSibling;
                if (feedback && feedback.classList.contains('invalid-feedback')) {
                    feedback.style.display = 'none';
                }
            }
        });
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('is-invalid')) {
                e.target.classList.remove('is-invalid');
                var feedback = e.target.nextElementSibling;
                if (feedback && feedback.classList.contains('invalid-feedback')) {
                    feedback.style.display = 'none';
                }
            }
        });
    </script>

    <script>
        // Toast notification helper
        function showToast(type, message) {
            const toastId = type + 'Toast';
            const messageId = type + 'ToastMessage';
            const toastEl = document.getElementById(toastId);
            const messageEl = document.getElementById(messageId);
            
            if (toastEl && messageEl) {
                messageEl.textContent = message;
                const toast = new bootstrap.Toast(toastEl, { delay: 4000 });
                toast.show();
            }
        }
        
        // Listen for Livewire toast events
        document.addEventListener('livewire:init', () => {
            Livewire.on('toast', (data) => {
                const eventData = Array.isArray(data) ? data[0] : data;
                showToast(eventData.type || 'success', eventData.message || 'Operation completed!');
            });
            
            Livewire.on('toast-success', (data) => {
                const message = Array.isArray(data) ? data[0] : (data.message || data);
                showToast('success', typeof message === 'string' ? message : message.message || 'Operation completed successfully!');
            });
            
            Livewire.on('toast-error', (data) => {
                const message = Array.isArray(data) ? data[0] : (data.message || data);
                showToast('error', typeof message === 'string' ? message : message.message || 'An error occurred!');
            });
            
            Livewire.on('toast-info', (data) => {
                const message = Array.isArray(data) ? data[0] : (data.message || data);
                showToast('info', typeof message === 'string' ? message : message.message || 'Information');
            });
        });
        
        // Show PHP session flash messages as toast on page load
        document.addEventListener('DOMContentLoaded', function() {
            @if(session('success'))
                showToast('success', "{{ session('success') }}");
            @endif
            @if(session('error'))
                showToast('error', "{{ session('error') }}");
            @endif
            @if(session('info'))
                showToast('info', "{{ session('info') }}");
            @endif
        });
    </script>
</body>

</html>
