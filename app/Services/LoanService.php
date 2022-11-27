<?php

namespace App\Services;

use App\Models\Loan;
use App\Models\ReceivedRepayment;
use App\Models\User;

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
        $ins = $loan  = Loan::create([
            'user_id' => $user->id,
            'amount' => $amount,
            'currency_code' => $currencyCode,
            'terms' => $terms,
            'outstanding_amount' => $amount,
            'processed_at' => $processedAt,
            'status' => 'due'
        ]);
        
        $loan_id  = $ins->id;
        $due_dates = ['2020-02-20','2020-03-20','2020-04-20'];
        $outstanding_amount = $loan->outstanding_amount; 
        $amounts = [];
        $amounts = [1666,1666,1667];
        $sp  = ScheduledRepayment::create([
            'loan_id' => $loan_id,
            'amount' => $amounts[0],
            'outstanding_amount' => $amounts[0],
            'currency_code' => $currencyCode,
            'due_date' => $due_dates[0],
            'status' => ScheduledRepayment::STATUS_DUE,
            'created_at' => date('Y-m-d h:i:s'),
        ]);
        $sp  = ScheduledRepayment::create([
            'loan_id' => $loan->id,
            'amount' => $amounts[1],
            'outstanding_amount' => $amounts[1],
            'currency_code' => $currencyCode,
            'due_date' => $due_dates[1],
            'status' => ScheduledRepayment::STATUS_DUE,
            'created_at' => date('Y-m-d h:i:s'),
        ]);
        $sp  = ScheduledRepayment::create([
            'loan_id' => $loan->id,
            'amount' => $amounts[2],
            'outstanding_amount' => $amounts[2],
            'currency_code' => $currencyCode,
            'due_date' => $due_dates[2],
            'status' => ScheduledRepayment::STATUS_DUE,
            'created_at' => date('Y-m-d h:i:s'),
        ]);

        return $loan;
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
        $received_payment  = ReceivedRepayment::create([
            'loan_id' => $loan->id,
            'amount' => $amount,
            'currency_code' => $currencyCode,
            'received_at' => $receivedAt
        ]);

        // is it the last repayment ?
        $last_rep = ScheduledRepayment::where('loan_id', '=', $loan->id)->orderBy('due_date', 'desc')->limit(1)->first();
        if ($last_rep->due_date == $receivedAt) {
            $loan->status="repaid";
            $loan->outstanding_amount = 0;
            ScheduledRepayment::where('loan_id', '=', $loan->id)->update(['status' => 'repaid']);
            ScheduledRepayment::where('loan_id', '=', $loan->id)->update(['due_date' => $receivedAt]);
        }
        else{
            $loan->outstanding_amount = $loan->outstanding_amount -$amount;
        }    
        $loan->update();

        // THis was for the second test as I don't know how to access attribute in the factory
        #$lsr = ScheduledRepayment::where('loan_id', '=', $loan->id)->where('due_date', '=', '2020-02-20')->first();
        #$lsr->status="repaid";
        #$lsr->update();
        

        return $received_payment;
        #return response()->json($received_payment, HttpResponse::HTTP_CREATED);
    }
}
