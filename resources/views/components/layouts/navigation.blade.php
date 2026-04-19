<nav class="nxl-navigation">
    <div class="navbar-wrapper">
        <div class="m-header">
            <a href="{{ route('dashboard') }}" class="b-brand">
                <img src="{{ asset('assets/images/logo.png') }}" alt="" class="logo logo-lg" />
                <img src="{{ asset('assets/images/logo-mini.png') }}" alt="" class="logo logo-sm" />
            </a>
        </div>
        <div class="navbar-content">
            <ul class="nxl-navbar">
                <li class="nxl-item nxl-caption">
                    <label>Navigation</label>
                </li>

                @role('member')
                <!-- Member portal -->
                <li class="nxl-item {{ request()->routeIs('portal.dashboard') ? 'active' : '' }}">
                    <a href="{{ route('portal.dashboard') }}" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-home"></i></span>
                        <span class="nxl-mtext">My Dashboard</span>
                    </a>
                </li>
                <li class="nxl-item {{ request()->routeIs('portal.statement') ? 'active' : '' }}">
                    <a href="{{ route('portal.statement') }}" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-file-text"></i></span>
                        <span class="nxl-mtext">My Statement</span>
                    </a>
                </li>
                <li class="nxl-item {{ request()->routeIs('portal.apply-loan') ? 'active' : '' }}">
                    <a href="{{ route('portal.apply-loan') }}" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-credit-card"></i></span>
                        <span class="nxl-mtext">Apply for Loan</span>
                    </a>
                </li>
                @endrole

                @role('admin|treasurer')
                <!-- Dashboards -->
                <li class="nxl-item nxl-hasmenu">
                    <a href="javascript:void(0);" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-airplay"></i></span>
                        <span class="nxl-mtext">Dashboards</span>
                        <span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                    </a>
                    <ul class="nxl-submenu">
                        <li class="nxl-item"><a class="nxl-link" href="{{ route('dashboard') }}"><i class="fa fa-circle me-2" style="font-size:7px; vertical-align:middle;"></i>CRM</a></li>
                        <li class="nxl-item"><a class="nxl-link" href="{{ route('analytics') }}"><i class="fa fa-circle me-2" style="font-size:7px; vertical-align:middle;"></i>Analytics</a></li>
                    </ul>
                </li>

                <!-- Members -->
                <li class="nxl-item nxl-hasmenu {{ request()->routeIs('members.*') ? 'active nxl-opened' : '' }}">
                    <a href="javascript:void(0);" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-user"></i></span>
                        <span class="nxl-mtext">Members</span>
                        <span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                    </a>
                    <ul class="nxl-submenu" {!! request()->routeIs('members.*') ? 'style="display: block;"' : '' !!}>
                        <li class="nxl-item {{ request()->routeIs('members.*') ? 'active' : '' }}"><a class="nxl-link {{ request()->routeIs('members.*') ? 'active' : '' }}" href="{{ route('members.index') }}"><i class="fa fa-circle me-2" style="font-size:7px; vertical-align:middle;"></i>Members</a></li>
                    </ul>
                </li>

                <!-- Contributions -->
                <li class="nxl-item nxl-hasmenu {{ request()->routeIs('contributions.*') ? 'active nxl-opened' : '' }}">
                    <a href="javascript:void(0);" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-dollar-sign"></i></span>
                        <span class="nxl-mtext">Contributions</span>
                        <span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                    </a>
                    <ul class="nxl-submenu" {!! request()->routeIs('contributions.*') ? 'style="display: block;"' : '' !!}>
                        <li class="nxl-item {{ request()->routeIs('contributions.*') ? 'active' : '' }}"><a class="nxl-link {{ request()->routeIs('contributions.*') ? 'active' : '' }}" href="{{ route('contributions.index') }}"><i class="fa fa-circle me-2" style="font-size:7px; vertical-align:middle;"></i>Contributions</a></li>
                    </ul>
                </li>

                <!-- Loans -->
                <li class="nxl-item nxl-hasmenu {{ request()->routeIs('loans.*') ? 'active nxl-opened' : '' }}">
                    <a href="javascript:void(0);" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-credit-card"></i></span>
                        <span class="nxl-mtext">Loans</span>
                        <span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                    </a>
                    <ul class="nxl-submenu" {!! request()->routeIs('loans.*') ? 'style="display: block;"' : '' !!}>
                        <li class="nxl-item {{ request()->routeIs('loans.*') ? 'active' : '' }}"><a class="nxl-link {{ request()->routeIs('loans.*') ? 'active' : '' }}" href="{{ route('loans.index') }}"><i class="fa fa-circle me-2" style="font-size:7px; vertical-align:middle;"></i>Loans</a></li>
                    </ul>
                </li>

                <!-- Financial -->
                <li class="nxl-item nxl-hasmenu {{ request()->routeIs('incomes.*') || request()->routeIs('expenditures.*') || request()->routeIs('withdrawals.*') || request()->routeIs('cash-returns.*') || request()->routeIs('projects.*') ? 'active nxl-opened' : '' }}">
                    <a href="javascript:void(0);" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-pie-chart"></i></span>
                        <span class="nxl-mtext">Financial</span>
                        <span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                    </a>
                    <ul class="nxl-submenu" {!! request()->routeIs('incomes.*') || request()->routeIs('expenditures.*') || request()->routeIs('withdrawals.*') || request()->routeIs('cash-returns.*') || request()->routeIs('projects.*') ? 'style="display: block;"' : '' !!}>
                        <li class="nxl-item {{ request()->routeIs('projects.*') ? 'active' : '' }}"><a class="nxl-link {{ request()->routeIs('projects.*') ? 'active' : '' }}" href="{{ route('projects.index') }}"><i class="fa fa-circle me-2" style="font-size:7px; vertical-align:middle;"></i>Projects</a></li>
                        <li class="nxl-item {{ request()->routeIs('incomes.*') ? 'active' : '' }}"><a class="nxl-link {{ request()->routeIs('incomes.*') ? 'active' : '' }}" href="{{ route('incomes.index') }}"><i class="fa fa-circle me-2" style="font-size:7px; vertical-align:middle;"></i>Incomes</a></li>
                        <li class="nxl-item {{ request()->routeIs('expenditures.*') ? 'active' : '' }}"><a class="nxl-link {{ request()->routeIs('expenditures.*') ? 'active' : '' }}" href="{{ route('expenditures.index') }}"><i class="fa fa-circle me-2" style="font-size:7px; vertical-align:middle;"></i>Expenditures</a></li>
                        <li class="nxl-item {{ request()->routeIs('withdrawals.*') ? 'active' : '' }}"><a class="nxl-link {{ request()->routeIs('withdrawals.*') ? 'active' : '' }}" href="{{ route('withdrawals.index') }}"><i class="fa fa-circle me-2" style="font-size:7px; vertical-align:middle;"></i>Withdrawals</a></li>
                        <li class="nxl-item {{ request()->routeIs('cash-returns.*') ? 'active' : '' }}"><a class="nxl-link {{ request()->routeIs('cash-returns.*') ? 'active' : '' }}" href="{{ route('cash-returns.index') }}"><i class="fa fa-circle me-2" style="font-size:7px; vertical-align:middle;"></i>Cash Returns</a></li>
                    </ul>
                </li>

                <!-- Fines -->
                @role('admin|treasurer')
                <li class="nxl-item {{ request()->routeIs('fines.*') ? 'active' : '' }}">
                    <a href="{{ route('fines.index') }}" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-alert-triangle"></i></span>
                        <span class="nxl-mtext">Fines</span>
                    </a>
                </li>
                @endrole

                <!-- Dividends -->
                @role('admin|treasurer')
                <li class="nxl-item {{ request()->routeIs('dividends') ? 'active' : '' }}">
                    <a href="{{ route('dividends') }}" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-gift"></i></span>
                        <span class="nxl-mtext">Dividends</span>
                    </a>
                </li>
                @endrole

                <!-- Reports -->
                <li class="nxl-item {{ request()->routeIs('reports') ? 'active' : '' }}">
                    <a href="{{ route('reports') }}" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-bar-chart-2"></i></span>
                        <span class="nxl-mtext">Reports</span>
                    </a>
                </li>
                @endrole

                <!-- Settings -->
                @role('admin')
                <li class="nxl-item {{ request()->routeIs('settings') ? 'active' : '' }}">
                    <a href="{{ route('settings') }}" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-settings"></i></span>
                        <span class="nxl-mtext">Settings</span>
                    </a>
                </li>
                @endrole
            </ul>
            
            <div class="card text-center d-none">
                <div class="card-body">
                    <i class="feather-sunrise fs-4 text-dark"></i>
                    <h6 class="mt-4 text-dark fw-bolder">Local Investors</h6>
                    <p class="fs-11 my-3 text-dark">SACCO Management Platform</p>
                </div>
            </div>
            
        </div>
    </div>
</nav>
