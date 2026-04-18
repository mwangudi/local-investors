<header class="nxl-header">
    <div class="header-wrapper">
        <!-- Header Left -->
        <div class="header-left d-flex align-items-center gap-4">
            <!-- Mobile Toggler -->
            <a href="javascript:void(0);" class="nxl-head-mobile-toggler" id="mobile-collapse">
                <div class="hamburger hamburger--arrowturn">
                    <div class="hamburger-box">
                        <div class="hamburger-inner"></div>
                    </div>
                </div>
            </a>
            
            <!-- Navigation Toggle -->
            <div class="nxl-navigation-toggle">
                <a href="javascript:void(0);" id="menu-mini-button">
                    <i class="feather-align-left"></i>
                </a>
                <a href="javascript:void(0);" id="menu-expend-button" style="display: none">
                    <i class="feather-arrow-right"></i>
                </a>
            </div>
            
            <!-- Mega Menu Toggle (Mobile) -->
            <div class="nxl-lavel-mega-menu-toggle d-flex d-lg-none">
                <a href="javascript:void(0);" id="nxl-lavel-mega-menu-open">
                    <i class="feather-align-left"></i>
                </a>
            </div>
        </div>
        
        <!-- Header Right -->
        <div class="header-right ms-auto">
            <div class="d-flex align-items-center">
                <div class="nxl-h-item d-none d-sm-flex">
                    <div class="full-screen-switcher">
                        <a href="javascript:void(0);" class="nxl-head-link me-0" id="fullscreen-button">
                            <i class="feather-maximize d-none d-sm-inline-block"></i>
                        </a>
                    </div>
                </div>
                
                <!-- Notifications -->
                @auth
                    <livewire:notifications.bell />
                @endauth
                
                <!-- User Profile -->
                <div class="dropdown nxl-h-item">
                    <a class="nxl-head-link me-0" data-bs-toggle="dropdown" href="#" role="button" data-bs-auto-close="outside">
                        <div class="avtar-md rounded-circle bg-soft-primary text-primary d-flex align-items-center justify-content-center">
                            <i class="feather-user"></i>
                        </div>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end nxl-h-dropdown nxl-user-dropdown">
                        <div class="dropdown-header">
                            <div class="d-flex align-items-center">
                                <div class="avtar-md bg-soft-primary text-primary rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="feather-user"></i>
                                </div>
                                <div class="ms-3">
                                    <h6 class="fw-semibold mb-0">{{ auth()->user()->name ?? 'Guest' }}</h6>
                                    <span class="fs-12 fw-normal text-muted">{{ auth()->user()->email ?? '' }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="dropdown-divider"></div>
                        <a href="#" class="dropdown-item">
                            <i class="feather-user"></i>
                            <span>My Profile</span>
                        </a>
                        <a href="#" class="dropdown-item">
                            <i class="feather-settings"></i>
                            <span>Account Settings</span>
                        </a>
                        <div class="dropdown-divider"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item">
                                <i class="feather-log-out"></i>
                                <span>Logout</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
