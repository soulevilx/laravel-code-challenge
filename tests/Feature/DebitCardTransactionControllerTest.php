<?php

namespace Tests\Feature;

use App\Models\DebitCard;
use App\Models\DebitCardTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class DebitCardTransactionControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected DebitCard $debitCard;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->debitCard = DebitCard::factory()->create([
            'user_id' => $this->user->id
        ]);
        Passport::actingAs($this->user);
    }

    public function testCustomerCanSeeAListOfDebitCardTransactions()
    {
        DebitCardTransaction::factory(3)
            ->for($this->debitCard)
            ->create();

        // "Model Binding" should do that => https://laravel.com/docs/10.x/routing#route-model-binding
        $res = $this->getJson('api/debit-card-transactions/?debit_card_id=' . $this->debitCard->id)
            ->assertOk()
            ->decodeResponseJson()
            ->json();

        $this->assertCount(3, $res);
    }

    public function testCustomerCannotSeeAListOfDebitCardTransactionsOfOtherCustomerDebitCard()
    {
        $otherUser = User::factory()->create();
        $debitCard = DebitCard::factory()->create([
            'user_id' => $otherUser->id
        ]);

        DebitCardTransaction::factory(3)
            ->for($debitCard)
            ->create();

        $this->getJson('api/debit-card-transactions/?debit_card_id=' . $debitCard->id)
            ->assertStatus(403);
    }

    public function testCustomerCanCreateADebitCardTransaction()
    {
        $debitCardTransaction = DebitCardTransaction::factory()->make();

        $res = $this->postJson('api/debit-card-transactions/?debit_card_id=' . $this->debitCard->id, [
            'amount' => $debitCardTransaction->amount,
            'currency_code' => $debitCardTransaction->currency_code
        ])
            ->assertCreated()
            ->decodeResponseJson()
            ->json();

        // there is no "id"!
        $this->assertEquals($res['amount'], $debitCardTransaction->amount);
        $this->assertEquals($res['currency_code'], $debitCardTransaction->currency_code);

        $this->assertDatabaseHas(DebitCardTransaction::class, [
            'amount' => $res['amount'],
            'currency_code' => $res['currency_code'],
            'debit_card_id' => $this->debitCard->id
        ]);
    }

    public function testCustomerCannotCreateADebitCardTransactionToOtherCustomerDebitCard()
    {
        $debitCardTransaction = DebitCardTransaction::factory()->make();
        $debitCard = DebitCard::factory()->create();

        $this->postJson('api/debit-card-transactions/?debit_card_id=' . $debitCard->id, [
            'amount' => $debitCardTransaction->amount,
            'currency_code' => $debitCardTransaction->currency_code
        ])
            ->assertStatus(403);
    }

    public function testCustomerCanSeeADebitCardTransaction()
    {
        $debitCardTransaction = DebitCardTransaction::factory()
            ->for($this->debitCard)
            ->create();

        $res = $this->getJson('api/debit-card-transactions/' . $debitCardTransaction->id)
            ->assertOk()
            ->decodeResponseJson()
            ->json();

        // there is no "id"!
        $this->assertEquals($res['amount'], $debitCardTransaction->amount);
        $this->assertEquals($res['currency_code'], $debitCardTransaction->currency_code);
    }

    public function testCustomerCannotSeeADebitCardTransactionAttachedToOtherCustomerDebitCard()
    {
        $debitCardTransaction = DebitCardTransaction::factory()
            ->create();

        $this->getJson('api/debit-card-transactions/' . $debitCardTransaction->id)
            ->assertStatus(403);
    }

    // Extra bonus for extra tests :)
}
