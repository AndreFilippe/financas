@extends('layouts.app')

@section('title', "Lista: {$shoppingList->name}")

@section('actions')
    <div class="d-flex gap-2">
        <a href="{{ route('shopping-lists.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
        <form action="{{ route('shopping-lists.destroy', $shoppingList) }}" method="POST">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-sm btn-outline-danger confirm-action" 
                    data-confirm-title="Excluir Lista?" 
                    data-confirm-text="Esta ação removerá a lista e todos os seus itens permanentemente.">
                <i class="bi bi-trash"></i> Excluir
            </button>
        </form>
    </div>
@endsection

@section('content')
<!-- Header Estilizado (Mobile-First) -->
<div class="row mb-3 align-items-center">
    <div class="col-8">
        <h4 class="fw-bold mb-0 text-truncate">{{ $shoppingList->name }}</h4>
        @if($shoppingList->location)
            <small class="text-primary d-block mt-1"><i class="bi bi-geo-alt"></i> {{ $shoppingList->location }}</small>
        @else
            <small class="text-muted d-block mt-1">{{ $shoppingList->date->format('d/m/Y') }}</small>
        @endif
    </div>
    <div class="col-4 text-end">
        <span class="badge {{ $shoppingList->status == 'open' ? 'bg-primary' : 'bg-success' }} px-3 py-2 rounded-pill">
            {{ $shoppingList->status == 'open' ? 'Aberta' : 'Finalizada' }}
        </span>
    </div>
</div>

