<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Transaction;
use App\Services\TransactionService;
use Carbon\Carbon;

class RepairRecurrences extends Command
{
    protected $signature = 'transactions:repair-recurrence';
    protected $description = 'Gera ocorrências futuras para transações pagas e recorrentes que estão órfãs';

    public function handle(TransactionService $service)
    {
        $this->info('Iniciando reparo de recorrências...');

        $transactions = Transaction::where('is_recurring', true)
            ->where('status', 'paid')
            ->get();

        $count = 0;
        foreach ($transactions as $t) {
            $nextDate = Carbon::parse($t->date)->addMonth();
            
            // Verifica se já existe uma transação idêntica no mês seguinte (mesmo pendente ou paga)
            $exists = Transaction::where('description', $t->description)
                ->where('date', $nextDate->format('Y-m-d'))
                ->exists();

            if (!$exists) {
                Transaction::create([
                    'account_id' => $t->account_id,
                    'category_id' => $t->category_id,
                    'description' => str_replace(' (Adiantado)', '', $t->description),
                    'amount' => $t->amount,
                    'type' => $t->type,
                    'date' => $nextDate,
                    'is_recurring' => true,
                    'recurrence_type' => $t->recurrence_type,
                    'associated_with' => $t->associated_with,
                    'status' => 'pending'
                ]);
                $count++;
                $this->line("Gerada próxima ocorrência para: {$t->description} ({$nextDate->format('m/Y')})");
            }
        }

        $this->info("Reparo concluído. {$count} novas transações geradas.");
    }
}
