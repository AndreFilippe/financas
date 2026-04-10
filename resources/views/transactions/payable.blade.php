@extends('layouts.app')

@section('title', 'Contas do Mês - Finanças Casal')
@section('page_title', 'Contas do Mês Vigente')

@section('content')
<div class="row mb-4">
    <div class="col-12 d-flex flex-column flex-lg-row justify-content-between mb-3 gap-3">
        <div class="btn-group shadow-sm w-100 w-lg-auto">
            <a href="{{ route('transactions.payable', $prevParams) }}" class="btn btn-outline-primary px-2 px-md-3">
                <i class="bi bi-chevron-left"></i><span class="d-none d-sm-inline"> Anterior</span>
            </a>
            <span class="btn btn-primary px-3 px-md-4 fw-bold disabled opacity-100 text-truncate" style="max-width: 150px;">
                {{ ucfirst($currentMonthName) }}
            </span>
            <a href="{{ route('transactions.payable', $nextParams) }}" class="btn btn-outline-primary px-2 px-md-3">
                <span class="d-none d-sm-inline">Próximo </span><i class="bi bi-chevron-right"></i>
            </a>
        </div>

        <div class="d-flex flex-column flex-sm-row gap-2 w-100 w-lg-auto">
            <form action="{{ route('transactions.replicate-recurrences') }}" method="POST" class="w-100 w-sm-auto">
                @csrf
                <input type="hidden" name="month" value="{{ request('month', now()->month) }}">
                <input type="hidden" name="year" value="{{ request('year', now()->year) }}">
                <button type="submit" class="btn btn-outline-success shadow-sm w-100" title="Puxar recorrências do mês anterior">
                    <i class="bi bi-arrow-repeat"></i> <span class="d-sm-none d-md-inline">Sincronizar Mês Passado</span><span class="d-none d-sm-inline d-md-none">Repetir</span>
                </button>
            </form>

            <button type="button" class="btn btn-primary shadow-sm w-100 w-sm-auto" data-bs-toggle="modal" data-bs-target="#quickTransactionModal">
                <i class="bi bi-plus-lg"></i> Novo Lançamento
            </button>
        </div>
    </div>

    <!-- Cards de Resumo Consolidado -->
    <div class="col-12 mb-4">
        <div class="row g-2 g-md-3">
            <div class="col-6 col-md">
                <div class="card bg-light border-0 shadow-sm h-100">
                    <div class="card-body py-2">
                        <small class="text-muted text-uppercase fw-bold d-block text-truncate" style="font-size: 0.65rem;">Saldo Inicial (Geral)</small>
                        <div class="h6 h5-md mb-0 fw-bold text-secondary text-truncate">R$ {{ number_format($globalOpeningBalance, 2, ',', '.') }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md">
                <div class="card border-0 shadow-sm border-start border-4 border-success h-100">
                    <div class="card-body py-2">
                        <small class="text-muted text-uppercase fw-bold d-block text-truncate" style="font-size: 0.65rem;">Total Entradas</small>
                        <div class="h6 h5-md mb-0 fw-bold text-success text-truncate">+ R$ {{ number_format($totalIncome, 2, ',', '.') }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md">
                <div class="card border-0 shadow-sm border-start border-4 border-danger h-100">
                    <div class="card-body py-2">
                        <small class="text-muted text-uppercase fw-bold d-block text-truncate" style="font-size: 0.65rem;">Total Saídas</small>
                        <div class="h6 h5-md mb-0 fw-bold text-danger text-truncate">- R$ {{ number_format($totalExpense, 2, ',', '.') }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body py-2">
                        <small class="text-muted text-uppercase fw-bold d-block text-truncate" style="font-size: 0.65rem;">Resultado Mensal</small>
                        <div class="h6 h5-md mb-0 fw-bold text-truncate {{ $monthlyResult >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ $monthlyResult >= 0 ? '+' : '' }} R$ {{ number_format($monthlyResult, 2, ',', '.') }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md mt-2 mt-md-0">
                <div class="card bg-primary text-white border-0 shadow-sm h-100">
                    <div class="card-body py-2">
                        <small class="text-white-50 text-uppercase fw-bold d-block text-truncate" style="font-size: 0.65rem;">Saldo Final Projetado</small>
                        <div class="h5 mb-0 fw-bold text-truncate">R$ {{ number_format($finalProjectedBalance, 2, ',', '.') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="col-12 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form action="{{ route('transactions.payable') }}" method="GET" class="row g-2 align-items-end">
                    <input type="hidden" name="month" value="{{ request('month', now()->month) }}">
                    <input type="hidden" name="year" value="{{ request('year', now()->year) }}">
                    
                    <div class="col-md-2">
                        <label class="form-label small fw-bold">De:</label>
                        <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold">Até:</label>
                        <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Descrição:</label>
                        <input type="text" name="description" class="form-control form-control-sm" value="{{ request('description') }}" placeholder="Buscar...">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold">Conta:</label>
                        <select name="account_id" class="form-select form-select-sm">
                            <option value="">Todas</option>
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}" {{ request('account_id') == $acc->id ? 'selected' : '' }}>{{ $acc->name }}</option>
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
                    <div class="col-12 col-md-1 d-flex gap-1 mt-3 mt-md-auto">
                        <button type="submit" class="btn btn-sm btn-primary flex-fill" title="Filtrar">
                            <i class="bi bi-filter"></i> Filtrar
                        </button>
                        <a href="{{ route('transactions.payable') }}" class="btn btn-sm btn-outline-secondary" title="Limpar">
                            <i class="bi bi-eraser-fill"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4 shadow-sm border-0">
    <div class="card-header bg-white py-3 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
        <h5 class="mb-0 text-primary fw-bold text-truncate"><i class="bi bi-hourglass-split me-2"></i> A Pagar / Receber <span class="badge bg-primary ms-2">{{ $transactions->where('status', 'pending')->count() }}</span></h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-nowrap">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3 ps-md-4">Data Ref.</th>
                        <th>Descrição</th>
                        <th class="d-none d-md-table-cell">Tipo</th>
                        <th class="d-none d-md-table-cell">Categoria</th>
                        <th class="d-none d-md-table-cell">Situação</th>
                        <th>Valor Previsto</th>
                        <th class="text-end pe-3 pe-md-4">Ação</th>
                    </tr>
                </thead>
                <tbody>
                    @php $pending = $transactions->where('status', 'pending'); @endphp
                    @forelse($pending as $t)
                    <tr>
                        <td class="ps-3 ps-md-4 {{ $t->date < \Carbon\Carbon::now() ? 'text-danger fw-bold' : '' }}">
                            <div class="d-flex flex-column">
                                <span>{{ $t->date->format('d/m/Y') }}</span>
                                <small class="d-md-none {{ $t->type == 'income' ? 'text-success' : 'text-danger' }}">
                                    {{ $t->type == 'income' ? 'A Receber' : 'A Pagar' }}
                                </small>
                            </div>
                        </td>
                        <td>
                            {{ $t->description }}
                            <div class="d-md-none text-muted small text-truncate" style="max-width: 120px;">
                                {{ $t->category ? $t->category->name : '-' }}
                            </div>
                        </td>
                        <td class="d-none d-md-table-cell">
                            @if($t->type == 'income') <i class="bi bi-arrow-up-circle-fill text-success"></i>
                            @else <i class="bi bi-arrow-down-circle-fill text-danger"></i> @endif
                        </td>
                        <td class="d-none d-md-table-cell">{{ $t->category ? $t->category->name : '-' }}</td>
                        <td class="d-none d-md-table-cell">
                            @if($t->type == 'income') <span class="badge bg-success-subtle text-success">A Receber</span>
                            @else <span class="badge bg-danger-subtle text-danger">A Pagar</span> @endif
                        </td>
                        <td class="fw-bold">
                            @if($t->type == 'income') <i class="bi bi-arrow-up-circle-fill text-success d-md-none me-1"></i>
                            @else <i class="bi bi-arrow-down-circle-fill text-danger d-md-none me-1"></i> @endif
                            R$ {{ number_format($t->amount, 2, ',', '.') }}
                        </td>
                        <td class="text-end pe-3 pe-md-4">
                            <div class="d-flex justify-content-end gap-1 gap-md-2">
                                @if($t->is_recurring)
                                <form action="{{ route('transactions.stop-recurrence', $t) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-danger shadow-sm confirm-action" 
                                            data-confirm-title="Parar Recorrência?" 
                                            data-confirm-text="Deseja realmente parar a recorrência e remover lançamentos futuros?"
                                            title="Parar de repetir">
                                        <i class="bi bi-calendar-x"></i>
                                    </button>
                                </form>
                                @endif

                                <a href="{{ route('transactions.edit', $t) }}" class="btn btn-sm btn-outline-secondary shadow-sm" title="Editar Lançamento">
                                    <i class="bi bi-pencil"></i>
                                </a>

                                <button type="button" class="btn btn-sm btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#payModal{{ $t->id }}">
                                    <i class="bi bi-check-circle"></i> {{ $t->type == 'income' ? 'Receber' : 'Pagar' }}
                                </button>
                            </div>
                        </td>
                    </tr>

                    <!-- Modal de Pagamento Analítico -->
                    <div class="modal fade" id="payModal{{ $t->id }}" tabindex="-1">
                      <div class="modal-dialog">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h5 class="modal-title">Confirmar: {{ $t->description }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                          </div>
                          <form action="{{ route('transactions.pay', $t) }}" method="POST">
                              @csrf
                              <div class="modal-body text-start">
                                  <p class="text-muted mb-3">Vencimento original: <strong>{{ $t->date->format('d/m/Y') }}</strong></p>
                                  
                                  <div class="mb-3">
                                      <label class="form-label">Valor Real Efetivado (R$)</label>
                                      <input type="number" step="0.01" name="final_amount" class="form-control" value="{{ $t->amount }}" required>
                                  </div>
                                  
                                  <div class="mb-3">
                                      <label class="form-label">{{ $t->type == 'income' ? 'Conta de Recebimento' : 'Conta de Pagamento' }}</label>
                                      <select name="account_id" class="form-select" required>
                                          @foreach($accounts as $acc)
                                              <option value="{{ $acc->id }}" {{ $t->account_id == $acc->id ? 'selected' : '' }}>{{ $acc->name }} (Saldo: R$ {{ number_format($acc->balance, 2, ',', '.') }})</option>
                                          @endforeach
                                      </select>
                                  </div>
                              </div>
                              <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                                <button type="submit" class="btn btn-success">{{ $t->type == 'income' ? 'Efetivar Recebimento' : 'Efetivar Pagamento' }}</button>
                              </div>
                          </form>
                        </div>
                      </div>
                    </div>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">Não há contas a pagar/receber para este período.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Seção de Faturas de Cartão -->
@if($invoices->count() > 0)
<div class="card mb-4 shadow-sm border-0 border-start border-4 border-warning">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0 text-dark fw-bold"><i class="bi bi-credit-card me-2 text-warning"></i> Faturas de Cartão do Mês</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Cartão</th>
                        <th>Referência</th>
                        <th>Vencimento Est.</th>
                        <th>Status</th>
                        <th>Valor Total</th>
                        <th class="text-end pe-4">Ação</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoices as $inv)
                    <tr>
                        <td class="ps-4 fw-bold">{{ $inv->creditCard->name }}</td>
                        <td>{{ $inv->reference_month }}</td>
                        <td>Dia {{ $inv->creditCard->due_day }}</td>
                        <td>
                            @if($inv->status == 'open') <span class="badge bg-info">Aberta</span>
                            @elseif($inv->status == 'closed') <span class="badge bg-warning text-dark">Fechada</span>
                            @else <span class="badge bg-success">Paga</span> @endif
                        </td>
                        <td class="fw-bold text-danger">R$ {{ number_format($inv->total_amount, 2, ',', '.') }}</td>
                        <td class="text-end pe-4">
                            @if($inv->status != 'paid')
                            <button type="button" class="btn btn-sm btn-warning shadow-sm" data-bs-toggle="modal" data-bs-target="#payInvoiceModal{{ $inv->id }}">
                                <i class="bi bi-wallet2"></i> Pagar Fatura
                            </button>
                            @else
                            <span class="text-success small fw-bold"><i class="bi bi-check-all"></i> Efetivada</span>
                            @endif
                        </td>
                    </tr>

                    <!-- Modal Pagamento de Fatura -->
                    <div class="modal fade" id="payInvoiceModal{{ $inv->id }}" tabindex="-1">
                      <div class="modal-dialog">
                        <div class="modal-content text-start">
                          <div class="modal-header">
                            <h5 class="modal-title">Pagar Fatura: {{ $inv->creditCard->name }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                          </div>
                          <form action="{{ route('credit-cards.pay-invoice', $inv) }}" method="POST">
                              @csrf
                              <div class="modal-body">
                                  <p>Valor total da fatura: <strong class="text-danger">R$ {{ number_format($inv->total_amount, 2, ',', '.') }}</strong></p>
                                  <div class="mb-3">
                                      <label class="form-label">Pagar usando a conta:</label>
                                      <select name="account_id" class="form-select" required>
                                          @foreach($accounts as $acc)
                                              <option value="{{ $acc->id }}">{{ $acc->name }} (Saldo: R$ {{ number_format($acc->balance, 2, ',', '.') }})</option>
                                          @endforeach
                                      </select>
                                  </div>
                              </div>
                              <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-primary">Confirmar Pagamento</button>
                              </div>
                          </form>
                        </div>
                      </div>
                    </div>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

<!-- Seção de Adiantamentos Efetuados ESTE mês -->
@if($earlyPayments->count() > 0)
<div class="card mb-4 shadow-sm border-0 border-start border-4 border-info">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0 text-info fw-bold"><i class="bi bi-fast-forward-fill me-2"></i> Pagamentos Antecipados (Referentes ao Futuro)</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0 text-muted">
                <thead class="table-light text-dark">
                    <tr>
                        <th class="ps-4">Pago em</th>
                        <th>Referência Original</th>
                        <th>Descrição</th>
                        <th class="text-end pe-4">Valor</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($earlyPayments as $ep)
                    <tr>
                        <td class="ps-4">{{ $ep->payment_date->format('d/m/Y') }}</td>
                        <td>{{ $ep->date->format('m/Y') }}</td>
                        <td>{{ $ep->description }}</td>
                        <td class="text-end pe-4 fw-bold text-dark">R$ {{ number_format($ep->amount, 2, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center">
        <h5 class="mb-0 text-success fw-bold text-truncate"><i class="bi bi-check2-all me-2"></i> Efetivados do Mês</h5>
    </div>
    <div class="card-body p-0 text-muted">
        <div class="table-responsive">
            <table class="table align-middle mb-0 text-nowrap">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3 ps-md-4">Data Pagto</th>
                        <th class="d-none d-md-table-cell">Ref.</th>
                        <th>Descrição</th>
                        <th class="d-none d-md-table-cell">Tipo</th>
                        <th class="d-none d-sm-table-cell">Conta</th>
                        <th class="text-end pe-3 pe-md-4">Valor</th>
                    </tr>
                </thead>
                <tbody>
                    @php 
                        $paidThisRefMonth = $transactions->where('status', 'paid');
                    @endphp
                    @forelse($paidThisRefMonth as $t)
                    <tr style="opacity: 0.8;">
                        <td class="ps-3 ps-md-4">
                            <div class="d-flex flex-column">
                                <span>{{ $t->payment_date ? $t->payment_date->format('d/m/Y') : $t->date->format('d/m/Y') }}</span>
                                <small class="d-md-none text-muted">Ref: {{ $t->date->format('m/Y') }}</small>
                            </div>
                        </td>
                        <td class="d-none d-md-table-cell">{{ $t->date->format('m/Y') }}</td>
                        <td>
                            {{ $t->description }}
                            <div class="d-sm-none text-muted small text-truncate" style="max-width: 120px;">
                                {{ $t->account->name }}
                            </div>
                            @if($t->payment_date && $t->payment_date->format('Y-m') < $t->date->format('Y-m'))
                                <span class="badge bg-info-subtle text-info d-block d-md-inline-block mt-1 mt-md-0 d-md-inline">Anticipado: {{ $t->payment_date->format('d/m/Y') }}</span>
                            @endif
                        </td>
                        <td class="d-none d-md-table-cell">
                            @if($t->type == 'income') <i class="bi bi-arrow-up-circle text-success"></i>
                            @else <i class="bi bi-arrow-down-circle text-danger"></i> @endif
                        </td>
                        <td class="d-none d-sm-table-cell">{{ $t->account->name }}</td>
                        <td class="text-end pe-3 pe-md-4">
                            @if($t->type == 'income') <i class="bi bi-arrow-up-circle-fill text-success d-md-none me-1"></i>
                            @else <i class="bi bi-arrow-down-circle-fill text-danger d-md-none me-1"></i> @endif
                            R$ {{ number_format($t->amount, 2, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4">Nenhuma conta confirmada ainda para este mês.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Novo Lançamento Rápido -->
<div class="modal fade" id="quickTransactionModal" tabindex="-1" aria-labelledby="quickTransactionModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-primary text-white border-0">
        <h5 class="modal-title fw-bold" id="quickTransactionModalLabel">
            <i class="bi bi-plus-circle me-2"></i>Novo Lançamento - {{ ucfirst($currentMonthName) }}
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('transactions.store') }}" method="POST">
          @csrf
          <input type="hidden" name="redirect_to" value="{{ route('transactions.payable', ['month' => request('month', now()->month), 'year' => request('year', now()->year)]) }}">
          
          <div class="modal-body p-4">
              <div class="row g-3">
                  <!-- Bloco de Valor e Tipo -->
                  <div class="col-12">
                      <div class="card bg-light border-0">
                          <div class="card-body p-3">
                              <div class="row g-3">
                                  <div class="col-md-6">
                                      <label class="form-label fw-bold text-muted small">TIPO DE TRANSAÇÃO</label>
                                      <div class="d-flex gap-2">
                                          <input type="radio" class="btn-check" name="type" id="type_expense" value="expense" checked required>
                                          <label class="btn btn-outline-danger w-100 py-2" for="type_expense">
                                              <i class="bi bi-dash-circle me-1"></i> Despesa
                                          </label>

                                          <input type="radio" class="btn-check" name="type" id="type_income" value="income" required>
                                          <label class="btn btn-outline-success w-100 py-2" for="type_income">
                                              <i class="bi bi-plus-circle me-1"></i> Receita
                                          </label>
                                      </div>
                                  </div>
                                  <div class="col-md-6">
                                      <label class="form-label fw-bold text-muted small">VALOR (R$)</label>
                                      <div class="input-group input-group-lg">
                                          <span class="input-group-text bg-white border-end-0">R$</span>
                                          <input type="number" step="0.01" name="amount" class="form-control border-start-0 ps-0 fw-bold" placeholder="0,00" required>
                                      </div>
                                  </div>
                              </div>
                          </div>
                      </div>
                  </div>

                  <!-- Detalhes -->
                  <div class="col-12">
                      <label class="form-label fw-bold small text-muted">DESCRIÇÃO</label>
                      <input type="text" name="description" class="form-control form-control-lg" placeholder="Ex: Aluguel, Supermercado, Salário..." required>
                  </div>

                  <div class="col-md-6">
                      <label class="form-label fw-bold small text-muted">CONTA</label>
                      <select name="account_id" class="form-select form-select-lg" required>
                          <option value="">Selecione a conta...</option>
                          @foreach($accounts as $account)
                              <option value="{{ $account->id }}">{{ $account->name }}</option>
                          @endforeach
                      </select>
                  </div>
                  <div class="col-md-6">
                      <label class="form-label fw-bold small text-muted">CATEGORIA</label>
                      <select name="category_id" class="form-select form-select-lg">
                          <option value="">Selecione a categoria...</option>
                          @foreach($categories as $category)
                              <option value="{{ $category->id }}">{{ $category->name }}</option>
                          @endforeach
                      </select>
                  </div>

                  <!-- Data e Status -->
                  <div class="col-md-4">
                      <label class="form-label fw-bold small text-muted">DATA DO VENCIMENTO</label>
                      <input type="date" name="date" class="form-control form-control-lg" value="{{ now()->format('Y-m-d') }}" required>
                  </div>
                  <div class="col-md-4">
                      <label class="form-label fw-bold small text-muted">SITUAÇÃO</label>
                      <select name="status" class="form-select form-select-lg" required>
                          <option value="pending">Pendente (A Pagar/Receber)</option>
                          <option value="paid">Confirmado (Já Pago/Recebido)</option>
                      </select>
                  </div>

                  <!-- Recorrência -->
                  <div class="col-md-4 d-flex align-items-center mt-md-4">
                      <div class="form-check form-switch p-0 ms-0">
                          <div class="d-flex align-items-center gap-3 bg-light rounded p-2 px-3">
                            <input class="form-check-input ms-0" type="checkbox" name="is_recurring" value="1" id="isRecurringQuick" role="switch" style="width: 3em; height: 1.5em;">
                            <label class="form-check-label fw-bold pt-1" for="isRecurringQuick">Repetir mensalmente?</label>
                          </div>
                      </div>
                  </div>

                  <div class="col-12" id="repeatUntilContainerQuick" style="display: none;">
                      <div class="alert alert-info py-2 mb-0">
                          <div class="row align-items-center">
                              <div class="col-sm-8 mb-2 mb-sm-0">
                                  <i class="bi bi-info-circle me-1"></i> Até quando esta conta deve se repetir?
                              </div>
                              <div class="col-sm-4">
                                  <input type="date" name="repeat_until" class="form-control">
                              </div>
                          </div>
                      </div>
                  </div>
              </div>
          </div>
          <div class="modal-footer bg-light border-0 p-3">
            <button type="button" class="btn btn-outline-secondary px-4 py-2" data-bs-dismiss="modal">Fechar</button>
            <button type="submit" class="btn btn-primary px-5 py-2 fw-bold">
                <i class="bi bi-save me-2"></i>Salvar Lançamento
            </button>
          </div>
      </form>
    </div>
  </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const isRecurringQuick = document.getElementById('isRecurringQuick');
    const containerQuick = document.getElementById('repeatUntilContainerQuick');

    if (isRecurringQuick) {
        isRecurringQuick.addEventListener('change', function() {
            containerQuick.style.display = this.checked ? 'block' : 'none';
        });
    }
});
</script>
@endpush
@endsection
