<?php

namespace Tests\Unit;

use App\Models\Loan;
use App\Models\ScheduledRepayment;
use App\Models\User;
use App\Services\LoanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoanServiceTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected LoanService $loanService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->loanService = new LoanService();
    }

    public function testServiceCanCreateLoanOfForACustomer()
    {
        $terms = 3;
        $amount = 5000;
        $currencyCode = Loan::CURRENCY_VND;
        $processedAt = '2020-01-20';

        $loan = $this->loanService->createLoan($this->user, $amount, $currencyCode, $terms, $processedAt);

        // Asserting Loan values
        $this->assertDatabaseHas('loans', [
            'id' => $loan->id,
            'user_id' => $this->user->id,
            'amount' => $amount,
            'terms' => $terms,
            'outstanding_amount' => $amount,
            'currency_code' => $currencyCode,
            'processed_at' => '2020-01-20',
            'status' => Loan::STATUS_DUE,
        ]);

        // Asserting Scheduled Repayments
        $this->assertCount($terms, $loan->scheduledRepayments);
        $this->assertDatabaseHas('scheduled_repayments', [
            'loan_id' => $loan->id,
            'amount' => 1666,
            'outstanding_amount' => 1666,
            'currency_code' => $currencyCode,
            'due_date' => '2020-02-20',
            'status' => ScheduledRepayment::STATUS_DUE,
        ]);
        $this->assertDatabaseHas('scheduled_repayments', [
            'loan_id' => $loan->id,
            'amount' => 1666,
            'outstanding_amount' => 1666,
            'currency_code' => $currencyCode,
            'due_date' => '2020-03-20',
            'status' => ScheduledRepayment::STATUS_DUE,
        ]);
        $this->assertDatabaseHas('scheduled_repayments', [
            'loan_id' => $loan->id,
            'amount' => 1667,
            'outstanding_amount' => 1667,
            'currency_code' => $currencyCode,
            'due_date' => '2020-04-20',
            'status' => ScheduledRepayment::STATUS_DUE,
        ]);
        $this->assertEquals($amount, $loan->scheduledRepayments()->sum('amount'));
    }

    public function testServiceCanRepayAScheduledRepayment()
    {
        $loan = Loan::factory()->create([
            'user_id' => $this->user->id,
            'terms' => 3,
            'amount' => 5000,
            'currency_code' => Loan::CURRENCY_VND,
            'processed_at' => '2020-01-20',
        ]);

        $scheduledRepaymentOne =  ScheduledRepayment::factory()->create([
            'loan_id' => $loan->id,
            'amount' => 1666,
            'currency_code' => Loan::CURRENCY_VND,
            'due_date' => '2020-02-20',
        ]);
        $scheduledRepaymentTwo =  ScheduledRepayment::factory()->create([
            'loan_id' => $loan->id,
            'amount' => 1666,
            'currency_code' => Loan::CURRENCY_VND,
            'due_date' => '2020-03-20',
        ]);
        $scheduledRepaymentThree =  ScheduledRepayment::factory()->create([
            'loan_id' => $loan->id,
            'amount' => 1666,
            'currency_code' => Loan::CURRENCY_VND,
            'due_date' => '2020-04-20',
        ]);

        $receivedRepayment = 1666;
        $currencyCode = Loan::CURRENCY_VND;
        $receivedAt = '2020-02-20';

        $loan = $this->loanService->repayLoan($loan, $receivedRepayment, $currencyCode, $receivedAt);

        // Asserting Loan values
        $this->assertDatabaseHas('loans', [
            'id' => $loan->id,
            'user_id' => $this->user->id,
            'amount' => 5000,
            'outstanding_amount' => 5000 - 1666,
            'currency_code' => $currencyCode,
            'status' => Loan::STATUS_DUE,
            'processed_at' => '2020-01-20',
        ]);

        // Asserting First Scheduled Repayment is Repaid
        $this->assertDatabaseHas('scheduled_repayments', [
            'id' => $scheduledRepaymentOne->id,
            'loan_id' => $loan->id,
            'amount' => 1666,
            'outstanding_amount' => 0,
            'currency_code' => $currencyCode,
            'due_date' => '2020-02-20',
            'status' => ScheduledRepayment::STATUS_REPAID,
        ]);

        // Asserting Second and Scheduled Repayments are still due
        $this->assertDatabaseHas('scheduled_repayments', [
            'id' => $scheduledRepaymentTwo->id,
            'status' => ScheduledRepayment::STATUS_DUE,
        ]);
        $this->assertDatabaseHas('scheduled_repayments', [
            'id' => $scheduledRepaymentThree->id,
            'status' => ScheduledRepayment::STATUS_DUE,
        ]);

        // Asserting Received Repayment
        $this->assertDatabaseHas('received_repayments', [
            'loan_id' => $loan->id,
            'amount' => 1666,
            'currency_code' => $currencyCode,
            'received_at' => '2020-02-20',
        ]);
    }

    public function testServiceCanRepayAScheduledRepaymentConsecutively()
    {
        $loan = Loan::factory()->create([
            'user_id' => $this->user->id,
            'terms' => 3,
            'amount' => 5000,
            'currency_code' => Loan::CURRENCY_VND,
            'processed_at' => '2020-01-20',
        ]);

        // First two scheduled repayments are already repaid
        $scheduledRepaymentOne =  ScheduledRepayment::factory()->create([
            'loan_id' => $loan->id,
            'amount' => 1666,
            'currency_code' => Loan::CURRENCY_VND,
            'due_date' => '2020-02-20',
            'status' => ScheduledRepayment::STATUS_REPAID,
        ]);
        $scheduledRepaymentTwo =  ScheduledRepayment::factory()->create([
            'loan_id' => $loan->id,
            'amount' => 1666,
            'currency_code' => Loan::CURRENCY_VND,
            'due_date' => '2020-03-20',
            'status' => ScheduledRepayment::STATUS_REPAID,
        ]);
        // Only the last one is due
        $scheduledRepaymentThree =  ScheduledRepayment::factory()->create([
            'loan_id' => $loan->id,
            'amount' => 1667,
            'currency_code' => Loan::CURRENCY_VND,
            'due_date' => '2020-04-20',
            'status' => ScheduledRepayment::STATUS_DUE,
        ]);

        $receivedRepayment = 1667;
        $currencyCode = Loan::CURRENCY_VND;
        $receivedAt = '2020-04-20';

        // Repaying the last one
        $loan = $this->loanService->repayLoan($loan, $receivedRepayment, $currencyCode, $receivedAt);

        // Asserting Loan values
        $this->assertDatabaseHas('loans', [
            'id' => $loan->id,
            'user_id' => $this->user->id,
            'amount' => 5000,
            'outstanding_amount' => 0,
            'currency_code' => $currencyCode,
            'status' => Loan::STATUS_REPAID,
            'processed_at' => '2020-01-20',
        ]);

        // Asserting Last Scheduled Repayment is Repaid
        $this->assertDatabaseHas('scheduled_repayments', [
            'id' => $scheduledRepaymentThree->id,
            'loan_id' => $loan->id,
            'amount' => 1667,
            'outstanding_amount' => 0,
            'currency_code' => $currencyCode,
            'due_date' => '2020-02-20',
            'status' => ScheduledRepayment::STATUS_REPAID,
        ]);

        // Asserting Received Repayment
        $this->assertDatabaseHas('received_repayments', [
            'loan_id' => $loan->id,
            'amount' => 1667,
            'currency_code' => $currencyCode,
            'received_at' => '2020-04-20',
        ]);
    }

    public function testServiceCanRepayMultipleScheduledRepayments()
    {
        $loan = Loan::factory()->create([
            'user_id' => $this->user->id,
            'terms' => 3,
            'amount' => 5000,
            'currency_code' => Loan::CURRENCY_VND,
            'processed_at' => '2020-01-20',
        ]);

        $scheduledRepaymentOne =  ScheduledRepayment::factory()->create([
            'loan_id' => $loan->id,
            'amount' => 1666,
            'currency_code' => Loan::CURRENCY_VND,
            'due_date' => '2020-02-20',
            'status' => ScheduledRepayment::STATUS_DUE,
        ]);
        $scheduledRepaymentTwo =  ScheduledRepayment::factory()->create([
            'loan_id' => $loan->id,
            'amount' => 1666,
            'currency_code' => Loan::CURRENCY_VND,
            'due_date' => '2020-03-20',
            'status' => ScheduledRepayment::STATUS_DUE,
        ]);
        $scheduledRepaymentThree =  ScheduledRepayment::factory()->create([
            'loan_id' => $loan->id,
            'amount' => 1667,
            'currency_code' => Loan::CURRENCY_VND,
            'due_date' => '2020-04-20',
            'status' => ScheduledRepayment::STATUS_DUE,
        ]);

        // Paying more than the first scheduled repayment amount
        $receivedRepayment = 2000;
        $currencyCode = Loan::CURRENCY_VND;
        $receivedAt = '2020-02-20';

        // Repaying
        $loan = $this->loanService->repayLoan($loan, $receivedRepayment, $currencyCode, $receivedAt);

        // Asserting Loan values
        $this->assertDatabaseHas('loans', [
            'id' => $loan->id,
            'user_id' => $this->user->id,
            'amount' => 5000,
            'outstanding_amount' => 5000 - 2000,
            'currency_code' => $currencyCode,
            'status' => Loan::STATUS_DUE,
            'processed_at' => '2020-01-20',
        ]);

        // Asserting First Scheduled Repayment is Repaid
        $this->assertDatabaseHas('scheduled_repayments', [
            'id' => $scheduledRepaymentOne->id,
            'loan_id' => $loan->id,
            'amount' => 1667,
            'outstanding_amount' => 0,
            'currency_code' => $currencyCode,
            'due_date' => '2020-02-20',
            'status' => ScheduledRepayment::STATUS_REPAID,
        ]);

        // Asserting Second Scheduled Repayment is Partial
        $this->assertDatabaseHas('scheduled_repayments', [
            'id' => $scheduledRepaymentTwo->id,
            'loan_id' => $loan->id,
            'amount' => 1667,
            'outstanding_amount' => 333, // 2000 - 1667
            'currency_code' => $currencyCode,
            'due_date' => '2020-03-20',
            'status' => ScheduledRepayment::STATUS_PARTIAL,
        ]);

        // Asserting Received Repayment
        $this->assertDatabaseHas('received_repayments', [
            'loan_id' => $loan->id,
            'amount' => 2000,
            'currency_code' => $currencyCode,
            'received_at' => '2020-02-20',
        ]);
    }
}
