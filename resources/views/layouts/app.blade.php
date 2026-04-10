<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Finanças Casal')</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        body { background-color: #f8f9fa; }
        .sidebar { min-height: 100vh; background: #212529; color: white; }
        .sidebar a { color: rgba(255,255,255,.75); text-decoration: none; padding: 10px 15px; display: block; }
        .sidebar a:hover, .sidebar a.active { color: white; background: rgba(255,255,255,.1); border-radius: 5px; }
        .card { border: none; box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,.075); margin-bottom: 20px; }
    </style>
    @stack('styles')
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav class="col-md-3 col-lg-2 d-md-block sidebar py-3 px-2 collapse" id="sidebarMenu">
            <div class="position-sticky">
                <h4 class="text-center mb-4"><i class="bi bi-wallet2 text-primary"></i> Finanças</h4>
                <ul class="nav flex-column gap-1">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                            <i class="bi bi-speedometer2 me-2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('accounts.*') ? 'active' : '' }}" href="{{ route('accounts.index') }}">
                            <i class="bi bi-bank me-2"></i> Contas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('transactions.payable') ? 'active' : '' }}" href="{{ route('transactions.payable') }}">
                            <i class="bi bi-calendar-check me-2"></i> Contas do Mês
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('transactions.index') ? 'active' : '' }}" href="{{ route('transactions.index') }}">
                            <i class="bi bi-arrow-left-right me-2"></i> Histórico Transações
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('shopping-lists.*') ? 'active' : '' }}" href="{{ route('shopping-lists.index') }}">
                            <i class="bi bi-cart-check me-2"></i> Lista de Compras
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('credit-cards.*') ? 'active' : '' }}" href="{{ route('credit-cards.index') }}">
                            <i class="bi bi-credit-card me-2"></i> Cartões de Crédito
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('investments.*') ? 'active' : '' }}" href="{{ route('investments.index') }}">
                            <i class="bi bi-graph-up-arrow me-2"></i> Investimentos
                        </a>
                    </li>
                    <li class="nav-item mt-4">
                        <a class="nav-link {{ request()->routeIs('categories.*') ? 'text-white' : 'text-warning' }}" href="{{ route('categories.index') }}">
                            <i class="bi bi-tags me-2"></i> Categorias
                        </a>
                    </li>
                    <li class="nav-item border-top mt-3 pt-3">
                        <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">
                            <i class="bi bi-people me-2"></i> Gestão de Usuários
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('logs.index') ? 'active' : '' }}" href="{{ route('logs.index') }}">
                            <i class="bi bi-shield-check me-2"></i> Auditoria de Logs
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}" href="{{ route('profile.edit') }}">
                            <i class="bi bi-person-circle me-2"></i> Meu Perfil / Senha
                        </a>
                    </li>
                    <li class="nav-item">
                        <form method="POST" action="{{ route('logout') }}" id="logout-form">
                            @csrf
                            <a class="nav-link text-danger" href="#" onclick="document.getElementById('logout-form').submit();">
                                <i class="bi bi-box-arrow-left me-2"></i> Sair do Sistema
                            </a>
                        </form>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center mb-3">
                <h1 class="h3">@yield('page_title')</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    @yield('actions')
                </div>
            </div>

            <script>
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer)
                        toast.addEventListener('mouseleave', Swal.resumeTimer)
                    }
                });
                window.Toast = Toast; // Global helper

                @if(session('success'))
                    Toast.fire({ icon: 'success', title: '{{ session('success') }}' });
                @endif

                @if(session('error'))
                    Toast.fire({ icon: 'error', title: '{{ session('error') }}' });
                @endif

                // Modal de Confirmação Global
                document.addEventListener('click', function(e) {
                    const btn = e.target.closest('.confirm-action');
                    if (btn) {
                        e.preventDefault();
                        const form = btn.closest('form');
                        const title = btn.dataset.confirmTitle || 'Tem certeza?';
                        const text = btn.dataset.confirmText || 'Esta ação não poderá ser desfeita.';
                        const type = btn.dataset.confirmType || 'warning';

                        Swal.fire({
                            title: title,
                            text: text,
                            icon: type,
                            showCancelButton: true,
                            confirmButtonColor: '#198754',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'Sim, confirmar',
                            cancelButtonText: 'Cancelar',
                            reverseButtons: true
                        }).then((result) => {
                            if (result.isConfirmed) {
                                if (form) form.submit();
                                else if (btn.tagName === 'A') window.location.href = btn.href;
                            }
                        });
                    }
                });
            </script>

            @yield('content')
        </main>
    </div>
</div>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@stack('scripts')
</body>
</html>
