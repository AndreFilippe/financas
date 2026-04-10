<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Transaction;
use App\Models\CreditCardInvoice;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $now = Carbon::now();
        
        // Define o período com base nos filtros
        $month = $request->get('month', $now->month);
        $year = $request->get('year', $now->year);
        $date = Carbon::createFromDate($year, $month, 1);

        $startOfPeriod = $date->copy()->startOfMonth();
        $endOfPeriod = $date->copy()->endOfMonth();

        if ($request->filled('date_from')) {
            $startOfPeriod = Carbon::parse($request->date_from)->startOfDay();
        }
        if ($request->filled('date_to')) {
            $endOfPeriod = Carbon::parse($request->date_to)->endOfDay();
        }
        
        $totalCurrentBalance = Account::where('is_benefit', false)->sum('balance');
        $accounts = Account::all()->map(function($acc) use ($startOfPeriod, $endOfPeriod) {
            $pendingIncomes = Transaction::where('account_id', $acc->id)
                ->where('type', 'income')
                ->where('status', 'pending')
                ->whereBetween('date', [$startOfPeriod, $endOfPeriod])
                ->sum('amount');

            $pendingExpenses = Transaction::where('account_id', $acc->id)
                ->where('type', 'expense')
                ->where('status', 'pending')
                ->whereBetween('date', [$startOfPeriod, $endOfPeriod])
                ->sum('amount');

            $acc->projected_balance = $acc->balance + $pendingIncomes - $pendingExpenses;
            return $acc;
        });

        // 1. Cálculo do Saldo Inicial do Período (Retroativo)
        // Pegamos o saldo atual e "descontamos" tudo o que foi PAGO do início do período selecionado até hoje.
        $futurePaidMovements = Transaction::where('status', 'paid')
            ->where('payment_date', '>=', $startOfPeriod)
            ->get();
        
        $openingBalanceOfPeriod = $totalCurrentBalance 
            - $futurePaidMovements->where('type', 'income')->sum('amount') 
            + $futurePaidMovements->where('type', 'expense')->sum('amount');

        // 2. Movimentação do Período (Realizado + Pendente)
        $transactionsInPeriod = Transaction::whereBetween('date', [$startOfPeriod, $endOfPeriod])->get();
        
        // Receitas do período (Pagas + Pendentes)
        $projectedIncome = $transactionsInPeriod->where('type', 'income')->sum('amount');

        // Despesas do período (Pagas + Pendentes)
        $projectedExpense = $transactionsInPeriod->where('type', 'expense')->sum('amount');

        // Faturas de Cartão pendentes no mês de referência
        $refMonthStr = $date->format('Y-m');
        $unpaidInvoices = CreditCardInvoice::where('reference_month', $refMonthStr)
            ->where('status', '!=', 'paid')
            ->sum('total_amount');

        $projectedExpense += $unpaidInvoices;

        // 3. Resultados
        $projectedResult = $projectedIncome - $projectedExpense;
        $projectedFinalBalance = $openingBalanceOfPeriod + $projectedResult;

        // Métricas Realizadas (Apenas o que já foi pago no período)
        $realizedIncome = Transaction::where('type', 'income')
            ->where('status', 'paid')
            ->whereBetween('payment_date', [$startOfPeriod, $endOfPeriod])
            ->sum('amount');

        $realizedExpense = Transaction::where('type', 'expense')
            ->where('status', 'paid')
            ->whereBetween('payment_date', [$startOfPeriod, $endOfPeriod])
            ->sum('amount');

        // Próximos Vencimentos (7 dias de utilidade imediata)
        $upcomingBills = Transaction::where('status', 'pending')
            ->where('date', '>=', $now->copy()->startOfDay())
            ->where('date', '<=', $now->copy()->addDays(7))
            ->orderBy('date', 'asc')
            ->get();

        // Categorias (Baseado no projetado: transações + faturas)
        $expensesByCategory = Transaction::where('type', 'expense')
            ->whereBetween('date', [$startOfPeriod, $endOfPeriod])
            // Ignoramos transações que são o pagamento da fatura em si para não duplicar com o montante da fatura
            ->where('description', 'not like', 'Pagamento Fatura:%')
            ->with('category')
            ->selectRaw('category_id, SUM(amount) as value')
            ->groupBy('category_id')
            ->get();

        $chartLabels = $expensesByCategory->map(fn($t) => $t->category ? $t->category->name : 'Geral')->toArray();
        $chartData = $expensesByCategory->pluck('value')->toArray();

        // Adiciona "Cartão" como uma categoria fixa se houver faturas no período
        if ($projectedExpense > 0 && $unpaidInvoices > 0 || Transaction::where('description', 'like', 'Pagamento Fatura:%')->whereBetween('date', [$startOfPeriod, $endOfPeriod])->exists()) {
            $totalInvoicesThisMonth = CreditCardInvoice::where('reference_month', $refMonthStr)->sum('total_amount');
            if ($totalInvoicesThisMonth > 0) {
                $chartLabels[] = 'Cartão (Fatores)';
                $chartData[] = (float)$totalInvoicesThisMonth;
            }
        }

        // Gráfico de Tendência (6 meses)
        $trendLabels = [];
        $trendData = [];
        $trendAnchor = Carbon::createFromDate($year, $month, 1);
        for ($i = 5; $i >= 0; $i--) {
            $mDate = $trendAnchor->copy()->subMonths($i);
            $trendLabels[] = $mDate->translatedFormat('M/y');
            $bal = Transaction::where('date', '<=', $mDate->endOfMonth())
                ->where('status', 'paid')
                ->selectRaw("SUM(CASE WHEN type = 'income' THEN amount ELSE -amount END) as bal")
                ->value('bal') ?? 0;
            $trendData[] = round($bal, 2);
        }

        $currentMonthName = $date->translatedFormat('F \d\e Y');

        return view('dashboard.index', compact(
            'totalCurrentBalance', 'projectedIncome', 'projectedExpense', 'projectedFinalBalance',
            'openingBalanceOfPeriod', 'realizedIncome', 'realizedExpense',
            'accounts', 'upcomingBills', 'chartLabels', 'chartData', 'trendLabels', 'trendData', 'currentMonthName'
        ));
    }

    /**
     * Dashboard específico de análise de cartões
     */
    public function cards(Request $request)
    {
        $now = Carbon::now();
        $month = $request->get('month', $now->month);
        $year = $request->get('year', $now->year);
        $date = Carbon::createFromDate($year, $month, 1);
        $currentMonthName = $date->translatedFormat('F \d\e Y');
        $refMonthStr = $date->format('Y-m');

        // Busca as faturas do mês selecionado
        $invoices = \App\Models\CreditCardInvoice::where('reference_month', $refMonthStr);
        if ($request->filled('credit_card_id')) {
            $invoices->where('credit_card_id', $request->credit_card_id);
        }
        $invoices = $invoices->get();
        $invoiceIds = $invoices->pluck('id');

        // 1. Ranking de Gastos por Cartão no mês
        $cardsSpending = \App\Models\CreditCard::all()->map(function($card) use ($refMonthStr) {
            $invoice = $card->invoices()->where('reference_month', $refMonthStr)->first();
            return [
                'name' => $card->name,
                'limit' => $card->limit,
                'spent' => $invoice ? $invoice->total_amount : 0,
                'status' => $invoice ? $invoice->status : 'pending'
            ];
        });

        // 2. Gastos por Categoria DENTRO dos cartões (Baseado nas faturas do mês)
        $cardCategoriesQuery = \App\Models\CreditCardTransaction::whereIn('credit_card_invoice_id', $invoiceIds);
        
        if ($request->filled('description')) {
            $cardCategoriesQuery->where('description', 'like', '%' . $request->description . '%');
        }
        if ($request->filled('category_id')) {
            $cardCategoriesQuery->where('category_id', $request->category_id);
        }

        $cardCategories = (clone $cardCategoriesQuery)
            ->with('category')
            ->selectRaw('category_id, SUM(amount) as value')
            ->groupBy('category_id')
            ->get();

        $chartLabels = $cardCategories->map(fn($t) => $t->category ? $t->category->name : 'Geral');
        $chartData = $cardCategories->pluck('value');

        // 3. Todos os Gastos no Cartão (Baseado nas faturas do mês)
        $allTransactions = $cardCategoriesQuery
            ->with(['category', 'invoice.creditCard'])
            ->orderBy('date', 'desc')
            ->get();

        $categories = \App\Models\Category::orderBy('name')->get();
        $creditCards = \App\Models\CreditCard::all();

        return view('dashboard.cards', compact(
            'cardsSpending', 'chartLabels', 'chartData', 'allTransactions', 'categories', 'creditCards', 'currentMonthName'
        ));
    }
}
