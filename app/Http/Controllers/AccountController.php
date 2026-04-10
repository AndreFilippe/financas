<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function index()
    {
        $accounts = Account::all();
        return view('accounts.index', compact('accounts'));
    }

    public function create()
    {
        return view('accounts.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'balance' => 'required|numeric',
            'is_benefit' => 'nullable|boolean'
        ]);

        $validated['is_benefit'] = $request->has('is_benefit');

        Account::create($validated);
        return redirect()->route('accounts.index')->with('success', 'Conta criada com sucesso.');
    }

    public function show(Request $request, Account $account)
    {
        $month = $request->get('month', \Carbon\Carbon::now()->month);
        $year = $request->get('year', \Carbon\Carbon::now()->year);

        $date = \Carbon\Carbon::createFromDate($year, $month, 1);
        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();

        // Cálculo do Saldo Inicial do Período
        // Saldo Inicial = Saldo Atual - (Transações pagas desde o início do período até hoje)
        $futureMovements = $account->transactions()
            ->where('status', 'paid')
            ->where('date', '>=', $startOfMonth)
            ->get();

        $sumFutureIncomes = $futureMovements->where('type', 'income')->sum('amount');
        $sumFutureExpenses = $futureMovements->where('type', 'expense')->sum('amount');
        $openingBalance = $account->balance - $sumFutureIncomes + $sumFutureExpenses;

        // Filtros
        $query = $account->transactions()
            ->with('category')
            ->where('status', 'paid')
            ->whereBetween('date', [$startOfMonth, $endOfMonth]);

        if ($request->filled('description')) {
            $query->where('description', 'like', '%' . $request->description . '%');
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $transactions = $query->orderBy('date', 'desc')->get();

        // Resumo do período
        $periodIncome = $transactions->where('type', 'income')->sum('amount');
        $periodExpense = $transactions->where('type', 'expense')->sum('amount');

        // Links para navegação
        $prevDate = $date->copy()->subMonth();
        $nextDate = $date->copy()->addMonth();

        $prevParams = ['month' => $prevDate->month, 'year' => $prevDate->year];
        $nextParams = ['month' => $nextDate->month, 'year' => $nextDate->year];
        $currentMonthName = $date->translatedFormat('F \d\e Y');
        $categories = \App\Models\Category::all();

        return view('accounts.show', compact(
            'account', 'transactions', 'periodIncome', 'periodExpense', 
            'currentMonthName', 'prevParams', 'nextParams', 'categories', 'openingBalance'
        ));
    }

    public function edit(Account $account)
    {
        return view('accounts.edit', compact('account'));
    }

    public function update(Request $request, Account $account)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'balance' => 'required|numeric',
            'is_benefit' => 'nullable|boolean'
        ]);

        $validated['is_benefit'] = $request->has('is_benefit');

        $account->update($validated);
        return redirect()->route('accounts.index')->with('success', 'Conta atualizada com sucesso.');
    }

    public function destroy(Account $account)
    {
        $account->delete();
        return redirect()->route('accounts.index')->with('success', 'Conta removida com sucesso.');
    }
}
