<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="x-ua-compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="description" content="Local Investors SACCO — savings, affordable loans and transparent member finance." />
    <title>{{ config('app.name', 'Local Investors') }} — SACCO Savings & Loans</title>

    <link rel="shortcut icon" type="image/png" href="{{ asset('assets/images/favicon.png') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/bootstrap.min.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/vendors/css/fontawesome.min.css') }}" />

    <style>
        :root {
            --li-primary: #ff8c00;
            --li-primary-dark: #e67e22;
            --li-primary-soft: #FDF2E2;
            --li-dark: #1f2937;
            --li-muted: #6b7280;
        }
        html { scroll-behavior: smooth; }
        body { font-family: 'Inter', system-ui, -apple-system, Segoe UI, Roboto, sans-serif; color: var(--li-dark); }
        .li-nav {
            position: sticky; top: 0; z-index: 100;
            background: #fff; border-bottom: 1px solid #eef0f3;
            padding: .75rem 0;
        }
        .li-brand { font-weight: 800; color: var(--li-dark); text-decoration: none; display:inline-flex; align-items:center; gap:.6rem; font-size:1.15rem; }
        .li-brand .dot { width: 34px; height: 34px; border-radius: 10px; background: var(--li-primary); color:#fff; display:inline-flex; align-items:center; justify-content:center; font-weight:700; }
        .li-nav a.nav-link { color: var(--li-dark); font-weight: 500; }
        .li-nav a.nav-link:hover { color: var(--li-primary); }
        .btn-li-primary { background: var(--li-primary); color: #fff; border: 1px solid var(--li-primary); font-weight:600; }
        .btn-li-primary:hover { background: var(--li-primary-dark); border-color: var(--li-primary-dark); color:#fff; }
        .btn-li-outline { background: transparent; color: var(--li-primary); border: 1px solid var(--li-primary); font-weight:600; }
        .btn-li-outline:hover { background: var(--li-primary); color:#fff; }

        .hero {
            background: linear-gradient(135deg, var(--li-primary-soft) 0%, #fff 60%);
            padding: 90px 0 80px;
        }
        .hero h1 { font-size: clamp(2rem, 4vw, 3.2rem); font-weight: 800; line-height: 1.15; }
        .hero h1 span { color: var(--li-primary); }
        .hero p.lead { font-size: 1.1rem; color: var(--li-muted); max-width: 540px; }
        .hero-card {
            background:#fff; border-radius: 16px; padding: 24px;
            box-shadow: 0 20px 50px -20px rgba(255,140,0,.35);
            border: 1px solid #f5e7d2;
        }
        .stat { border-right: 1px solid #f1e3cb; }
        .stat:last-child { border-right: 0; }
        .stat h3 { color: var(--li-primary); font-weight: 800; margin: 0; }
        .stat small { color: var(--li-muted); text-transform: uppercase; letter-spacing: .05em; font-size: .7rem; }

        .section { padding: 72px 0; }
        .section h2 { font-weight: 800; font-size: clamp(1.6rem, 2.5vw, 2.2rem); margin-bottom: .5rem; }
        .section .sub { color: var(--li-muted); max-width: 640px; margin: 0 auto 2.5rem; }

        .service-card {
            background:#fff; border:1px solid #eef0f3; border-radius: 14px; padding: 28px;
            height:100%; transition: all .2s ease;
        }
        .service-card:hover { transform: translateY(-4px); box-shadow: 0 16px 40px -20px rgba(0,0,0,.15); border-color: var(--li-primary); }
        .service-icon {
            width: 52px; height:52px; border-radius: 12px;
            background: var(--li-primary-soft); color: var(--li-primary);
            display: inline-flex; align-items:center; justify-content:center;
            font-size: 1.4rem; margin-bottom: 16px;
        }

        .step { display:flex; gap: 16px; margin-bottom: 22px; }
        .step-num {
            flex: 0 0 40px; height:40px; border-radius: 50%;
            background: var(--li-primary); color:#fff; font-weight: 700;
            display:inline-flex; align-items:center; justify-content:center;
        }

        .cta {
            background: var(--li-dark); color: #fff; border-radius: 18px;
            padding: 48px; text-align:center;
        }
        .cta h2 { color:#fff; }
        .cta p { color: #cbd5e1; }

        footer.li-footer { background:#0b1220; color:#94a3b8; padding: 32px 0; font-size:.9rem; }
        footer.li-footer a { color:#cbd5e1; text-decoration:none; }
        footer.li-footer a:hover { color: var(--li-primary); }

        .bg-soft { background: #fafbfc; }
    </style>
</head>
<body>

<!-- NAV -->
<nav class="li-nav">
    <div class="container d-flex align-items-center">
        <a href="#top" class="li-brand">
            <img src="{{ asset('images/logo-mini.png') }}" alt="Logo" style="height: 34px; border-radius: 8px;">
            <span>{{ config('app.name', 'Local Investors') }}</span>
        </a>
        <div class="ms-auto d-none d-md-flex align-items-center gap-3">
            <a class="nav-link" href="#services">Services</a>
            <a class="nav-link" href="#how">How to Join</a>
            <a class="nav-link" href="#contact">Contact</a>
            <a class="btn btn-li-outline btn-sm px-3" href="{{ route('login') }}">Member Login</a>
        </div>
        <a class="btn btn-li-primary btn-sm d-md-none ms-auto" href="{{ route('login') }}">Login</a>
    </div>
</nav>

<!-- HERO -->
<section class="hero" id="top">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <span class="badge rounded-pill px-3 py-2 mb-3" style="background: var(--li-primary-soft); color: var(--li-primary-dark);">
                    <i class="fas fa-shield-alt me-1"></i> Member-owned. Member-driven.
                </span>
                <h1 class="mb-3">Grow your money with <span>Local Investors</span>.</h1>
                <p class="lead mb-4">
                    A transparent, member-owned SACCO for savings, affordable loans and accountable group finance.
                    Track your contributions, loans and fines in real time — no paperwork, no guesswork.
                </p>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('login') }}" class="btn btn-li-primary px-4 py-2">
                        <i class="fas fa-sign-in-alt me-2"></i> Member Login
                    </a>
                    <a href="#services" class="btn btn-li-outline px-4 py-2">Learn More</a>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="hero-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <strong>Member Snapshot</strong>
                        <span class="badge bg-success-subtle text-success">Live</span>
                    </div>
                    @php
                        $memberCount = \App\Models\Member::where('is_active', true)->count();
                        $totalSavings = \App\Models\Contribution::sum('shares') + \App\Models\Contribution::sum('welfare') + \App\Models\Contribution::sum('merry_go_round');
                        $activeLoans = \App\Models\Loan::where('status', 'disbursed')->count();
                    @endphp
                    <div class="row text-center py-3">
                        <div class="col-4 stat">
                            <h3>{{ $memberCount }}</h3>
                            <small>Members</small>
                        </div>
                        <div class="col-4 stat">
                            <h3>KES {{ number_format($totalSavings, 0) }}</h3>
                            <small>Total Savings</small>
                        </div>
                        <div class="col-4 stat">
                            <h3>{{ $activeLoans }}</h3>
                            <small>Active Loans</small>
                        </div>
                    </div>
                    <hr />
                    <small class="text-muted d-block">
                        <i class="fas fa-circle text-success me-1" style="font-size: .5rem; vertical-align: middle;"></i>
                        Live figures — updated in real time from the SACCO ledger.
                    </small>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- SERVICES -->
<section class="section" id="services">
    <div class="container">
        <div class="text-center">
            <h2>What we offer</h2>
            <p class="sub">Everything a small SACCO needs — savings, credit and transparent bookkeeping — in one simple platform.</p>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="service-card">
                    <div class="service-icon"><i class="fas fa-piggy-bank"></i></div>
                    <h5 class="fw-bold">Savings &amp; Contributions</h5>
                    <p class="text-muted mb-0">Regular member contributions are tracked automatically, with statements you can download anytime.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="service-card">
                    <div class="service-icon"><i class="fas fa-hand-holding-usd"></i></div>
                    <h5 class="fw-bold">Affordable Loans</h5>
                    <p class="text-muted mb-0">Apply, get approved and repay — with a clear schedule, transparent interest and visible balance.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="service-card">
                    <div class="service-icon"><i class="fas fa-gavel"></i></div>
                    <h5 class="fw-bold">Fair Fines &amp; Fees</h5>
                    <p class="text-muted mb-0">Fines for late contributions or missed meetings are recorded openly and added to your statement.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="service-card">
                    <div class="service-icon"><i class="fas fa-chart-pie"></i></div>
                    <h5 class="fw-bold">Dividends &amp; Share-out</h5>
                    <p class="text-muted mb-0">At year-end profits are distributed to members in proportion to their contributions.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="service-card">
                    <div class="service-icon"><i class="fas fa-file-invoice"></i></div>
                    <h5 class="fw-bold">Reports &amp; Statements</h5>
                    <p class="text-muted mb-0">Exportable PDF and Excel reports for loans, contributions, income and expenditure.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="service-card">
                    <div class="service-icon"><i class="fas fa-bell"></i></div>
                    <h5 class="fw-bold">SMS &amp; Email Alerts</h5>
                    <p class="text-muted mb-0">Automatic reminders for meetings, loan repayments and outstanding fines.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- HOW TO JOIN -->
<section class="section bg-soft" id="how">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-6">
                <h2>How to join Local Investors</h2>
                <p class="text-muted">Becoming a member is straightforward. Reach out to a committee member and follow these four steps.</p>
                <div class="mt-4">
                    <div class="step"><div class="step-num">1</div><div><strong>Express interest</strong><div class="text-muted small">Contact any existing member or the secretary via the details below.</div></div></div>
                    <div class="step"><div class="step-num">2</div><div><strong>Fill the membership form</strong><div class="text-muted small">Provide your ID, phone number and next-of-kin details.</div></div></div>
                    <div class="step"><div class="step-num">3</div><div><strong>Pay registration &amp; first contribution</strong><div class="text-muted small">Your starter contribution activates your member account.</div></div></div>
                    <div class="step"><div class="step-num">4</div><div><strong>Receive your login</strong><div class="text-muted small">The admin creates your account and you can sign in to track everything online.</div></div></div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="hero-card h-100">
                    <h5 class="fw-bold mb-3">Why members stay</h5>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-3"><i class="fas fa-check-circle text-warning me-2"></i>Every shilling is traceable on your statement.</li>
                        <li class="mb-3"><i class="fas fa-check-circle text-warning me-2"></i>Loans are approved by the committee — not a stranger.</li>
                        <li class="mb-3"><i class="fas fa-check-circle text-warning me-2"></i>Members vote on rules, interest and dividends.</li>
                        <li class="mb-3"><i class="fas fa-check-circle text-warning me-2"></i>Profits come back to you at year-end.</li>
                        <li class="mb-0"><i class="fas fa-check-circle text-warning me-2"></i>Meetings, fines &amp; reminders happen on time.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CONTACT / CTA -->
<section class="section" id="contact">
    <div class="container">
        <div class="cta">
            <h2 class="mb-2">Ready to grow with us?</h2>
            <p class="mb-4">Existing members can sign in below. New members — contact the secretary to begin.</p>
            <div class="d-flex flex-wrap justify-content-center gap-2">
                <a href="{{ route('login') }}" class="btn btn-li-primary px-4 py-2"><i class="fas fa-sign-in-alt me-2"></i>Member Login</a>
                <a href="mailto:info@localinvestors.co.ke" class="btn btn-outline-light px-4 py-2"><i class="fas fa-envelope me-2"></i>info@localinvestors.co.ke</a>
            </div>
        </div>
    </div>
</section>

<!-- FOOTER -->
<footer class="li-footer">
    <div class="container d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            &copy; <script>document.write(new Date().getFullYear())</script> {{ config('app.name', 'Local Investors') }}. All rights reserved.
        </div>
        <div class="d-flex gap-3">
            <a href="#services">Services</a>
            <a href="#how">Join</a>
            <a href="{{ route('login') }}">Login</a>
        </div>
    </div>
</footer>

</body>
</html>
