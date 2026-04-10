<?php

namespace App\Http\Controllers;

use App\Models\Investment;
use Illuminate\Http\Request;

class InvestmentController extends Controller
{
    public function index()
    {
        $investments = Investment::orderBy('start_date', 'desc')->get();
        return view('investments.index', compact('investments'));
    }

    public function create()
    {
        return view('investments.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'invested_amount' => 'required|numeric|min:0',
            'current_balance' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'estimated_profitability' => 'nullable|numeric'
        ]);

        Investment::create($validated);
        return redirect()->route('investments.index')->with('success', 'Investimento registrado com sucesso.');
    }

    public function edit(Investment $investment)
    {
        return view('investments.edit', compact('investment'));
    }

    public function update(Request $request, Investment $investment)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'invested_amount' => 'required|numeric|min:0',
            'current_balance' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'estimated_profitability' => 'nullable|numeric'
        ]);

        $investment->update($validated);
        return redirect()->route('investments.index')->with('success', 'Investimento atualizado.');
    }

    public function destroy(Investment $investment)
    {
        $investment->delete();
        return redirect()->route('investments.index')->with('success', 'Investimento removido.');
    }
}
