@extends('layouts.app')

@section('title', "Extrato - {$account->name}")
@section('page_title', "Extrato: {$account->name}")

@section('actions')
    <button type="button" class="btn btn-sm btn-success shadow-sm me-2" data-bs-toggle="modal" data-bs-target="#futureIncomeModal">
        <i class="bi bi-calendar-plus"></i> Prever Entrada
    </button>
    <a href="{{ route('accounts.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Voltar
    </a>
@endsection

@section('content')
<<div class="row mb-4">
    <div class="col-md-12 d-flex justify-content-between align-items-center mb-3">
        <div class="btn-group shadow-sm">
            <a href="{{ route('accounts.show', [$account->id] + $prevParams) }}" class="btn btn-outline-primary">
                <i class="bi bi-chevron-left"></i> Anterior
            </a>
            <span class="btn btn-primary px-4 fw-bold disabled opacity-100">
                {{ ucfirst($currentMonthName) }}
            </span>
            <a href="{{ route('accounts.show', [$account->id] + $nextParams) }}" class="btn btn-outline-primary">
                Próximo <i class="bi bi-chevron-right"></i>
            </a>
        </div>
        
        <div class="d-flex gap-2">
            <div class="card p-2 px-3 border-secondary border-2 shadow-sm bg-light">
                <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Saldo Inicial</small>
                <div class="fw-bold">R$ {{ number_format($openingBalance, 2, ',', '.') }}</div>
            </div>
            <div class="card p-2 px-3 border-success border-2 shadow-sm">
                <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Receitas</small>
                <div class="text-success fw-bold">R$ {{ number_format($periodIncome, 2, ',', '.') }}</div>
            </div>
            <div class="card p-2 px-3 border-danger border-2 shadow-sm">
                <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Despesas</small>
                <div class="text-danger fw-bold">R$ {{ number_format($periodExpense, 2, ',', '.') }}</div>
            </div>
            <div class="card p-2 px-3 border-primary border-2 shadow-sm bg-primary text-white">
                <small class="text-white-50 text-uppercase fw-bold" style="font-size: 0.7rem;">Saldo Final</small>
                <div class="fw-bold">R$ {{ number_format($openingBalance + $periodIncome - $periodExpense, 2, ',', '.') }}</div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="col-12">
        <div class="card border-0 shadow-sm bg-light mb-3">
            <div class="card-body py-2">
                <form action="{{ route('accounts.show', $account) }}" method="GET" class="row g-2 align-items-end">
                    <input type="hidden" name="month" value="{{ request('month') }}">
                    <input type="hidden" name="year" value="{{ request('year') }}">
                    
                    <div class="col-md-4">
                        <label class="form-label small mb-1">Descrição</label>
                        <input type="text" name="description" class="form-control form-control-sm" value="{{ request('description') }}" placeholder="Buscar por descrição...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small mb-1">Categoria</label>
                        <select name="category_id" class="form-select form-select-sm">
                            <option value="">Todas as Categorias</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-sm btn-primary w-100"><i class="bi bi-filter"></i> Filtrar</button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('accounts.show', $account->id) }}" class="btn btn-sm btn-outline-secondary w-100">Limpar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white py-3">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Histórico de Lançamentos</h5>
            <span class="text-muted">Saldo Atual da Conta: <strong>R$ {{ number_format($account->balance, 2, ',', '.') }}</strong></span>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Data</th>
                        <th>Descrição</th>
                        <th>Categoria</th>
                        <th class="text-end pe-4">Valor</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $t)
                    <tr>
                        <td class="ps-4">{{ $t->date->format('d/m/Y') }}</td>
                        <td>{{ $t->description }}</td>
                        <td>
                            @if($t->category)
                                <span class="badge rounded-pill" style="background-color: {{ $t->category->color ?? '#6c757d' }}">
                                    {{ $t->category->name }}
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-end pe-4 fw-bold {{ $t->type == 'income' ? 'text-success' : 'text-danger' }}">
                            {{ $t->type == 'income' ? '+' : '-' }} R$ {{ number_format($t->amount, 2, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-5 text-muted">Nenhum lançamento confirmado neste período.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Nova Previsão de Entrada -->
<div class="modal fade" id="futureIncomeModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Agendar Entrada Futurista (Salário, 13º, etc)</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('transactions.store') }}" method="POST">
          @csrf
          <input type="hidden" name="account_id" value="{{ $account->id }}">
          <input type="hidden" name="type" value="income">
          <input type="hidden" name="status" value="pending">
          
          <div class="modal-body">
              <div class="mb-3">
                  <label class="form-label">Descrição</label>
                  <input type="text" name="description" class="form-control" placeholder="Ex: Salário Mensal, 13º Salário, Reembolso" required>
              </div>
              <div class="row mb-3">
                  <div class="col-md-6">
                      <label class="form-label">Valor Previsto (R$)</label>
                      <input type="number" step="0.01" name="amount" class="form-control" required>
                  </div>
                  <div class="col-md-6">
                      <label class="form-label">Data Prevista</label>
                      <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                  </div>
              </div>
              <div class="mb-3">
                  <label class="form-label">Categoria</label>
                  <select name="category_id" class="form-select">
                      <option value="">Selecione...</option>
                      @foreach($categories as $cat)
                          <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                      @endforeach
                  </select>
              </div>
              <div class="form-check mb-2">
                  <input class="form-check-input" type="checkbox" name="is_recurring" value="1" id="isRecurringModal">
                  <label class="form-check-label" for="isRecurringModal">
                      Lançamento Recorrente (Repetir mensalmente)
                  </label>
              </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-success">Agendar Receita</button>
          </div>
      </form>
    </div>
  </div>
</div>
@endsection
