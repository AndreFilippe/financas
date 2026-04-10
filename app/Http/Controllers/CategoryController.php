<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::all();
        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        return view('categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:income,expense,both',
            'color' => 'nullable|string|max:7',
        ]);

        Category::create($validated);
        return redirect()->route('categories.index')->with('success', 'Categoria criada com sucesso.');
    }

    public function edit(Category $category)
    {
        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:income,expense,both',
            'color' => 'nullable|string|max:7',
        ]);

        $category->update($validated);
        return redirect()->route('categories.index')->with('success', 'Categoria atualizada.');
    }

    public function destroy(Category $category)
    {
        // Se a categoria tiver transações, dependendo da migration, fica nulo ou bloqueia
        $category->delete();
        return redirect()->route('categories.index')->with('success', 'Categoria excluída.');
    }
}
