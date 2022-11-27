<?php

namespace Tests\Feature;

use App\Models\User;
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
        $response = $this->actingAs($this->user)->get('/debit-cards');    
        $response->assertStatus(200); 
    }
    /*
    public function testCustomerCannotSeeAListOfDebitCardsOfOtherCustomers()
    {
        // get /debit-cards
    }
    */
    
    public function testCustomerCanCreateADebitCard()
    {
        $response = $this->actingAs($this->user)->post('/debit-cards', ['type' => 'MC']);    
        $response->assertStatus(201);
    }
    
    public function testCustomerCanSeeASingleDebitCardDetails()
    {
        // get api/debit-cards/{debitCard}
        $response = $this->actingAs($this->user)->get('/api/debit-cards/1');    
        $response->assertStatus(200);

    }
    /*
    public function testCustomerCannotSeeASingleDebitCardDetails()
    {
        // get api/debit-cards/{debitCard}
        $response = $this->actingAs($this->user)->post('/debit-cards', ['type' => 'MC']);    
        $response->assertStatus(201);
    }
    */
    public function testCustomerCanActivateADebitCard()
    {
        // put api/debit-cards/{debitCard}
        $response = $this->actingAs($this->user)->put('/api/debit-cards/1', ['is_active' => '1']);  
        $response->assertStatus(200);  
    }
    
    public function testCustomerCanDeactivateADebitCard()
    {
        // put api/debit-cards/{debitCard}
        $response = $this->actingAs($this->user)->put('/api/debit-cards/1', ['is_active' => '0']);  
        $response->assertStatus(200); 
    }
    /*
    public function testCustomerCannotUpdateADebitCardWithWrongValidation()
    {
        // put api/debit-cards/{debitCard}
    }

    public function testCustomerCanDeleteADebitCard()
    {
        // delete api/debit-cards/{debitCard}
    }

    public function testCustomerCannotDeleteADebitCardWithTransaction()
    {
        // delete api/debit-cards/{debitCard}
    }
    */
    // Extra bonus for extra tests :)
}
