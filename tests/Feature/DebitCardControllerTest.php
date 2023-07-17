<?php

namespace Tests\Feature;

use App\Models\DebitCard;
use App\Models\DebitCardTransaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    public function testCustomerCanSeeAListOfDebitCards()
    {
        DebitCard::factory(3)->create([
            'user_id' => $this->user->id,
            'disabled_at' => null
        ]);

        $this->getJson('/api/debit-cards')
            ->assertOk();

        $res = $this->getJson('/api/debit-cards')
            ->assertStatus(200)
            ->decodeResponseJson();

        $this->assertEquals(3, $res->count());
    }

    public function testCustomerCannotSeeAListOfDebitCardsOfOtherCustomers()
    {
        $otherUser = User::factory()->create();

        DebitCard::factory(2)->create([
            'user_id' => $this->user->id,
            'disabled_at' => null
        ]);

        $otherUserDebitCards = DebitCard::factory(2)->create([
            'user_id' => $otherUser->id,
            'disabled_at' => null
        ]);

        $otherUserDebitCardIds = $otherUserDebitCards->pluck('id')->toArray();

        $res = $this->getJson('/api/debit-cards')
            ->assertOk()
            ->decodeResponseJson();

        $this->assertEquals(2, $res->count());
        $this->assertTrue(! in_array(array_column($res->json(), 'id'), $otherUserDebitCardIds));
    }

    public function testCustomerCanCreateADebitCard()
    {
        $now = now();
        Carbon::setTestNow($now);

        $debitCard = DebitCard::factory()->make();

        $this->postJson('api/debit-cards')
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('type');

        $data = $this->postJson('api/debit-cards', $debitCard->toArray())
            ->assertCreated()
            ->decodeResponseJson()
            ->json();

        // Number cannot be tested because it is randomly generated
        $this->assertNotEmpty($data['id']);
        $this->assertEquals($data['type'], $debitCard->type);
        $this->assertEquals(
            Carbon::parse($data['expiration_date'])->format('Y-m-d H:i:s'),
            $now->addYear()->format('Y-m-d H:i:s')
        );

        $this->assertDatabaseHas(DebitCard::class, [
            'id' => $data['id'],
            'type' => $data['type'],
            'number' => $data['number'],
            'disabled_at' => null,
            'expiration_date' => $now
        ]);
    }

    public function testCustomerCanSeeASingleDebitCardDetails()
    {
        $debitCard = DebitCard::factory()->create([
            'user_id' => $this->user->id
        ]);

        $data = $this->getJson('api/debit-cards/' . $debitCard->id)
            ->assertOk()
            ->decodeResponseJson()
            ->json();

        $this->assertEquals($data['id'], $debitCard->id);
        $this->assertEquals($data['number'], $debitCard->number);
        $this->assertEquals($data['type'], $debitCard->type);
        $this->assertEquals($data['expiration_date'], $debitCard->expiration_date->format('Y-m-d H:i:s'));
        $this->assertEquals($data['is_active'], $debitCard->is_active);
    }

    public function testCustomerCannotSeeASingleDebitCardDetails()
    {
        $otherUser = User::factory()->create();
        $debitCard = DebitCard::factory()->create([
            'user_id' => $otherUser->id
        ]);

        $this->getJson('api/debit-cards/' . $debitCard->id)
            ->assertStatus(403);
    }

    public function testCustomerCanActivateADebitCard()
    {
        $debitCard = DebitCard::factory()->create([
            'user_id' => $this->user->id,
            'disabled_at' => now()
        ]);

        $this->assertFalse($debitCard->is_active);

        $res = $this->putJson('api/debit-cards/' . $debitCard->id, ['is_active' => true])
            ->assertOk()
            ->decodeResponseJson()
            ->json();

        $this->assertTrue($res['is_active']);

        $this->assertDatabaseHas(DebitCard::class, [
            'id' => $debitCard->id,
            'disabled_at' => null
        ]);
    }

    public function testCustomerCanDeactivateADebitCard()
    {
        $now = now();
        Carbon::setTestNow($now);

        $debitCard = DebitCard::factory()->create([
            'user_id' => $this->user->id,
            'disabled_at' => null
        ]);

        $this->assertTrue($debitCard->is_active);

        $res = $this->putJson('api/debit-cards/' . $debitCard->id, ['is_active' => false])
            ->assertOk()
            ->decodeResponseJson()
            ->json();

        $this->assertFalse($res['is_active']);

        $this->assertDatabaseHas(DebitCard::class, [
            'id' => $debitCard->id,
            'disabled_at' => $now
        ]);
    }

    public function testCustomerCannotUpdateADebitCardWithWrongValidation()
    {
        $debitCard = DebitCard::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $this->putJson('api/debit-cards/' . $debitCard->id)
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('is_active');
    }

    public function testCustomerCanDeleteADebitCard()
    {
        $debitCard = DebitCard::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $this->deleteJson('api/debit-cards/' . $debitCard->id)
            ->assertNoContent();

        $this->assertSoftDeleted(DebitCard::class, ['id' => $debitCard->id]);
    }

    public function testCustomerCannotDeleteADebitCardWithTransaction()
    {
        $debitCard = DebitCard::factory()->create([
            'user_id' => $this->user->id,
        ]);
        DebitCardTransaction::factory()
            ->for($debitCard)
            ->create();

        $this->deleteJson('api/debit-cards/' . $debitCard->id)
            ->assertStatus(403);
    }

    // Extra bonus for extra tests :)
    // @todo Authentication || datetime => timestamp
    // DebitCtrl::47 !== DebitCardModel::$expiration_date
}