<!-- Barra de Resumo Fixa no Topo (Mobile) -->
<div class="sticky-top bg-white py-2 border-bottom shadow-sm mb-3" style="top: 0; z-index: 1020;">
    <div class="container-fluid px-2">
        <div class="row g-2 align-items-center">
            <div class="col-8">
                <div class="row g-0">
                    <div class="col-6">
                        <div class="small text-muted text-uppercase fw-bold ls-1" style="font-size: 0.65rem;">Estimado</div>
                        <div class="h6 fw-bold mb-0 text-muted" id="total-estimated-display">R$ {{ number_format($totalEstimated, 2, ',', '.') }}</div>
                    </div>
                    <div class="col-6">
                        <div class="small text-muted text-uppercase fw-bold ls-1" style="font-size: 0.65rem;">Gasto Real</div>
                        <div class="h6 fw-bold mb-0 text-primary" id="total-actual-display">R$ {{ number_format($totalActual, 2, ',', '.') }}</div>
                    </div>
                </div>
            </div>
            <div class="col-4 text-end">
                @if($shoppingList->status == 'open')
                <button type="button" class="btn btn-success btn-sm px-3 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#finishModal">
                    <i class="bi bi-check-lg"></i> Finalizar
                </button>
                @else
                <a href="{{ route('shopping-lists.copy', $shoppingList) }}" class="btn btn-outline-info btn-sm px-3 fw-bold">
                    <i class="bi bi-copy"></i> Duplicar
                </a>
                @endif
            </div>
        </div>
        
        @if($shoppingList->status == 'open')
        <!-- Linha de Cadastro Rápido (Sempre Visível) -->
        <form action="{{ route('shopping-lists.items.add', $shoppingList) }}" method="POST" class="mt-2 pt-2 border-top">
            @csrf
            <div class="row g-1">
                <div class="col-8 col-md-8">
                    <input type="text" name="name" class="form-control form-control-sm border-primary-subtle" placeholder="Item..." required>
                </div>
                <div class="col-4 col-md-1">
                    <input type="number" step="0.001" name="quantity" class="form-control form-control-sm border-primary-subtle text-center px-1" value="1" title="Qtd" required>
                </div>
                <div class="col-6 col-md-2">
                    <input type="number" step="0.01" name="estimated_unit_price" class="form-control form-control-sm border-primary-subtle text-end px-1" placeholder="R$..." title="Preço" required>
                </div>
                <div class="col-6 col-md-1">
                    <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-plus-lg"></i></button>
                </div>
            </div>
        </form>
        <!-- Campo de Busca -->
        <div class="mt-2">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" id="search-item" class="form-control border-start-0 ps-0" placeholder="Buscar item na lista...">
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Listagem de Itens (Layout Mobile-First / Desktop Grid) -->
<div class="row g-2 mb-5">
    @forelse($shoppingList->items as $item)
    <div class="col-12 col-md-6 col-lg-4">
        <div class="item-card card border-0 shadow-sm h-100 {{ $item->is_checked ? 'checked-card bg-light-subtle' : '' }}" 
             id="row-{{ $item->id }}"
             data-quantity="{{ $item->quantity }}" 
             data-actual-price="{{ $item->actual_unit_price }}"
             data-estimated-price="{{ $item->estimated_unit_price }}"
             class="item-row">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="status-icon me-2">
                        @if($item->is_checked)
                            <i class="bi bi-check-circle-fill text-success fs-3"></i>
                        @else
                            <i class="bi bi-circle text-muted fs-3"></i>
                        @endif
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-bold fs-5 item-name {{ $item->is_checked ? 'text-decoration-line-through text-muted' : '' }}">
                            {{ $item->name }}
                        </div>
                        <div class="d-flex align-items-center gap-1 mb-1">
                            <select class="form-select form-select-sm category-select p-0 px-1 border-0 bg-light text-muted" 
                                    style="font-size: 0.65rem; width: auto;" 
                                    data-id="{{ $item->id }}">
                                <option value="">Sem Categoria</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ $item->category_id == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <small class="text-muted subtotal-label" id="subtotal-item-{{ $item->id }}">
                            R$ {{ number_format($item->actual_unit_price, 2, ',', '.') }} x {{ (float)$item->quantity }} = 
                            <strong>R$ {{ number_format($item->actual_unit_price * $item->quantity, 2, ',', '.') }}</strong>
                        </small>
                    </div>
                    @if($shoppingList->status == 'open')
                    <form action="{{ route('shopping-list-items.delete', $item) }}" method="POST">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-link link-danger p-0 ms-2 confirm-action" data-confirm-title="Remover Item?" data-confirm-text="Deseja realmente remover '{{ $item->name }}' da lista?">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </form>
                    @endif
                </div>

                <div class="row g-2 align-items-end mt-2">
                    <div class="col-6">
                        <label class="small text-muted d-block mb-1">Qtd</label>
                        <input type="number" step="0.001" 
                               class="form-control form-control-sm text-center quantity-input" 
                               value="{{ (float)$item->quantity }}" 
                               data-id="{{ $item->id }}"
                               {{ $shoppingList->status == 'closed' ? 'disabled' : '' }}>
                    </div>
                    <div class="col-6">
                        <label class="small text-muted d-block mb-1">Preço Real</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text px-1 bg-white border-end-0">R$</span>
                            <input type="number" step="0.01" 
                                   class="form-control form-control-sm actual-price-input border-start-0 ps-0" 
                                   value="{{ $item->actual_unit_price }}" 
                                   data-id="{{ $item->id }}"
                                   {{ $shoppingList->status == 'closed' ? 'disabled' : '' }}>
                        </div>
                    </div>
                    <div class="col-12">
                        @if($shoppingList->status == 'open')
                        <button type="button" 
                                class="btn btn-confirm w-100 fw-bold {{ $item->is_checked ? 'btn-success' : 'btn-primary' }} btn-sm py-2 shadow-sm"
                                data-id="{{ $item->id }}"
                                data-checked="{{ $item->is_checked ? '1' : '0' }}">
                            {{ $item->is_checked ? 'OK' : 'Comprar' }}
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12 py-5 text-center text-muted">A lista está vazia. Comece a adicionar itens acima.</div>
    @endforelse
</div>

<!-- Resumo Flutuante (Mobile) -->
@if(($totalEstimated - $totalActual) > 0 && $totalActual > 0)
<div class="fixed-bottom p-3 d-md-none" style="z-index: 1000; bottom: 0;">
    <div class="alert alert-success shadow-lg mb-0 py-2 d-flex justify-content-between align-items-center rounded-pill">
        <span class="small fw-bold"><i class="bi bi-graph-down-arrow"></i> Economia:</span>
        <span class="fw-bold" id="economy-value">R$ {{ number_format($totalEstimated - $totalActual, 2, ',', '.') }}</span>
    </div>
</div>
@endif

