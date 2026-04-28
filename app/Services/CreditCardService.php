<?php

namespace App\Services;

use App\Models\CreditCard;
use App\Models\CreditCardInvoice;
use App\Models\CreditCardTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CreditCardService
{
    /**
     * Add a transaction to a credit card, automatically finding/creating the right invoice
     * based on due date and closing day, and replicating for installments.
     */
    public function addTransaction(CreditCard $card, array $data): void
    {
        DB::transaction(function () use ($card, $data) {
            $baseDate = Carbon::parse($data['date']);
            $installments = (int)($data['installments'] ?? 1);
            $amountPerInstallment = $data['amount'] / $installments;

            for ($i = 1; $i <= $installments; $i++) {
                $targetDate = $i === 1 ? $baseDate : $baseDate->copy()->addMonths($i - 1);
                
                $invoice = $this->getOrCreateInvoiceForDate($card, $targetDate);
                
                if ($invoice->status !== 'open') {
                    throw new \Exception("Não é possível adicionar transações à fatura de {$invoice->reference_month} pois ela já está fechada ou paga.");
                }
                
                CreditCardTransaction::create([
                    'credit_card_invoice_id' => $invoice->id,
                    'category_id' => $data['category_id'] ?? null,
                    'description' => $installments > 1 ? $data['description'] . " ({$i}/{$installments})" : $data['description'],
                    'amount' => $amountPerInstallment,
                    'date' => $targetDate->format('Y-m-d'),
                    'installments' => $installments,
                    'current_installment' => $i
                ]);

                $invoice->increment('total_amount', $amountPerInstallment);
            }
        });
    }

    public function payInvoice(CreditCardInvoice $invoice, int $accountId): void
    {
        DB::transaction(function () use ($invoice, $accountId) {
            if ($invoice->status === 'paid') return;

            $card = $invoice->creditCard;
            
            // 1. Criar a transação de despesa
            $transaction = \App\Models\Transaction::create([
                'account_id' => $accountId,
                'category_id' => null, // Opcional: Categoria "Cartão de Crédito"
                'description' => "Pagamento Fatura: {$card->name} ({$invoice->reference_month})",
                'amount' => $invoice->total_amount,
                'type' => 'expense',
                'date' => Carbon::now(),
                'payment_date' => Carbon::now(),
                'status' => 'paid'
            ]);

            // 2. Atualizar saldo da conta (Pode usar o service de transação ou fazer direto)
            $account = \App\Models\Account::findOrFail($accountId);
            $account->balance -= $invoice->total_amount;
            $account->save();

            // 3. Marcar fatura como paga
            $invoice->update(['status' => 'paid']);
        });
    }

    public function importFromCsv(CreditCard $card, string $filePath, ?string $referenceMonth = null): array
    {
        $handle = fopen($filePath, 'r');
        $header = fgetcsv($handle); // Ignora cabeçalho (date,title,amount)
        
        $importedCount = 0;
        $ignoredCount = 0;

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 3) continue;

            $date = Carbon::parse($row[0]);
            $description = $row[1];
            $amount = (float) $row[2];

            // 1. Ignorar pagamentos da fatura e lançamentos zerados
            if (str_contains(strtolower($description), 'pagamento recebido')) {
                $ignoredCount++;
                continue;
            }

            if ($referenceMonth) {
                $invoice = CreditCardInvoice::firstOrCreate(
                    ['credit_card_id' => $card->id, 'reference_month' => $referenceMonth],
                    ['status' => 'open', 'total_amount' => 0]
                );
            } else {
                $invoice = $this->getOrCreateInvoiceForDate($card, $date);
            }

            // 2. Prevenir duplicidade básica (mesma data, descrição e valor na mesma fatura)
            $exists = CreditCardTransaction::where('credit_card_invoice_id', $invoice->id)
                ->where('date', $date->format('Y-m-d'))
                ->where('description', $description)
                ->where('amount', $amount)
                ->exists();

            if (!$exists) {
                CreditCardTransaction::create([
                    'credit_card_invoice_id' => $invoice->id,
                    'description' => $description,
                    'amount' => $amount,
                    'date' => $date->format('Y-m-d'),
                    'installments' => 1,
                    'current_installment' => 1
                ]);

                $invoice->increment('total_amount', $amount);
                $importedCount++;
            } else {
                $ignoredCount++;
            }
        }

        fclose($handle);

        return ['imported' => $importedCount, 'ignored' => $ignoredCount];
    }

    private function getOrCreateInvoiceForDate(CreditCard $card, Carbon $date): CreditCardInvoice
    {
        // Se a compra foi feita após ou no dia de fechamento, cai para o mês seguinte.
        $closingDay = $card->closing_day;
        
        if ($date->day >= $closingDay) {
            $referenceDate = $date->copy()->addMonth();
        } else {
            $referenceDate = $date->copy();
        }
        
        $referenceMonth = $referenceDate->format('Y-m');

        return CreditCardInvoice::firstOrCreate(
            ['credit_card_id' => $card->id, 'reference_month' => $referenceMonth],
            ['status' => 'open', 'total_amount' => 0]
        );
    }
}
