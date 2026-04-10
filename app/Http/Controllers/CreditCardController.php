<?php

namespace App\Http\Controllers;

use App\Models\CreditCard;
use App\Models\Category;
use App\Services\CreditCardService;
use Illuminate\Http\Request;

class CreditCardController extends Controller
{
    private CreditCardService $service;

    public function __construct(CreditCardService $service) 
    {
        $this->service = $service;
    }

    public function index()
    {
        $cards = CreditCard::with(['invoices' => function($q) {
            $q->orderBy('reference_month', 'desc');
        }])->get();
        return view('credit_cards.index', compact('cards'));
    }

    public function create()
    {
        return view('credit_cards.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'limit' => 'required|numeric|min:0',
            'closing_day' => 'required|integer|min:1|max:31',
            'due_day' => 'required|integer|min:1|max:31',
        ]);

        CreditCard::create($validated);
        return redirect()->route('credit-cards.index')->with('success', 'Cartão adicionado com sucesso.');
    }

    public function edit(CreditCard $creditCard)
    {
        return view('credit_cards.edit', compact('creditCard'));
    }

    public function update(Request $request, CreditCard $creditCard)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'limit' => 'required|numeric|min:0',
            'closing_day' => 'required|integer|min:1|max:31',
            'due_day' => 'required|integer|min:1|max:31',
        ]);

        $creditCard->update($validated);
        return redirect()->route('credit-cards.index')->with('success', 'Cartão atualizado com sucesso.');
    }

    public function show(CreditCard $creditCard)
    {
        $creditCard->load(['invoices' => function($q) {
            $q->orderBy('reference_month', 'desc');
        }, 'invoices.transactions' => function($q) {
            $q->orderBy('date', 'desc');
        }]);
        
        $categories = Category::all();
        return view('credit_cards.show', compact('creditCard', 'categories'));
    }

    public function addTransaction(Request $request, CreditCard $creditCard)
    {
        $validated = $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'installments' => 'required|integer|min:1',
        ]);

        $this->service->addTransaction($creditCard, $validated);
        return back()->with('success', 'Compra no cartão lançada com sucesso.');
    }

    public function payInvoice(Request $request, \App\Models\CreditCardInvoice $invoice)
    {
        $request->validate([
            'account_id' => 'required|exists:accounts,id'
        ]);

        $this->service->payInvoice($invoice, $request->account_id);

        return back()->with('success', 'Fatura confirmada e paga com sucesso!');
    }

    public function import(Request $request, CreditCard $creditCard)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt',
            'reference_month' => 'nullable|string|regex:/^\d{4}-\d{2}$/'
        ]);

        $results = $this->service->importFromCsv(
            $creditCard, 
            $request->file('csv_file')->path(),
            $request->reference_month
        );

        return back()->with('success', "Importação concluída: {$results['imported']} transações novas, {$results['ignored']} ignoradas.");
    }
}
