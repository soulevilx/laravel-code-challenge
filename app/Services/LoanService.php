<?php

namespace App\Services;

use App\Models\Loan;
use App\Models\ReceivedRepayment;
use App\Models\ScheduledRepayment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LoanService
{
    /**
     * Create a Loan
     *
     * @param  User  $user
     * @param  int  $amount
     * @param  string  $currencyCode
     * @param  int  $terms
     * @param  string  $processedAt
     *
     * @return Loan
     */
    public function createLoan(User $user, int $amount, string $currencyCode, int $terms, string $processedAt): Loan
    {
        return DB::transaction(function() use ($user, $amount, $currencyCode, $terms, $processedAt) {
            /** @var Loan $loan */
            $loan = $user->loans()->create([
                'amount' => $amount,
                'outstanding_amount' => $amount,
                'currency_code' => $currencyCode,
                'terms' => $terms,
                'processed_at' => $processedAt,
                'status' => Loan::STATUS_DUE
            ]);

            $amountPerRepayment = $amount / $terms;
            $floorAmountPerRepayment = floor($amountPerRepayment);
            $rest = $amount - $floorAmountPerRepayment * $terms;

            for ($countScheduledRepayments = 1; $countScheduledRepayments <= $terms; $countScheduledRepayments++) {

                $scheduledRepaymentAmount = $countScheduledRepayments === $terms
                    ? $floorAmountPerRepayment + $rest
                    : $floorAmountPerRepayment;

                $loan->scheduledRepayments()->create([
                    'amount' => $scheduledRepaymentAmount,
                    'outstanding_amount' => $scheduledRepaymentAmount,
                    'currency_code' => $currencyCode,
                    'due_date' => Carbon::parse($processedAt)->addMonths($countScheduledRepayments),
                    'status' => Loan::STATUS_DUE
                ]);
            }

            return $loan;
        });
    }

    /**
     * Repay Scheduled Repayments for a Loan
     *
     * @param  Loan  $loan
     * @param  int  $amount
     * @param  string  $currencyCode
     * @param  string  $receivedAt
     *
     * @return ReceivedRepayment
     */
    public function repayLoan(Loan $loan, int $amount, string $currencyCode, string $receivedAt): ReceivedRepayment
    {
        return DB::transaction(function () use ($loan, $amount, $currencyCode, $receivedAt) {
            /** @var ReceivedRepayment $receivedPayment */
            $receivedPayment = $loan->receivedRepayments()->create([
                'amount' => $amount,
                'currency_code' => $currencyCode,
                'received_at' => $receivedAt
            ]);

            $loan->outstanding_amount = $loan->outstanding_amount - $amount;
            $loan->status = $loan->outstanding_amount <= 0
                ? Loan::STATUS_REPAID
                : Loan::STATUS_DUE;
            $loan->save();

            $loan->scheduledRepayments()
                ->where('status', '!=', ScheduledRepayment::STATUS_REPAID)
                ->each(function (ScheduledRepayment $scheduledRepayment) use (&$amount) {
                    if ($amount <= 0) return;

                    $amount = $amount - $scheduledRepayment->outstanding_amount;

                    $scheduledRepayment->outstanding_amount = abs(min($amount, 0));
                    $scheduledRepayment->status = $scheduledRepayment->outstanding_amount === 0
                        ? ScheduledRepayment::STATUS_REPAID
                        : ScheduledRepayment::STATUS_PARTIAL;
                    $scheduledRepayment->save();
                });

            return $receivedPayment;
        });
    }
}
