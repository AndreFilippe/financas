@extends('layouts.app')

@section('title', "Cartão {$creditCard->name}")
@section('page_title', "Faturas do Cartão {$creditCard->name}")

@section('actions')
    <button type="button" class="btn btn-sm btn-outline-success shadow-sm me-2" data-bs-toggle="modal" data-bs-target="#importCsvModal">
        <i class="bi bi-file-earmark-arrow-up"></i> Importar CSV Nubank
    </button>
    <button type="button" class="btn btn-sm btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#newPurchaseModal">
        <i class="bi bi-plus-lg"></i> Lançar Compra
    </button>
    <a href="{{ route('credit-cards.edit', $creditCard) }}" class="btn btn-sm btn-outline-primary" title="Editar Configurações">
        <i class="bi bi-gear"></i> Editar
    </a>
    <a href="{{ route('credit-cards.index') }}" class="btn btn-sm btn-outline-secondary">
        Voltar
    </a>
@endsection

@section('content')

@foreach($creditCard->invoices as $invoice)
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0">Fatura: {{ $invoice->reference_month }}</h5>
            <span class="badge {{ $invoice->status == 'open' ? 'bg-primary' : ($invoice->status == 'paid' ? 'bg-success' : 'bg-secondary') }}">
                {{ strtoupper($invoice->status) }}
            </span>
        </div>
        <div class="d-flex align-items-center">
            <h5 class="mb-0 me-3">R$ {{ number_format($invoice->total_amount, 2, ',', '.') }}</h5>
            
            <div class="btn-group">
                @if($invoice->status === 'open')
                <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#editInvoiceModal{{ $invoice->id }}" title="Editar Manualmente">
                    <i class="bi bi-pencil"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger" title="Excluir Fatura" data-bs-toggle="modal" data-bs-target="#deleteInvoiceModal{{ $invoice->id }}">
                    <i class="bi bi-trash"></i>
                </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Modal Confirmar Exclusão -->
    <div class="modal fade" id="deleteInvoiceModal{{ $invoice->id }}" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header bg-danger text-white">
            <h5 class="modal-title"><i class="bi bi-exclamation-triangle"></i> Confirmar Exclusão</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <p>Você tem certeza que deseja excluir a fatura de <strong>{{ $invoice->reference_month }}</strong>?</p>
            <p class="text-danger fw-bold"><i class="bi bi-info-circle"></i> Esta ação é irreversível e excluirá todas as transações vinculadas a esta fatura.</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <form action="{{ route('credit-cards.destroy-invoice', $invoice) }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">Sim, Excluir Fatura</button>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Editar Fatura -->
    <div class="modal fade" id="editInvoiceModal{{ $invoice->id }}" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content text-start">
          <div class="modal-header">
            <h5 class="modal-title">Editar Fatura: {{ $invoice->reference_month }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <form action="{{ route('credit-cards.update-invoice', $invoice) }}" method="POST">
              @csrf
              @method('PUT')
              <div class="modal-body">
                  <div class="mb-3">
                      <label class="form-label fw-bold">Mês de Referência</label>
                      <input type="month" name="reference_month" class="form-control" value="{{ $invoice->reference_month }}" required>
                  </div>
                  <div class="mb-3">
                      <label class="form-label fw-bold">Valor Total (Manual)</label>
                      <div class="input-group">
                          <span class="input-group-text">R$</span>
                          <input type="number" step="0.01" name="total_amount" class="form-control" value="{{ $invoice->total_amount }}" required>
                      </div>
                      <small class="text-muted">Atenção: Mudar este valor não altera as transações individuais abaixo.</small>
                  </div>
                  <div class="mb-3">
                      <label class="form-label fw-bold">Status</label>
                      <select name="status" class="form-select" required>
                          <option value="open" {{ $invoice->status == 'open' ? 'selected' : '' }}>Aberto</option>
                          <option value="closed" {{ $invoice->status == 'closed' ? 'selected' : '' }}>Fechado</option>
                          <option value="paid" {{ $invoice->status == 'paid' ? 'selected' : '' }}>Pago</option>
                      </select>
                  </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Salvar Alterações</button>
              </div>
          </form>
        </div>
      </div>
    </div>
    <div class="card-body">
        <table class="table table-sm">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Descrição</th>
                    <th>Parcela</th>
                    <th class="text-end">Valor</th>
                    <th class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoice->transactions as $t)
                <tr>
                    <td>{{ $t->date->format('d/m/Y') }}</td>
                    <td>{{ $t->description }}</td>
                    <td>{{ $t->current_installment }}/{{ $t->installments }}</td>
                    <td class="text-end">R$ {{ number_format($t->amount, 2, ',', '.') }}</td>
                    <td class="text-end">
                        @if($invoice->status === 'open')
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-link text-info p-0 me-2" data-bs-toggle="modal" data-bs-target="#editTransactionModal{{ $t->id }}" title="Editar Item">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-link text-danger p-0" data-bs-toggle="modal" data-bs-target="#deleteTransactionModal{{ $t->id }}" title="Excluir Item">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                        @else
                        <span class="text-muted"><i class="bi bi-lock-fill"></i></span>
                        @endif
                    </td>
                </tr>

                <!-- Modal Editar Transação -->
                <div class="modal fade" id="editTransactionModal{{ $t->id }}" tabindex="-1">
                  <div class="modal-dialog">
                    <div class="modal-content text-start">
                      <div class="modal-header">
                        <h5 class="modal-title">Editar Item: {{ $t->description }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                      </div>
                      <form action="{{ route('credit-card-transactions.update', $t) }}" method="POST">
                          @csrf
                          @method('PUT')
                          <div class="modal-body">
                              <div class="mb-3">
                                  <label class="form-label fw-bold">Data</label>
                                  <input type="date" name="date" class="form-control" value="{{ $t->date->format('Y-m-d') }}" required>
                              </div>
                              <div class="mb-3">
                                  <label class="form-label fw-bold">Descrição</label>
                                  <input type="text" name="description" class="form-control" value="{{ $t->description }}" required>
                              </div>
                              <div class="mb-3">
                                  <label class="form-label fw-bold">Categoria</label>
                                  <select name="category_id" class="form-select">
                                      <option value="">Sem Categoria</option>
                                      @foreach($categories as $cat)
                                      <option value="{{ $cat->id }}" {{ $t->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                      @endforeach
                                  </select>
                              </div>
                              <div class="mb-3">
                                  <label class="form-label fw-bold">Valor</label>
                                  <div class="input-group">
                                      <span class="input-group-text">R$</span>
                                      <input type="number" step="0.01" name="amount" class="form-control" value="{{ $t->amount }}" required>
                                  </div>
                              </div>
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                          </div>
                      </form>
                    </div>
                  </div>
                </div>

                <!-- Modal Confirmar Exclusão Transação -->
                <div class="modal fade" id="deleteTransactionModal{{ $t->id }}" tabindex="-1">
                  <div class="modal-dialog">
                    <div class="modal-content text-start">
                      <div class="modal-header bg-warning">
                        <h5 class="modal-title"><i class="bi bi-exclamation-triangle"></i> Remover Item</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                      </div>
                      <div class="modal-body text-center">
                        <p>Deseja remover <strong>{{ $t->description }}</strong> da fatura?</p>
                        <p class="text-muted small">O valor total da fatura será atualizado automaticamente.</p>
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <form action="{{ route('credit-card-transactions.destroy', $t) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Sim, Remover</button>
                        </form>
                      </div>
                    </div>
                  </div>
                </div>
                @empty
                <tr><td colspan="5" class="text-center">Nenhum lançamento.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endforeach

<!-- Modal Nova Compra -->
<div class="modal fade" id="newPurchaseModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Nova Compra - {{ $creditCard->name }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('credit-cards.transactions.store', $creditCard) }}" method="POST">
          @csrf
          <div class="modal-body">
              <div class="mb-3">
                  <label class="form-label">Data da Compra</label>
                  <input type="date" name="date" class="form-control" required value="{{ date('Y-m-d') }}">
              </div>
              <div class="mb-3">
                  <label class="form-label">Descrição</label>
                  <input type="text" name="description" class="form-control" required>
              </div>
              <div class="mb-3">
                  <label class="form-label">Categoria (Opcional)</label>
                  <select name="category_id" class="form-select">
                      <option value="">Selecione...</option>
                      @foreach($categories as $cat)
                      <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                      @endforeach
                  </select>
              </div>
              <div class="row mb-3">
                  <div class="col-6">
                      <label class="form-label">Valor Total</label>
                      <input type="number" step="0.01" name="amount" class="form-control" required>
                  </div>
                  <div class="col-6">
                      <label class="form-label">Parcelas</label>
                      <input type="number" name="installments" class="form-control" value="1" min="1" required>
                  </div>
              </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary">Lançar no Cartão</button>
          </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Importar CSV -->
<div class="modal fade" id="importCsvModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content text-start">
      <div class="modal-header">
        <h5 class="modal-title">Importar Fatura (CSV)</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form action="{{ route('credit-cards.import', $creditCard) }}" method="POST" enctype="multipart/form-data">
          @csrf
          <div class="modal-body">
              <p class="text-muted small">Suporta o formato padrão do Nubank: <code>date,title,amount</code>.</p>
              <div class="mb-3">
                  <label class="form-label fw-bold small">Mês da Fatura (Opcional)</label>
                  <input type="month" name="reference_month" class="form-control" value="{{ now()->format('Y-m') }}">
                  <small class="text-muted d-block mt-1">Se não selecionado, o sistema usará o dia de fechamento do cartão.</small>
              </div>
              <div class="mb-3">
                  <label class="form-label fw-bold small">Arquivo .csv (Nubank)</label>
                  <input type="file" name="csv_file" class="form-control" accept=".csv" required>
              </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-success">Processar Arquivo</button>
          </div>
      </form>
    </div>
  </div>
</div>
@endsection
