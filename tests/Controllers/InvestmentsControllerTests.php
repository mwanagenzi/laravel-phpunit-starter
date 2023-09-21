<?php

namespace Controllers;

use App\Models\Investment;
use App\Models\Strategy;
use App\Models\User;
use Tests\TestCase;
use \Illuminate\Http\Response;

class InvestmentsControllerTests extends TestCase
{
    public function testIndexReturnsDataInValidFormat()
    {
        $this->json('get', 'api/investment')
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'user_id',
                        'strategy_id',
                        'successful',
                        'amount',
                        'returns',
                        'created_at'
                    ]
                ]
            ]);

    }

    public function testInvestmentIsCreatedSuccessfully()
    {
        //dummy data to store
        //call api
        //assert json structure
        //assert data in db
        $user = User::create(
            [
                'first_name' => $this->faker->firstName,
                'last_name' => $this->faker->lastName,
                'email' => $this->faker->email,
            ]
        );

        $strategy = Strategy::create(
            Strategy::factory()->create()->getAttributes()
        );

        $amount = $this->faker->randomNumber(6);

        $payload = [
            'user_id' => $user->id,
            'strategy_id' => $strategy->id,
            'amount' => $amount
        ];

        $this->json('post', 'api/investment', $payload)
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJsonStructure(
                [
                    'data' => [
                        'id',
                        'user_id',
                        'strategy_id',
                        'successful',
                        'amount',
                        'returns',
                        'created_at'
                    ]
                ]
            );
        $this->assertDatabaseHas('investments', $payload);
    }

    public function testInvestmentIsShownCorrectly()
    {
        $user = User::create(User::factory()->make()->getAttributes());
        $strategy = Strategy::create(Strategy::factory()->make()->getAttributes());

        $isSuccessful = $this->faker->boolean;
        $investmentAmount = $this->faker->randomNumber(6);
        $investmentReturn = $isSuccessful ?
            $investmentAmount * $strategy->yield :
            $investmentAmount * $strategy->relief;

        $investment = Investment::create(
            [
                'user_id' => $user->id,
                'strategy_id' => $strategy->id,
                'successful' => $isSuccessful,
                'amount' => $investmentAmount,
                'returns' => $investmentReturn,
            ]
        );

        $this->json('get', "api/investment/$investment->id")
            ->assertStatus(Response::HTTP_OK)
            ->assertExactJson([
                'data' => [
                    'id' => $investment->id,
                    'user_id' => $user->id,
                    'strategy_id' => $strategy->id,
                    'successful' => (bool)$isSuccessful,
                    'amount' => round($investment->amount, 2, PHP_ROUND_HALF_UP),
                    'returns' => round($investment->returns, 2, PHP_ROUND_HALF_UP),
                    'created_at' => (string)$investment->created_at,
                ]
            ]);
    }

}