@extends('layouts.app')

@section('title', 'Análise de Cartões - Finanças Casal')
@section('page_title', 'Análise Detalhada: Cartões de Crédito')

@section('actions')
    <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Voltar ao Geral
    </a>
@endsection

@section('content')
<!-- Filtros Rápidos -->
<div class="card mb-4 border-0 shadow-sm">
    <div class="card-body">
        <form action="{{ route('dashboard.cards') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-bold">Período:</label>
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
                <label class="form-label small fw-bold">Cartão:</label>
                <select name="credit_card_id" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    @foreach($creditCards as $cc)
                        <option value="{{ $cc->id }}" {{ request('credit_card_id') == $cc->id ? 'selected' : '' }}>{{ $cc->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">Categoria:</label>
                <select name="category_id" class="form-select form-select-sm">
                    <option value="">Todas</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold">Descrição:</label>
                <input type="text" name="description" class="form-control form-control-sm" value="{{ request('description') }}" placeholder="Buscar no cartão...">
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-primary flex-fill">
                    <i class="bi bi-filter"></i>
                </button>
                <a href="{{ route('dashboard.cards') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-x-lg"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<div class="row">
    <!-- Ranking de Cartões -->
    <div class="col-lg-12 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h5 class="card-title fw-bold mb-4">Utilização de Limite por Cartão</h5>
                <div class="row">
                    @foreach($cardsSpending as $card)
                    <div class="col-md-4 mb-3">
                        <div class="p-3 border rounded shadow-sm">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold fs-5">{{ $card['name'] }}</span>
                                <span class="badge {{ $card['status'] == 'paid' ? 'bg-success' : 'bg-warning text-dark' }}">
                                    {{ $card['status'] == 'paid' ? 'Paga' : 'Aberta' }}
                                </span>
                            </div>
                            <div class="mb-1 d-flex justify-content-between">
                                <small class="text-muted">Gasto: R$ {{ number_format($card['spent'], 2, ',', '.') }}</small>
                                <small class="text-muted">Limite: R$ {{ number_format($card['limit'], 2, ',', '.') }}</small>
                            </div>
                            @php 
                                $percent = $card['limit'] > 0 ? ($card['spent'] / $card['limit']) * 100 : 0;
                                $color = $percent > 80 ? 'bg-danger' : ($percent > 50 ? 'bg-warning' : 'bg-success');
                            @endphp
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar {{ $color }}" role="progressbar" style="width: {{ $percent }}%" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <div class="text-end mt-1">
                                <small class="fw-bold">{{ round($percent, 1) }}% do limite</small>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Categorias Internas vs Maiores Gastos -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <h5 class="card-title fw-bold mb-4 text-start">O que tem dentro das faturas?</h5>
                <div style="height: 300px;">
                    <canvas id="cardCategoryChart"></canvas>
                </div>
                <div class="mt-4 text-start">
                    <small class="text-muted">Mostra a distribuição de categorias apenas dos gastos feitos no cartão de crédito no período selecionado.</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title fw-bold mb-0">Todos os Gastos na Fatura</h5>
                    <span class="badge bg-secondary">{{ $allTransactions->count() }} itens</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th>Data</th>
                                <th>Cartão</th>
                                <th>Descrição</th>
                                <th>Categoria</th>
                                <th class="text-end">Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($allTransactions as $expense)
                            <tr>
                                <td class="small">{{ $expense->date->format('d/m') }}</td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary border-0">
                                        {{ $expense->invoice->creditCard->name }}
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-bold">{{ $expense->description }}</div>
                                    @if($expense->installments > 1)
                                        <small class="badge bg-secondary-subtle text-secondary">{{ $expense->current_installment }}/{{ $expense->installments }}</small>
                                    @endif
                                </td>
                                <td>
                                    <select class="form-select form-select-sm category-select" 
                                            data-id="{{ $expense->id }}"
                                            style="min-width: 140px;">
                                        <option value="">Geral</option>
                                        @foreach($categories as $cat)
                                            <option value="{{ $cat->id }}" {{ $expense->category_id == $cat->id ? 'selected' : '' }}>
                                                {{ $cat->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="text-end fw-bold">R$ {{ number_format($expense->amount, 2, ',', '.') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">Nenhum gasto registrado nos cartões para este período.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function() {
    // Logic do Gráfico
    const ctx = document.getElementById('cardCategoryChart').getContext('2d');
    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: {!! json_encode($chartLabels) !!},
            datasets: [{
                data: {!! json_encode($chartData) !!},
                backgroundColor: [
                    '#0d6efd', '#6610f2', '#6f42c1', '#d63384', '#dc3545', '#fd7e14', '#ffc107', '#198754', '#20c997', '#0dcaf0'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        padding: 15
                    }
                }
            }
        }
    });

    // Lógica de Categorização Rápida
    const selects = document.querySelectorAll('.category-select');
    selects.forEach(select => {
        select.addEventListener('change', function() {
            const transactionId = this.dataset.id;
            const categoryId = this.value;
            const originalColor = this.style.borderColor;
            
            this.disabled = true;
            this.style.borderColor = '#0d6efd';

            fetch(`/credit-card-transactions/${transactionId}/update-category`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ category_id: categoryId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.style.borderColor = '#198754';
                    setTimeout(() => {
                        this.style.borderColor = originalColor;
                        this.disabled = false;
                        // Opcional: Recarregar gráfico? Como o gráfico é gerado no backend, precisaria recarregar a página
                        // ou emitir um evento. Para não ser intrusivo, não recarregamos agora.
                    }, 1000);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                this.style.borderColor = '#dc3545';
                this.disabled = false;
            });
        });
    });
});
</script>
@endpush