<!-- Modal Finalizar (Design Melhorado) -->
<div class="modal fade" id="finishModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold">Fechar Compra</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('shopping-lists.finish', $shoppingList) }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="text-center mb-4">
                        <div class="display-6 fw-bold text-success">R$ {{ number_format($totalActual, 2, ',', '.') }}</div>
                        <div class="text-muted small">Valor total da compra</div>
                    </div>

                    <div class="card bg-light border-0 mb-4">
                        <div class="card-body p-3">
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" id="createTransaction" name="create_transaction" value="1" checked>
                                <label class="form-check-label fw-bold" for="createTransaction">Lançar no Financeiro (Extrato)</label>
                                <p class="text-muted small mb-0">Registrar automaticamente como despesa de Mercado.</p>
                            </div>
                        </div>
                    </div>

                    <div id="financial-fields">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Data do Pagamento</label>
                            <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>

                        <div class="card bg-info-subtle border-0 mb-4">
                            <div class="card-body p-3">
                                <label class="form-label fw-bold mb-1"><i class="bi bi-credit-card"></i> Pagamento no Cartão (Opcional)</label>
                                <p class="text-muted small mb-2">Este valor NÃO será registrado agora (evita duplicidade com o CSV).</p>
                                <div class="input-group">
                                    <span class="input-group-text">R$</span>
                                    <input type="number" step="0.01" name="credit_card_amount" id="credit_card_amount" class="form-control" value="0.00" min="0">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Pagamento via Contas (Extrato)</label>
                            <div id="payments-container">
                                <!-- Primeiro meio de pagamento (padrão) -->
                                <div class="payment-row row g-2 mb-2">
                                    <div class="col-7">
                                        <select name="payments[0][account_id]" class="form-select select-account">
                                            @foreach($accounts as $acc)
                                                <option value="{{ $acc->id }}">{{ $acc->name }} (R$ {{ number_format($acc->balance, 2, ',', '.') }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-5">
                                        <div class="input-group">
                                            <span class="input-group-text px-1">R$</span>
                                            <input type="number" step="0.01" name="payments[0][amount]" class="form-control payment-amount" value="{{ $totalActual }}" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="add-payment-btn">
                                <i class="bi bi-plus-circle"></i> Dividir Pagamento
                            </button>
                        </div>
                        
                        <div class="alert alert-warning d-none" id="payment-sum-alert">
                            <i class="bi bi-exclamation-triangle"></i> Suma dos pagamentos: <strong id="current-sum">R$ 0,00</strong>. Deve ser exatamente <strong id="target-total">R$ {{ number_format($totalActual, 2, ',', '.') }}</strong>.
                        </div>

                        <input type="hidden" name="category_id" value="{{ $categories->where('name', 'Mercado')->first()->id ?? $categories->first()->id }}">
                    </div>

                    <!-- Novo campo de Motivo (Aparece quando Desabilitado) -->
                    <div id="reason-fields" class="d-none">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Por que não lançar no extrato? <span class="text-danger">*</span></label>
                            <textarea name="closing_reason" id="closing_reason" class="form-control" rows="3" placeholder="Ex: Pago com dinheiro em espécie, Já lançado manualmente, Presente..."></textarea>
                            <div class="invalid-feedback">O motivo é obrigatório para fechar sem lançar no extrato.</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="submit" id="confirm-finish-btn" class="btn btn-success btn-lg w-100 fw-bold shadow">Confirmar e Finalizar</button>
                    <button type="button" class="btn btn-link text-muted w-100" data-bs-dismiss="modal">Fechar sem salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function() {
    const csrftoken = '{{ csrf_token() }}';
    const fmt = (val) => new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(val);

    // Toggle financeiro vs Motivo
    const transactionSwitch = document.getElementById('createTransaction');
    const financialFields = document.getElementById('financial-fields');
    const reasonFields = document.getElementById('reason-fields');
    const closingReasonInput = document.getElementById('closing_reason');

    if(transactionSwitch) {
        transactionSwitch.addEventListener('change', function() {
            if (this.checked) {
                financialFields.classList.remove('d-none');
                financialFields.style.display = 'block';
                reasonFields.classList.add('d-none');
                closingReasonInput.required = false;
            } else {
                financialFields.classList.add('d-none');
                financialFields.style.display = 'none';
                reasonFields.classList.remove('d-none');
                closingReasonInput.required = true;
            }
            calculatePaymentSum();
        });
    }

    // Lógica de Múltiplos Pagamentos no Modal
    const paymentsContainer = document.getElementById('payments-container');
    const addPaymentBtn = document.getElementById('add-payment-btn');
    const confirmFinishBtn = document.getElementById('confirm-finish-btn');
    const paymentSumAlert = document.getElementById('payment-sum-alert');
    const currentSumDisplay = document.getElementById('current-sum');
    const targetTotalDisplay = document.getElementById('target-total');

    let paymentIndex = 1;

    function calculatePaymentSum() {
        let total = 0;
        document.querySelectorAll('.payment-amount').forEach(input => {
            total += parseFloat(input.value) || 0;
        });

        const ccAmount = parseFloat(document.getElementById('credit_card_amount').value) || 0;
        total += ccAmount;

        // Obter total real da lista (renderizado nos datasets)
        let shoppingTotal = 0;
        document.querySelectorAll('.item-card').forEach(card => {
            shoppingTotal += (parseFloat(card.dataset.quantity) || 0) * (parseFloat(card.dataset.actualPrice) || 0);
        });

        const diff = Math.abs(total - shoppingTotal);
        
        if (currentSumDisplay) currentSumDisplay.innerText = fmt(total);
        if (targetTotalDisplay) targetTotalDisplay.innerText = fmt(shoppingTotal);

        if (transactionSwitch && transactionSwitch.checked) {
            if (diff > 0.01) {
                if (paymentSumAlert) paymentSumAlert.classList.remove('d-none');
                if (confirmFinishBtn) confirmFinishBtn.disabled = true;
            } else {
                if (paymentSumAlert) paymentSumAlert.classList.add('d-none');
                if (confirmFinishBtn) confirmFinishBtn.disabled = false;
            }
        } else {
            if (paymentSumAlert) paymentSumAlert.classList.add('d-none');
            // Validar se tem motivo
            if (confirmFinishBtn && closingReasonInput) {
                confirmFinishBtn.disabled = closingReasonInput.value.length < 5;
            }
        }

        return { current: total, target: shoppingTotal };
    }

    if (closingReasonInput) {
        closingReasonInput.addEventListener('input', calculatePaymentSum);
    }

    if (addPaymentBtn) {
        addPaymentBtn.addEventListener('click', function() {
            const { current, target } = calculatePaymentSum();
            const remaining = Math.max(0, target - current);

            const row = document.createElement('div');
            row.className = 'payment-row row g-2 mb-2';
            row.innerHTML = `
                <div class="col-7">
                    <select name="payments[${paymentIndex}][account_id]" class="form-select select-account">
                        ${document.querySelector('.select-account').innerHTML}
                    </select>
                </div>
                <div class="col-4">
                    <div class="input-group">
                        <span class="input-group-text px-1">R$</span>
                        <input type="number" step="0.01" name="payments[${paymentIndex}][amount]" class="form-control payment-amount" value="${remaining.toFixed(2)}" required>
                    </div>
                </div>
                <div class="col-1">
                    <button type="button" class="btn btn-link link-danger p-0 pt-1 remove-payment-btn"><i class="bi bi-dash-circle fs-5"></i></button>
                </div>
            `;
            paymentsContainer.appendChild(row);
            paymentIndex++;
            calculatePaymentSum();

            // Event listener para remover
            row.querySelector('.remove-payment-btn').addEventListener('click', function() {
                row.remove();
                calculatePaymentSum();
            });

            // Event listener para o novo input de valor
            row.querySelector('.payment-amount').addEventListener('input', calculatePaymentSum);
        });
    }

    // Event listener para o primeiro input de valor
    document.querySelector('.payment-amount').addEventListener('input', calculatePaymentSum);
    
    // Event listener para o campo do cartão
    document.getElementById('credit_card_amount').addEventListener('input', calculatePaymentSum);

    // Recalcular quando o modal abrir para garantir valores frescos
    const finishModal = document.getElementById('finishModal');
    if (finishModal) {
        finishModal.addEventListener('shown.bs.modal', calculatePaymentSum);
    }

    function updateTotals() {
        let totalEstimated = 0;
        let totalActual = 0;

        document.querySelectorAll('.item-card').forEach(card => {
            const qty = parseFloat(card.dataset.quantity) || 0;
            const est = parseFloat(card.dataset.estimatedPrice) || 0;
            const act = parseFloat(card.dataset.actualPrice) || 0;

            totalEstimated += qty * est;
            totalActual += qty * act;

            // Atualizar o label de subtotal individual do card
            const subtotalLabel = card.querySelector('.subtotal-label');
            if (subtotalLabel) {
                const subAmt = qty * act;
                subtotalLabel.innerHTML = `${fmt(act)} x ${qty} = <strong>${fmt(subAmt)}</strong>`;
            }
        });

        const totalActualDisplay = document.getElementById('total-actual-display');
        const totalEstimatedDisplay = document.getElementById('total-estimated-display');

        if (totalEstimatedDisplay) totalEstimatedDisplay.innerText = fmt(totalEstimated);
        if (totalActualDisplay) totalActualDisplay.innerText = fmt(totalActual);

        const economy = totalEstimated - totalActual;
        const economyValue = document.getElementById('economy-value');
        if (economyValue) {
            economyValue.innerText = fmt(economy > 0 ? economy : 0);
        }
    }

    function updateItemServer(itemId, data, elementsToEnable = []) {
        const card = document.getElementById(`row-${itemId}`);
        if (!card) return;

        if (data.quantity !== undefined) card.dataset.quantity = data.quantity;
        if (data.actual_unit_price !== undefined) card.dataset.actualPrice = data.actual_unit_price;
        
        updateTotals();

        fetch(`/shopping-list-items/${itemId}`, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrftoken },
            body: JSON.stringify(data)
        })
        .then(response => {
            if (!response.ok) throw new Error('Falha na resposta do servidor');
            return response.json();
        })
        .then(res => {
            if(res.success) elementsToEnable.forEach(el => el.disabled = false);
        })
        .catch(err => {
            console.error("Erro ao atualizar item:", err);
            Toast.fire({ icon: 'error', title: 'Erro ao salvar alteração.' });
            elementsToEnable.forEach(el => el.disabled = false);
        });
    }

    // Events
    document.querySelectorAll('.btn-confirm').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const card = document.getElementById(`row-${id}`);
            const priceInput = card.querySelector('.actual-price-input');
            const price = parseFloat(priceInput.value) || 0;

            if (price <= 0 && this.dataset.checked === '0') {
                Toast.fire({ icon: 'warning', title: 'O preço real deve ser maior que 0!' });
                priceInput.focus();
                return;
            }

            const isChecked = this.dataset.checked === '1';
            const newStatus = !isChecked;
            this.dataset.checked = newStatus ? '1' : '0';
            this.disabled = true;

            updateItemServer(id, { is_checked: newStatus }, [this]);
            
            // card já declarado acima
            const iconContainer = card.querySelector('.status-icon');
            const nameContainer = card.querySelector('.item-name');

            if (newStatus) {
                card.classList.add('checked-card', 'bg-light-subtle');
                nameContainer.classList.add('text-decoration-line-through', 'text-muted');
                iconContainer.innerHTML = '<i class="bi bi-check-circle-fill text-success fs-3"></i>';
                this.className = 'btn btn-confirm w-100 fw-bold btn-success btn-sm py-2 shadow-sm';
                this.innerText = 'OK';
            } else {
                card.classList.remove('checked-card', 'bg-light-subtle');
                nameContainer.classList.remove('text-decoration-line-through', 'text-muted');
                iconContainer.innerHTML = '<i class="bi bi-circle text-muted fs-3"></i>';
                this.className = 'btn btn-confirm w-100 fw-bold btn-primary btn-sm py-2 shadow-sm';
                this.innerText = 'Comprar';
            }
        });
    });

    // Busca de Itens
    const searchInput = document.getElementById('search-item');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const term = this.value.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
            document.querySelectorAll('.col-12.col-md-6.col-lg-4').forEach(col => {
                const name = col.querySelector('.item-name').innerText.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
                if (name.includes(term)) {
                    col.classList.remove('d-none');
                } else {
                    col.classList.add('d-none');
                }
            });
        });
    }

    document.querySelectorAll('.category-select').forEach(select => {
        select.addEventListener('change', function() {
            updateItemServer(this.dataset.id, { category_id: this.value });
        });
    });

    document.querySelectorAll('.actual-price-input').forEach(input => {
        input.addEventListener('change', function() {
            updateItemServer(this.dataset.id, { actual_unit_price: this.value });
        });
    });

    document.querySelectorAll('.quantity-input').forEach(input => {
        input.addEventListener('change', function() {
            updateItemServer(this.dataset.id, { quantity: this.value });
        });
    });
});
</script>
<style>
    .ls-1 { letter-spacing: 1px; }
    .item-card { transition: all 0.2s; border-left: 4px solid #0d6efd !important; }
    .checked-card { border-left-color: #198754 !important; opacity: 0.85; }
    .sticky-top { transition: top 0.3s; }
    @media (max-width: 576px) {
        .h4 { font-size: 1.1rem; }
        .h5 { font-size: 1rem; }
    }
</style>
@endpush
