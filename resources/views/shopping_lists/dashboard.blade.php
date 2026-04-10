@extends('layouts.app')

@section('title', 'Dashboard de Compras - Finanças Casal')
@section('page_title', 'Dashboard de Compras')

@section('actions')
    <a href="{{ route('shopping-lists.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Voltar
    </a>
@endsection

@section('content')
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form action="{{ route('shopping-lists.dashboard') }}" method="GET" class="row g-3 align-items-end">            <div class="col-md-2">
                <label class="form-label small fw-bold">Ano</label>
                <select name="year" class="form-select form-select-sm">
                    @foreach($availableYears as $y)
                        <option value="{{ $y }}" {{ request('year', now()->year) == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">Mês</label>
                <select name="month" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    @php
                        $meses = [
                            1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
                            5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
                            9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
                        ];
                    @endphp
                    @foreach($meses as $num => $nome)
                        <option value="{{ $num }}" {{ request('month') == $num ? 'selected' : '' }}>
                            {{ $nome }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold">Período</label>
                <div class="input-group input-group-sm">
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                    <span class="input-group-text">até</span>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">Categoria</label>
                <select name="category_id" class="form-select form-select-sm">
                    <option value="">Todas</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-primary flex-fill">
                    <i class="bi bi-filter"></i> Filtrar
                </button>
                <a href="{{ route('shopping-lists.dashboard') }}" class="btn btn-sm btn-outline-secondary px-2">
                    <i class="bi bi-x-circle"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-primary text-white h-100">
            <div class="card-body">
                <small class="text-white-50 fw-bold">TOTAL GASTO</small>
                <div class="h3 fw-bold">R$ {{ number_format($stats['total_spent'], 2, ',', '.') }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <small class="text-muted fw-bold">MÉDIA POR LISTA</small>
                <div class="h3 fw-bold">R$ {{ number_format($stats['avg_list_value'], 2, ',', '.') }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <small class="text-muted fw-bold">TOTAL DE LISTAS</small>
                <div class="h3 fw-bold">{{ $stats['total_lists'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <small class="text-muted fw-bold">TOTAL DE ITENS</small>
                <div class="h3 fw-bold">{{ $stats['item_count'] }}</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold">{{ request('month') ? 'Evolução Diária' : 'Evolução Mensal' }}</h5>
            </div>
            <div class="card-body">
                @if($timeSpending->count() > 0)
                    <div style="position: relative; height: 300px; width: 100%;">
                        <canvas id="timeChart"></canvas>
                    </div>
                @else
                    <div class="text-center py-5 text-muted">Nenhum dado para o gráfico de evolução.</div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold">Gastos por Categoria</h5>
            </div>
            <div class="card-body d-flex align-items-center">
                @if($categoryDistribution->count() > 0)
                    <div style="position: relative; height: 300px; width: 100%;">
                        <canvas id="categoryChart"></canvas>
                    </div>
                @else
                    <div class="text-center py-5 text-muted flex-fill">Sem dados de categoria.</div>
                @endif
            </div>
        </div>
    </div>
</div></div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold">Impacto no Orçamento (Filtrado)</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Item</th>
                                <th class="text-center">Preço Médio</th>
                                <th class="text-end pe-4">Total Gasto</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topItems as $item)
                            <tr>
                                <td class="ps-4 fw-bold">{{ $item->name }}</td>
                                <td class="text-center text-muted">R$ {{ number_format($item->avg_price, 2, ',', '.') }}</td>
                                <td class="text-end pe-4 fw-bold text-primary">R$ {{ number_format($item->total_spent, 2, ',', '.') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center py-4">Nenhum item encontrado com os filtros aplicados.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    @if($timeSpending->count() > 0)
    // Gráfico de Tempo (Barra)
    const timeCtx = document.getElementById('timeChart').getContext('2d');
    new Chart(timeCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($timeSpending->pluck('period')) !!},
            datasets: [{
                label: 'Gasto Total (R$)',
                data: {!! json_encode($timeSpending->pluck('total')) !!},
                backgroundColor: '#0d6efd',
                borderRadius: 5,
                barThickness: 30
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
    @endif


    @if($categoryDistribution->count() > 0)
    // Gráfico de Categoria
    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
    new Chart(categoryCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($categoryDistribution->pluck('name')) !!},
            datasets: [{
                data: {!! json_encode($categoryDistribution->pluck('total')) !!},
                backgroundColor: {!! json_encode($categoryDistribution->pluck('color')->map(fn($c) => $c ?? '#6c757d')) !!},
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            },
            cutout: '70%'
        }
    });
    @endif
});
</script>
@endpush
@endsection
