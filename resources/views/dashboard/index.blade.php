@extends('layouts.app')

@section('title', 'Dashboard - Finanças Casal')
@section('page_title', 'Visão Geral')

@section('actions')
    <div class="d-flex gap-2">
        <a href="{{ route('dashboard.cards') }}" class="btn btn-sm btn-outline-info shadow-sm">
            <i class="bi bi-credit-card-2-back"></i> Análise de Cartões
        </a>
        <a href="{{ route('transactions.create') }}" class="btn btn-sm btn-primary shadow-sm">
            <i class="bi bi-plus-lg"></i> Nova Transação
        </a>
    </div>
@endsection

@section('content')
<!-- Filtros -->
<div class="card mb-4 border-0 shadow-sm">
    <div class="card-body">
        <form action="{{ route('dashboard') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-bold">Mês de Referência:</label>
                <div class="d-flex gap-2">
                    <select name="month" class="form-select form-select-sm">
                        @foreach(range(1, 12) as $m)
                            <option value="{{ $m }}" {{ request('month', now()->month) == $m ? 'selected' : '' }}>
                                {{ ucfirst(\Carbon\Carbon::create()->month($m)->translatedFormat('F')) }}
                            </option>
                        @endforeach
                    </select>
                    <input type="number" name="year" class="form-control form-control-sm" value="{{ request('year', now()->year) }}" style="width: 80px;">
                </div>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">De:</label>
                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">Até:</label>
                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary flex-fill">
                    <i class="bi bi-filter"></i> Filtrar Dashboard
                </button>
                <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-x-lg"></i>
                </a>
            </div>
            <div class="col-md-2 text-end">
                <span class="badge bg-primary-subtle text-primary p-2">
                    {{ ucfirst($currentMonthName) }}
                </span>
            </div>
        </form>
    </div>
</div>

<!-- Top Metrics -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-light h-100">
            <div class="card-body">
                <h6 class="card-title text-muted small text-uppercase fw-bold">Saldo Inicial (Período)</h6>
                <h3 class="mb-0 fw-bold text-secondary">R$ {{ number_format($openingBalanceOfPeriod, 2, ',', '.') }}</h3>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-success text-white h-100">
            <div class="card-body">
                <h6 class="card-title text-white-50 small text-uppercase fw-bold">Entradas (Previstas)</h6>
                <h3 class="mb-0 fw-bold">R$ {{ number_format($projectedIncome, 2, ',', '.') }}</h3>
                <small class="opacity-75">Realizado: R$ {{ number_format($realizedIncome, 2, ',', '.') }}</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-danger text-white h-100">
            <div class="card-body">
                <h6 class="card-title text-white-50 small text-uppercase fw-bold">Saídas (Previstas)</h6>
                <h3 class="mb-0 fw-bold">R$ {{ number_format($projectedExpense, 2, ',', '.') }}</h3>
                <small class="opacity-75">Realizado: R$ {{ number_format($realizedExpense, 2, ',', '.') }}</small>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-primary text-white h-100">
            <div class="card-body">
                <h6 class="card-title text-white-50 small text-uppercase fw-bold">Saldo Final (Projetado)</h6>
                <h3 class="mb-0 fw-bold">R$ {{ number_format($projectedFinalBalance, 2, ',', '.') }}</h3>
                <small class="opacity-75">Saldo Atual: R$ {{ number_format($totalCurrentBalance, 2, ',', '.') }}</small>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Charts and Tables -->
    <div class="col-lg-8">
        <!-- Balance Trend Chart -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title fw-bold mb-4">Evolução do Patrimônio (6 meses)</h5>
                <div style="height: 300px;">
                    <canvas id="balanceChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Upcoming Bills -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title fw-bold mb-3">Próximos Vencimentos (7 dias)</h5>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th>Data</th>
                                <th>Descrição</th>
                                <th>Categoria</th>
                                <th class="text-end">Valor</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($upcomingBills as $bill)
                            <tr>
                                <td class="small fw-bold">{{ $bill->date->format('d/m') }}</td>
                                <td>
                                    <div class="fw-bold">{{ $bill->description }}</div>
                                    <small class="text-muted">{{ $bill->account->name }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">{{ $bill->category->name ?? 'Geral' }}</span>
                                </td>
                                <td class="text-end fw-bold {{ $bill->type == 'income' ? 'text-success' : 'text-danger' }}">
                                    {{ $bill->type == 'income' ? '+' : '-' }} R$ {{ number_format($bill->amount, 2, ',', '.') }}
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('transactions.payable') }}?month={{ $bill->date->month }}&year={{ $bill->date->year }}" class="btn btn-sm btn-outline-primary shadow-sm py-0 pb-1">Pagar</a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">Nenhuma conta vencendo nos próximos dias.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar Info -->
    <div class="col-lg-4">
        <!-- Account Balances -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title fw-bold mb-3">Saldos por Conta</h5>
                <div class="list-group list-group-flush">
                    @foreach($accounts as $account)
                    <div class="list-group-item px-0 border-0 border-bottom mb-2">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <div class="fw-bold text-primary">{{ $account->name }}</div>
                            <div class="small text-muted">{{ $account->type }}</div>
                        </div>
                        <div class="d-flex justify-content-between">
                            <div class="small">
                                <span class="text-muted">Atual:</span> 
                                <span class="fw-bold">R$ {{ number_format($account->balance, 2, ',', '.') }}</span>
                            </div>
                            <div class="small">
                                <span class="text-muted text-uppercase" style="font-size: 0.65rem;">Projetado:</span> 
                                <span class="fw-bold {{ $account->projected_balance >= 0 ? 'text-success' : 'text-danger' }}">
                                    R$ {{ number_format($account->projected_balance, 2, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Expenses by Category Chart -->
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h5 class="card-title fw-bold mb-4 text-start">Gastos por Categoria</h5>
                <div style="height: 250px;">
                    <canvas id="expensesChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function() {
    // Chart 1: Balance Trend
    const ctxBalance = document.getElementById('balanceChart').getContext('2d');
    new Chart(ctxBalance, {
        type: 'line',
        data: {
            labels: {!! json_encode($trendLabels) !!},
            datasets: [{
                label: 'Patrimônio Líquido',
                data: {!! json_encode($trendData) !!},
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                borderWidth: 3,
                tension: 0.3,
                fill: true,
                pointBackgroundColor: '#0d6efd',
                pointRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: false,
                    grid: { borderDash: [5, 5] }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });

    // Chart 2: Category Distribution
    const ctxExpenses = document.getElementById('expensesChart').getContext('2d');
    new Chart(ctxExpenses, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($chartLabels) !!},
            datasets: [{
                data: {!! json_encode($chartData) !!},
                backgroundColor: [
                    '#0d6efd', '#20c997', '#ffc107', '#dc3545', '#6610f2', '#fd7e14', '#138496'
                ],
                hoverOffset: 4,
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        padding: 20,
                        font: { size: 10 }
                    }
                }
            }
        }
    });
});
</script>
@endpush
