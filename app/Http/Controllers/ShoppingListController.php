<?php

namespace App\Http\Controllers;

use App\Models\ShoppingList;
use App\Models\ShoppingListItem;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ShoppingListController extends Controller
{
    public function index()
    {
        $lists = ShoppingList::withCount('items')
            ->with(['transactions.account']) // Carregar contas usadas
            ->orderBy('date', 'desc')
            ->get();
        return view('shopping_lists.index', compact('lists'));
    }

    public function dashboard(Request $request)
    {
        $year = $request->get('year', now()->year);
        $month = $request->get('month');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $categoryId = $request->get('category_id');

        // Query base para Listas Fechadas
        $listQuery = ShoppingList::where('status', 'closed')
            ->when($year && !$dateFrom, fn($q) => $q->whereYear('date', $year))
            ->when($month && !$dateFrom, fn($q) => $q->whereMonth('date', $month))
            ->when($dateFrom, fn($q) => $q->where('date', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->where('date', '<=', $dateTo));

        // Query base para Itens
        $itemQuery = ShoppingListItem::join('shopping_lists', 'shopping_list_items.shopping_list_id', '=', 'shopping_lists.id')
            ->where('shopping_lists.status', 'closed')
            ->when($year && !$dateFrom, fn($q) => $q->whereYear('shopping_lists.date', $year))
            ->when($month && !$dateFrom, fn($q) => $q->whereMonth('shopping_lists.date', $month))
            ->when($dateFrom, fn($q) => $q->where('shopping_lists.date', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->where('shopping_lists.date', '<=', $dateTo))
            ->when($categoryId, fn($q) => $q->where('shopping_list_items.category_id', $categoryId));

        // Gastos por Tempo (Mensal ou Diário se filtrar por mês)
        $timeFormat = $month ? '%d/%m' : '%m/%Y';
        $timeSpending = ShoppingListItem::join('shopping_lists', 'shopping_list_items.shopping_list_id', '=', 'shopping_lists.id')
            ->where('shopping_lists.status', 'closed')
            ->when($year && !$dateFrom, fn($q) => $q->whereYear('shopping_lists.date', $year))
            ->when($month && !$dateFrom, fn($q) => $q->whereMonth('shopping_lists.date', $month))
            ->when($dateFrom, fn($q) => $q->where('shopping_lists.date', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->where('shopping_lists.date', '<=', $dateTo))
            ->when($categoryId, fn($q) => $q->where('shopping_list_items.category_id', $categoryId))
            ->select(
                DB::raw("DATE_FORMAT(shopping_lists.date, '{$timeFormat}') as period"),
                DB::raw("SUM(shopping_list_items.quantity * shopping_list_items.actual_unit_price) as total")
            )
            ->groupBy('period')
            ->orderBy(DB::raw('MIN(shopping_lists.date)'), 'asc')
            ->get();

        // Gastos por Categoria (se não houver filtro de categoria, ou mostra a evolução da sub-categoria se existisse)
        $categoryDistribution = ShoppingListItem::join('shopping_lists', 'shopping_list_items.shopping_list_id', '=', 'shopping_lists.id')
            ->join('categories', 'shopping_list_items.category_id', '=', 'categories.id')
            ->where('shopping_lists.status', 'closed')
            ->when($year && !$dateFrom, fn($q) => $q->whereYear('shopping_lists.date', $year))
            ->when($dateFrom, fn($q) => $q->where('shopping_lists.date', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->where('shopping_lists.date', '<=', $dateTo))
            ->select(
                'categories.name',
                'categories.color',
                DB::raw("SUM(shopping_list_items.quantity * shopping_list_items.actual_unit_price) as total")
            )
            ->groupBy('categories.id', 'categories.name', 'categories.color')
            ->orderBy('total', 'desc')
            ->get();

        // Itens que mais custam
        $topItems = $itemQuery->clone()
            ->select(
                'shopping_list_items.name',
                DB::raw("SUM(shopping_list_items.quantity * shopping_list_items.actual_unit_price) as total_spent"),
                DB::raw("AVG(shopping_list_items.actual_unit_price) as avg_price")
            )
            ->groupBy('shopping_list_items.name')
            ->orderBy('total_spent', 'desc')
            ->limit(10)
            ->get();

        // Médias e Totais Filtrados
        $stats = [
            'total_spent' => $itemQuery->sum(DB::raw('shopping_list_items.quantity * shopping_list_items.actual_unit_price')),
            'avg_list_value' => $listQuery->avg('total_amount') ?: 0,
            'total_lists' => $listQuery->count(),
            'item_count' => $itemQuery->count(),
        ];

        // Dados para filtros
        $availableYears = ShoppingList::selectRaw('YEAR(date) as year')->distinct()->orderBy('year', 'desc')->pluck('year');
        $categories = Category::orderBy('name')->get();

        return view('shopping_lists.dashboard', compact('timeSpending', 'categoryDistribution', 'topItems', 'stats', 'availableYears', 'categories'));
    }

    public function create()
    {
        return view('shopping_lists.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'date' => 'required|date',
        ]);

        $list = ShoppingList::create($validated);
        return redirect()->route('shopping-lists.show', $list)->with('success', 'Lista de compras criada!');
    }

    public function show(ShoppingList $shoppingList)
    {
        $shoppingList->load(['items.category', 'transactions.account']);
        $accounts = Account::all();
        $categories = Category::all();
        
        $totalEstimated = $shoppingList->items->sum(fn($i) => $i->quantity * $i->estimated_unit_price);
        $totalActual = $shoppingList->items->sum(fn($i) => $i->quantity * $i->actual_unit_price);

        return view('shopping_lists.show', compact('shoppingList', 'accounts', 'categories', 'totalEstimated', 'totalActual'));
    }

    public function addItem(Request $request, ShoppingList $shoppingList)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'quantity' => 'required|numeric|min:0.001',
            'estimated_unit_price' => 'required|numeric|min:0',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        // Preenche o preço real com o estimado por padrão
        $validated['estimated_unit_price'] = $validated['estimated_unit_price'] ?? 0;
        $validated['actual_unit_price'] = $validated['estimated_unit_price'];

        $shoppingList->items()->create($validated);
        return back()->with('success', 'Item adicionado.');
    }

    public function copy(ShoppingList $shoppingList)
    {
        return view('shopping_lists.copy', compact('shoppingList'));
    }

    public function duplicate(Request $request, ShoppingList $shoppingList)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'date' => 'required|date',
        ]);

        return DB::transaction(function() use ($shoppingList, $validated) {
            $newList = ShoppingList::create([
                'name' => $validated['name'],
                'location' => $validated['location'],
                'date' => $validated['date'],
                'status' => 'open',
                'total_amount' => 0
            ]);

            foreach ($shoppingList->items as $item) {
                $newItem = $item->replicate();
                $newItem->shopping_list_id = $newList->id;
                // Preço estimado da nova = preço real da antiga
                $newItem->estimated_unit_price = $item->actual_unit_price > 0 ? $item->actual_unit_price : $item->estimated_unit_price;
                $newItem->actual_unit_price = $newItem->estimated_unit_price;
                $newItem->is_checked = false;
                $newItem->save();
            }

            return redirect()->route('shopping-lists.show', $newList)->with('success', 'Lista duplicada com sucesso!');
        });
    }

    public function updateItem(Request $request, ShoppingListItem $item)
    {
        // Tratar categoria vazia como null
        if ($request->has('category_id') && $request->category_id == "") {
            $request->merge(['category_id' => null]);
        }

        $validated = $request->validate([
            'actual_unit_price' => 'nullable|numeric|min:0',
            'quantity' => 'nullable|numeric|min:0.001',
            'is_checked' => 'boolean',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        if ($request->has('is_checked')) {
            $validated['is_checked'] = (bool) $request->is_checked;
        }

        $item->update($validated);
        return response()->json(['success' => true]);
    }

    public function deleteItem(ShoppingListItem $item)
    {
        $item->delete();
        return back()->with('success', 'Item removido.');
    }

    public function finish(Request $request, ShoppingList $shoppingList)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'payment_date' => 'required|date',
            'create_transaction' => 'boolean',
            'closing_reason' => 'required_if:create_transaction,false|nullable|string|min:5',
            'payments' => 'required_if:create_transaction,true|array',
            'payments.*.account_id' => 'required_with:payments|exists:accounts,id',
            'payments.*.amount' => 'required_with:payments|numeric|min:0.01',
            'credit_card_amount' => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function() use ($shoppingList, $validated, $request) {
            $totalActual = $shoppingList->items->sum(fn($i) => $i->quantity * $i->actual_unit_price);
            $creditCardAmount = (float) ($validated['credit_card_amount'] ?? 0);
            
            // Validação de soma se for criar transação
            if ($request->boolean('create_transaction')) {
                $sumPayments = collect($validated['payments'] ?? [])->sum('amount');
                if (abs(($sumPayments + $creditCardAmount) - $totalActual) > 0.01) {
                    throw new \Exception("A soma dos pagamentos (R$ ".($sumPayments + $creditCardAmount).") deve ser igual ao total da compra (R$ $totalActual).");
                }
            }

            $closingNote = $validated['closing_reason'] ?? null;
            if ($creditCardAmount > 0) {
                $ccPart = "R$ " . number_format($creditCardAmount, 2, ',', '.');
                $closingNote = ($closingNote ? $closingNote . " | " : "") . "Pago no Cartão: $ccPart (Não lançado no extrato)";
            }

            $shoppingList->update([
                'total_amount' => $totalActual,
                'status' => 'closed',
                'closing_reason' => $closingNote
            ]);

            if ($request->boolean('create_transaction')) {
                foreach ($validated['payments'] as $payment) {
                    Transaction::create([
                        'account_id' => $payment['account_id'],
                        'category_id' => $validated['category_id'],
                        'shopping_list_id' => $shoppingList->id,
                        'description' => "Compra: " . $shoppingList->name . ($creditCardAmount > 0 ? " (Parte em Conta)" : ""),
                        'amount' => $payment['amount'],
                        'type' => 'expense',
                        'date' => $shoppingList->date,
                        'payment_date' => $validated['payment_date'],
                        'status' => 'paid'
                    ]);

                    // Update account balance
                    $account = Account::find($payment['account_id']);
                    $account->balance -= $payment['amount'];
                    $account->save();
                }
            }
        });

        return redirect()->route('shopping-lists.index')->with('success', 'Compra finalizada com sucesso!');
    }

    public function destroy(ShoppingList $shoppingList)
    {
        $shoppingList->delete();
        return redirect()->route('shopping-lists.index')->with('success', 'Lista de compras removida.');
    }
}
