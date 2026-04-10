<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\Account;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TransactionService
{
    public function createTransaction(array $data): Transaction
    {
        return DB::transaction(function () use ($data) {
            $transaction = Transaction::create($data);

            if ($transaction->status === 'paid') {
                $this->updateAccountBalance($transaction);
                $this->replicateRecurringTransaction($transaction);
            }

            // Geração em massa se houver repeat_until
            if ($transaction->is_recurring && !empty($data['repeat_until'])) {
                $until = Carbon::parse($data['repeat_until']);
                $currentDate = Carbon::parse($transaction->date);
                
                while (true) {
                    $currentDate->addMonth();
                    if ($currentDate->gt($until)) break;

                    Transaction::create(array_merge($data, [
                        'date' => $currentDate->copy(),
                        'status' => 'pending',
                        'payment_date' => null
                    ]));
                }
            }

            return $transaction;
        });
    }

    public function updateTransaction(Transaction $transaction, array $data): Transaction
    {
        return DB::transaction(function () use ($transaction, $data) {
            $oldStatus = $transaction->status;
            $oldAmount = $transaction->amount;
            $oldType = $transaction->type;
            $oldAccountId = $transaction->account_id;

            $transaction->update($data);

            if ($oldStatus === 'paid') {
                $this->revertAccountBalance($oldAccountId, $oldAmount, $oldType);
            }

            if ($transaction->status === 'paid') {
                $this->updateAccountBalance($transaction);

                // Se mudou para pago agora ou se já era pago e ainda é recorrente
                $this->replicateRecurringTransaction($transaction);
            }

            return $transaction;
        });
    }

    public function deleteTransaction(Transaction $transaction): bool
    {
        return DB::transaction(function () use ($transaction) {
            if ($transaction->status === 'paid') {
                $this->revertAccountBalance($transaction->account_id, $transaction->amount, $transaction->type);
            }
            return $transaction->delete();
        });
    }

    /**
     * Confirmação de conta a pagar (Contas do Mês).
     * Permite mudar o valor pago final.
     */
    public function payTransaction(Transaction $transaction, float $finalAmount, int $accountId): Transaction
    {
        return DB::transaction(function () use ($transaction, $finalAmount, $accountId) {
            $paymentDate = Carbon::now();
            
            $description = $transaction->description;
            if ($paymentDate->format('Y-m') < Carbon::parse($transaction->date)->format('Y-m')) {
                $description .= " (Adiantado)";
            }

            // Atualiza o valor para o que realmente foi pago e vincula a conta de débito
            $transaction->update([
                'amount' => $finalAmount,
                'account_id' => $accountId,
                'status' => 'paid',
                'description' => $description,
                'payment_date' => $paymentDate
            ]);

            // Debitar / Creditar da conta
            $this->updateAccountBalance($transaction);

            // Replicar pro mês seguinte
            $this->replicateRecurringTransaction($transaction);

            return $transaction;
        });
    }

    /**
     * Gera a próxima ocorrência de uma transação recorrente
     */
    private function replicateRecurringTransaction(Transaction $transaction): void
    {
        if (!$transaction->is_recurring || $transaction->status !== 'paid') {
            return;
        }

        // Acha o próximo mês a partir da data de REFERÊNCIA
        $nextMonthDate = Carbon::parse($transaction->date)->addMonth();
        
        // TRAVA DE TÉRMINO: Se o próximo mês passar do limite definido, para aqui.
        if ($transaction->repeat_until && $nextMonthDate->gt($transaction->repeat_until)) {
            return;
        }

        // Evita duplicidade simples: verifica se já existe uma transação idêntica
        // pendente na mesma data de referência.
        $exists = Transaction::where('description', $transaction->description)
            ->where('date', $nextMonthDate->format('Y-m-d'))
            ->exists();

        if (!$exists) {
            Transaction::create([
                'account_id' => $transaction->account_id,
                'category_id' => $transaction->category_id,
                'description' => str_replace(' (Adiantado)', '', $transaction->description),
                'amount' => $transaction->amount,
                'type' => $transaction->type,
                'date' => $nextMonthDate,
                'is_recurring' => true,
                'repeat_until' => $transaction->repeat_until,
                'recurrence_type' => $transaction->recurrence_type,
                'associated_with' => $transaction->associated_with,
                'status' => 'pending'
            ]);
        }
    }

    /**
     * Interrompe um ciclo de recorrência e limpa o futuro
     */
    public function stopRecurrence(Transaction $transaction): void
    {
        DB::transaction(function () use ($transaction) {
            // 1. Desmarca a transação atual
            $transaction->update(['is_recurring' => false]);

            // 2. Remove ocorrências futuras que estejam pendentes
            Transaction::where('description', $transaction->description)
                ->where('amount', $transaction->amount)
                ->where('status', 'pending')
                ->where('date', '>', $transaction->date)
                ->delete();
        });
    }

    /**
     * Sincroniza todas as recorrências do mês anterior para o mês alvo
     */
    public function replicateRecurrencesFromMonth(int $month, int $year): int
    {
        $targetDate = Carbon::createFromDate($year, $month, 1);
        $prevDate = $targetDate->copy()->subMonth();
        
        $sourceStart = $prevDate->copy()->startOfMonth();
        $sourceEnd = $prevDate->copy()->endOfMonth();

        // Busca todas as recorrências do mês anterior
        $recurrences = Transaction::where('is_recurring', true)
            ->whereBetween('date', [$sourceStart, $sourceEnd])
            ->get();

        $count = 0;
        foreach ($recurrences as $r) {
            $newDate = Carbon::parse($r->date)->addMonth();
            
            // Verifica se já existe algo igual (mesma descrição) no mês alvo
            $exists = Transaction::where('description', $r->description)
                ->where('date', $newDate->format('Y-m-d'))
                ->exists();

            if (!$exists) {
                Transaction::create([
                    'account_id' => $r->account_id,
                    'category_id' => $r->category_id,
                    'description' => str_replace(' (Adiantado)', '', $r->description),
                    'amount' => $r->amount,
                    'type' => $r->type,
                    'date' => $newDate,
                    'is_recurring' => true,
                    'recurrence_type' => $r->recurrence_type,
                    'associated_with' => $r->associated_with,
                    'status' => 'pending'
                ]);
                $count++;
            }
        }

        return $count;
    }

    private function updateAccountBalance(Transaction $transaction): void
    {
        $account = Account::findOrFail($transaction->account_id);
        
        if ($transaction->type === 'income') {
            $account->balance += $transaction->amount;
        } else {
            $account->balance -= $transaction->amount;
        }

        $account->save();
    }

    private function revertAccountBalance(int $accountId, float $amount, string $type): void
    {
        $account = Account::findOrFail($accountId);
        
        if ($type === 'income') {
            $account->balance -= $amount;
        } else {
            $account->balance += $amount;
        }

        $account->save();
    }
}
