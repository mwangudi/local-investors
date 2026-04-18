<main class="auth-cover-wrapper">
    <div class="auth-cover-content-inner">
        <div class="auth-cover-content-wrapper">
            <div class="auth-img">
                <img src="{{ asset('assets/images/auth/auth-cover-login-bg.svg') }}" alt="" class="img-fluid">
            </div>
        </div>
    </div>
    <div class="auth-cover-sidebar-inner">
        <div class="auth-cover-card-wrapper">
            <div class="auth-cover-card p-sm-5">
                <div class="wd-55 mb-5">
                    <img src="{{ asset('assets/images/logo.png') }}" alt="" class="img-fluid">
                </div>
                <h4 class="fs-20 fw-bold mb-2">Login to your account</h4>
                <p class="fs-12 fw-medium text-muted">Welcome back! Please enter your credentials to access your dashboard and manage your data.</p>
                
                <form wire:submit="login" novalidate class="w-100 mt-4 pt-2">
                    @if (session('status'))
                        <div class="alert alert-success mb-3">
                            {{ session('status') }}
                        </div>
                    @endif

                    <div class="mb-4">
                        <input type="email" 
                               class="form-control @error('email') is-invalid @enderror" 
                               placeholder="Email or Username"
                               wire:model="email"
                               autofocus
                               required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <input type="password" 
                               class="form-control @error('password') is-invalid @enderror" 
                               placeholder="Password"
                               wire:model="password"
                               required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="rememberMe" wire:model="remember">
                                <label class="custom-control-label c-pointer" for="rememberMe">Remember Me</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-5">
                        <button type="submit" class="btn btn-lg btn-primary w-100" wire:loading.attr="disabled">
                            <span wire:loading.remove>Login</span>
                            <span wire:loading>
                                <span class="spinner-border spinner-border-sm me-2"></span>Signing in...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>
