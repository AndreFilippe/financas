<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Account;
use App\Models\Category;
use App\Services\TransactionService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    private TransactionService $service;

    public function __construct(TransactionService $service) 
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $query = Transaction::with(['account', 'category']);

        // Filtro por Período
        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }

        // Se não houver data, podemos padronizar para o mês atual (opcional, mas o usuário pediu filtrar por mês)
        if (!$request->filled('date_from') && !$request->filled('date_to')) {
            $month = $request->get('month', Carbon::now()->month);
            $year = $request->get('year', Carbon::now()->year);
            $query->whereMonth('date', $month)->whereYear('date', $year);
        }

        // Filtro por Texto
        if ($request->filled('description')) {
            $query->where('description', 'like', '%' . $request->description . '%');
        }

        // Filtro por Categoria e Conta
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('account_id')) {
            $query->where('account_id', $request->account_id);
        }

        $transactions = $query->orderBy('date', 'desc')->get();
        $accounts = Account::all();
        $categories = Category::all();

        return view('transactions.index', compact('transactions', 'accounts', 'categories'));
    }

    public function create()
    {
        $accounts = Account::all();
        $categories = Category::all();
        return view('transactions.create', compact('accounts', 'categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'category_id' => 'nullable|exists:categories,id',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'type' => 'required|in:income,expense',
            'date' => 'required|date',
            'status' => 'required|in:paid,pending',
            'repeat_until' => 'nullable|date|after_or_equal:date',
        ]);

        $validated['is_recurring'] = $request->has('is_recurring');

        $this->service->createTransaction($validated);

        if ($request->filled('redirect_to')) {
            return redirect($request->redirect_to)->with('success', 'Transação incluída com sucesso.');
        }

        return redirect()->route('transactions.index')->with('success', 'Transação incluída com sucesso.');
    }

    public function edit(Transaction $transaction)
    {
        $accounts = Account::all();
        $categories = Category::all();
        return view('transactions.edit', compact('transaction', 'accounts', 'categories'));
    }

    public function update(Request $request, Transaction $transaction)
    {
        $validated = $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'category_id' => 'nullable|exists:categories,id',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'type' => 'required|in:income,expense',
            'date' => 'required|date',
            'status' => 'required|in:paid,pending',
            'repeat_until' => 'nullable|date|after_or_equal:date',
        ]);

        $validated['is_recurring'] = $request->has('is_recurring');

        $this->service->updateTransaction($transaction, $validated);
        return redirect()->route('transactions.index')->with('success', 'Transação atualizada com sucesso.');
    }

    public function destroy(Transaction $transaction)
    {
        $this->service->deleteTransaction($transaction);
        return redirect()->route('transactions.index')->with('success', 'Transação removida com sucesso.');
    }

    /**
     * Atualização rápida de categoria para itens do cartão
     */
    public function updateCardCategory(Request $request, \App\Models\CreditCardTransaction $transaction)
    {
        $request->validate([
            'category_id' => 'nullable|exists:categories,id'
        ]);

        $transaction->update([
            'category_id' => $request->category_id
        ]);

        return response()->json(['success' => true]);
    }

    public function payable(Request $request)
    {
        $month = $request->get('month', Carbon::now()->month);
        $year = $request->get('year', Carbon::now()->year);

        $date = Carbon::createFromDate($year, $month, 1);
        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();

        // Se houver datas específicas, o período muda
        if ($request->filled('date_from')) {
            $startOfMonth = Carbon::parse($request->date_from)->startOfDay();
        }
        if ($request->filled('date_to')) {
            $endOfMonth = Carbon::parse($request->date_to)->endOfDay();
        }

        $refMonthStr = $date->format('Y-m');

        // 1. Cálculo do Saldo Inicial Global
        $globalCurrentBalance = Account::sum('balance');
        $futureMovements = Transaction::where('status', 'paid')
            ->where('payment_date', '>=', $startOfMonth)
            ->get();
        
        $globalOpeningBalance = $globalCurrentBalance 
            - $futureMovements->where('type', 'income')->sum('amount') 
            + $futureMovements->where('type', 'expense')->sum('amount');

        // 2. Transações de Referência
        $query = Transaction::with(['account', 'category'])
            ->whereBetween('date', [$startOfMonth, $endOfMonth]);

        if ($request->filled('description')) {
            $query->where('description', 'like', '%' . $request->description . '%');
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('account_id')) {
            $query->where('account_id', $request->account_id);
        }
        
        $transactions = $query->orderBy('date', 'asc')->get();

        // 3. Adiantamentos (Pagos ESTE mês, mas que pertencem ao FUTURO)
        $earlyPayments = Transaction::with(['account', 'category'])
            ->where('payment_date', '>=', $startOfMonth)
            ->where('payment_date', '<=', $endOfMonth)
            ->where('date', '>', $endOfMonth)
            ->get();

        // 4. Faturas de Cartão do Mês
        $invoices = \App\Models\CreditCardInvoice::with('creditCard')
            ->where('reference_month', $refMonthStr)
            ->get();

        // 5. Cálculos do Resumo (Fluxo de Caixa do Mês)
        // Entradas = (Transações do mês pagas este mês) + (Pendentes do mês) + (Adiantamentos de receita pagos este mês)
        // Vamos simplificar: Usar payment_date para tudo o que foi PAGO este mês + PENDENTES do mês de referência.
        
        $paidIncomesThisMonth = Transaction::where('type', 'income')
            ->where('status', 'paid')
            ->whereBetween('payment_date', [$startOfMonth, $endOfMonth])
            ->sum('amount');
            
        $pendingIncomesThisRefMonth = $transactions->where('type', 'income')->where('status', 'pending')->sum('amount');
        $totalIncome = $paidIncomesThisMonth + $pendingIncomesThisRefMonth;

        $paidExpensesThisMonth = Transaction::where('type', 'expense')
            ->where('status', 'paid')
            ->whereBetween('payment_date', [$startOfMonth, $endOfMonth])
            ->sum('amount');
        
        // Faturas pagas este mês (já estão nas transactions via CreditCardService@payInvoice)
        // Somamos apenas as faturas que NÃO estão pagas mas vencem este mês:
        $pendingInvoicesAmount = $invoices->where('status', '!=', 'paid')->sum('total_amount');
        
        $pendingExpensesThisRefMonth = $transactions->where('type', 'expense')->where('status', 'pending')->sum('amount');
        $totalExpense = $paidExpensesThisMonth + $pendingExpensesThisRefMonth + $pendingInvoicesAmount;

        $monthlyResult = $totalIncome - $totalExpense;
        $finalProjectedBalance = $globalOpeningBalance + $monthlyResult;

        $accounts = Account::all();
        $categories = Category::all();

        // Links para navegação
        $prevDate = $date->copy()->subMonth();
        $nextDate = $date->copy()->addMonth();

        $prevParams = ['month' => $prevDate->month, 'year' => $prevDate->year];
        $nextParams = ['month' => $nextDate->month, 'year' => $nextDate->year];
        $currentMonthName = $date->translatedFormat('F \d\e Y');

        return view('transactions.payable', compact(
            'transactions', 'earlyPayments', 'invoices', 'accounts', 'categories', 'currentMonthName', 
            'prevParams', 'nextParams', 'globalOpeningBalance', 
            'totalIncome', 'totalExpense', 'monthlyResult', 'finalProjectedBalance'
        ));
    }

    public function pay(Request $request, Transaction $transaction)
    {
        $request->validate([
            'final_amount' => 'required|numeric|min:0.01',
            'account_id' => 'required|exists:accounts,id',
        ]);

        $this->service->payTransaction($transaction, $request->final_amount, $request->account_id);

        return back()->with('success', 'Pagamento confirmado!');
    }

    public function stopRecurrence(Transaction $transaction)
    {
        $this->service->stopRecurrence($transaction);
        return back()->with('success', 'Recorrência interrompida e meses futuros limpos.');
    }

    public function replicateRecurrences(Request $request)
    {
        $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer',
        ]);

        $count = $this->service->replicateRecurrencesFromMonth($request->month, $request->year);

        return back()->with('success', "Sincronização concluída! {$count} novas transações geradas com base no mês anterior.");
    }
}
