<?php

namespace Tests\Feature;

use App\Models\DebitCard;
use App\Models\DebitCardTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Laravel\Passport\Passport;
use Tests\TestCase;

class DebitCardControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Passport::actingAs($this->user);
    }

    public function testCustomerCanSeeAListOfDebitCards(): void
    {
        $userDebitCards = DebitCard::factory()
            ->active()
            ->count(2)
            ->create([
                'user_id' => $this->user->id,
            ]);

        $response = $this->get('api/debit-cards');

        $response->assertStatus(200);
        $response->assertJsonCount(count($userDebitCards));

        $response->assertJson(fn (AssertableJson $json) =>
        $json->first(
            fn ($json) =>
            $json
                ->whereType('id', 'integer')
                ->whereType('number', 'string')
                ->whereType('type', 'string')
                ->whereType('expiration_date', 'string')
                ->whereType('is_active', 'boolean')
        ));
    }

    public function testCustomerCannotSeeAListOfDebitCardsOfOtherCustomers(): void
    {
        $anotherUser = User::factory()->create();
        DebitCard::factory()
            ->active()
            ->count(2)
            ->create([
                'user_id' => $anotherUser->id,
            ]);

        $response = $this->get('api/debit-cards');

        $response->assertStatus(200);
        $response->assertJsonCount(0);
    }

    public function testCustomerCanCreateADebitCard(): void
    {
        $response = $this->postJson('api/debit-cards', [
            'type' => 'testCard'
        ]);

        $response->assertStatus(201);

        $response->assertJson(fn (AssertableJson $json) =>
            $json
                ->whereType('id', 'integer')
                ->whereType('number', 'integer')
                ->whereType('type', 'string')
                ->whereType('expiration_date', 'string')
                ->whereType('is_active', 'boolean')
        );

        $this->assertDatabaseHas('debit_cards', [
            'type' => 'testCard'
        ]);
    }

    public function testCustomerCanSeeASingleDebitCardDetails(): void
    {
        $userDebitCards = DebitCard::factory()
            ->active()
            ->count(2)
            ->create([
                'user_id' => $this->user->id,
            ]);

        $response = $this->get('api/debit-cards/' . $userDebitCards->first()->id);

        $response->assertStatus(200);
        $response->assertJson(fn (AssertableJson $json) =>
        $json
            ->whereType('id', 'integer')
            ->whereType('number', 'string')
            ->whereType('type', 'string')
            ->whereType('expiration_date', 'string')
            ->whereType('is_active', 'boolean')
        );

        $response->assertJson(fn ($json) => $json->where('number', $userDebitCards->first()->number)->etc());
    }

    public function testCustomerCannotSeeASingleDebitCardDetails(): void
    {
        $anotherUser = User::factory()->create();
        DebitCard::factory()
            ->active()
            ->count(2)
            ->create([
                'user_id' => $anotherUser->id,
            ]);

        $response = $this->get('api/debit-cards/' . $anotherUser->first()->id);

        $response->assertStatus(403);
    }

    public function testCustomerCanActivateADebitCard(): void
    {
        // First create a disabled debit card and make sure it was disabled correctly
        $userDebitCard = DebitCard::factory()
            ->active()
            ->create([
                'user_id' => $this->user->id,
                'disabled_at' => date('Y-m-d H:i:s')
            ]);

        $response = $this->get('api/debit-cards/' . $userDebitCard->first()->id);

        $response->assertStatus(200);
        $response->assertJson(
            fn ($json) => $json->where('is_active', false)->etc()
        );

        // Second set card to active and check result
        $response = $this->putJson('api/debit-cards/' . $userDebitCard->first()->id, [
            'is_active' => true
        ]);

        $response->assertJson(
            fn ($json) => $json
                ->where('is_active', true)
                ->where('number', $userDebitCard->first()->number)
                ->etc()
        );
    }

    public function testCustomerCanDeactivateADebitCard(): void
    {
        $userDebitCard = DebitCard::factory()
            ->active()
            ->create([
                'user_id' => $this->user->id
            ]);

        $response = $this->putJson('api/debit-cards/' . $userDebitCard->first()->id, [
            'is_active' => false
        ]);

        $response->assertJson(
            fn ($json) => $json
                ->where('is_active', false)
                ->where('number', $userDebitCard->first()->number)
                ->etc()
        );
    }

    public function testCustomerCannotUpdateADebitCardWithWrongValidation()
    {
        $userDebitCard = DebitCard::factory()
            ->active()
            ->create([
                'user_id' => $this->user->id
            ]);

       $response = $this->putJson('api/debit-cards/' . $userDebitCard->first()->id, [
            'number' => false
        ]);

       $response->assertStatus(422);
    }

    public function testCustomerCanDeleteADebitCard()
    {
        $userDebitCard = DebitCard::factory()
            ->active()
            ->create([
                'user_id' => $this->user->id
            ]);

        $this->deleteJson('api/debit-cards/' . $userDebitCard->id);

        $this->assertSoftDeleted('debit_cards', [
            'id' => $userDebitCard->id,
            'user_id' => $userDebitCard->user->id,
        ]);
    }

    public function testCustomerCannotDeleteADebitCardWithTransaction()
    {
        $userDebitCard = DebitCard::factory()->active()->create([
            'user_id' => $this->user->id,
        ]);

        DebitCardTransaction::factory()->create([
            'debit_card_id' => $userDebitCard->id
        ]);

        $response = $this->deleteJson('api/debit-cards/' . $userDebitCard->id);

        $response->assertStatus(403);
    }

    // Extra bonus for extra tests :)
}
