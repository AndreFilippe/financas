<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            // Despesas
            ['name' => 'Alimentação', 'type' => 'expense', 'color' => '#dc3545'],
            ['name' => 'Moradia', 'type' => 'expense', 'color' => '#6610f2'],
            ['name' => 'Transporte', 'type' => 'expense', 'color' => '#0d6efd'],
            ['name' => 'Saúde', 'type' => 'expense', 'color' => '#198754'],
            ['name' => 'Educação', 'type' => 'expense', 'color' => '#ffc107'],
            ['name' => 'Lazer', 'type' => 'expense', 'color' => '#fd7e14'],
            ['name' => 'Vestuário', 'type' => 'expense', 'color' => '#6f42c1'],
            ['name' => 'Serviços (Luz, Água, Internet)', 'type' => 'expense', 'color' => '#20c997'],
            ['name' => 'Hortifruti', 'type' => 'expense', 'color' => '#198754'],
            ['name' => 'Açougue', 'type' => 'expense', 'color' => '#dc3545'],
            ['name' => 'Padaria', 'type' => 'expense', 'color' => '#fd7e14'],
            ['name' => 'Higiene e Limpeza', 'type' => 'expense', 'color' => '#0dcaf0'],
            ['name' => 'Bebidas', 'type' => 'expense', 'color' => '#0d6efd'],
            ['name' => 'Mercearia', 'type' => 'expense', 'color' => '#6c757d'],
            ['name' => 'Laticínios e Frios', 'type' => 'expense', 'color' => '#ffc107'],
            ['name' => 'Delivery (Alimentação)', 'type' => 'expense', 'color' => '#dc3545'],
            ['name' => 'Marketplace (Amazon, ML, etc)', 'type' => 'expense', 'color' => '#fd7e14'],
            ['name' => 'Assinaturas e Streaming', 'type' => 'expense', 'color' => '#6610f2'],
            ['name' => 'Apps de Transporte (Uber, 99)', 'type' => 'expense', 'color' => '#212529'],
            ['name' => 'Farmácia', 'type' => 'expense', 'color' => '#198754'],
            ['name' => 'Outros (Despesas)', 'type' => 'expense', 'color' => '#6c757d'],

            // Receitas
            ['name' => 'Salário', 'type' => 'income', 'color' => '#198754'],
            ['name' => 'Rendimentos / Investimentos', 'type' => 'income', 'color' => '#0d6efd'],
            ['name' => 'Prêmios / Bônus', 'type' => 'income', 'color' => '#ffc107'],
            ['name' => 'Vendas', 'type' => 'income', 'color' => '#20c997'],
            ['name' => 'Outros (Receitas)', 'type' => 'income', 'color' => '#6c757d'],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['name' => $category['name'], 'type' => $category['type']],
                ['color' => $category['color']]
            );
        }
    }
}
