<?php

namespace Controllers;

use App\Http\Controllers\InvestmentController;
use App\Models\Investment;
use App\Models\Strategy;
use App\Models\User;
use Carbon\Carbon;
use Psy\Util\Str;
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

        $payload = [
            'user_id' => $user->id,
            'strategy_id' => $strategy->id,
            'successful' => $this->faker->boolean,
            'amount' => $this->faker->randomNumber(4),
        ];

        $investment = Investment::create($payload)->getAttributes();

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
        $this->assertDatabaseHas('investments', $investment);
    }

    public function testInvestmentStoredWithMissingData()
    {
        $payload = [
            '$user_id' => $this->faker->randomNumber(1, true)
        ];
        $this->json('post', 'api/investment', $payload)
            ->assertJsonStructure(['error'])
            ->assertStatus(Response::HTTP_BAD_REQUEST);
    }

    public function testInvestmentStoredWithMissingUserAndStrategy()
    {
        $payload = [
            'user_id' => 0,
            'strategy_id' => 0,
            'amount' => $this->faker->randomNumber(4, true),
        ];
        $this->json('post', 'api/investment', $payload)
            ->assertJsonStructure(['error'])
            ->assertStatus(Response::HTTP_NOT_FOUND);
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

    public function testShowWithMissingInvestment()
    {
        $this->json('get', 'api/investment/0')
            ->assertStatus(Response::HTTP_NOT_FOUND)
            ->assertJsonStructure(['error']);
    }

    public function testDestroyInvestment()
    {
        $user = User::create(User::factory()->create()->getAttributes());
        $strategy = Strategy::create(Strategy::factory()->create()->getAttributes());
        $isSuccessful = $this->faker->boolean;
        $investmentAmount = $this->faker->randomNumber(6);
        $investmentReturn = $isSuccessful ?
            $investmentAmount * $strategy->yield :
            $investmentAmount * $strategy->relief;

        $investment = Investment::create([
            'user_id' => $user->id,
            'strategy_id' => $strategy->id,
            'successful' => $isSuccessful,
            'amount' => $investmentAmount,
            'returns' => $investmentReturn,
        ]);

        $this->json('delete', "api/investment/$investment->id")
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
        //TODO: check logic behind the above line
    }

    public function testDestroyMissingInvestment()
    {
        $this->json('delete', "api/investment/0")
            ->assertJsonStructure(['error'])
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function testUpdateInvestment()
    {
        $user = User::create(User::factory()->create()->getAttributes());
        $strategy = Strategy::create(Strategy::factory()->create()->getAttributes());
        $isSuccessful = $this->faker->boolean;
        $investmentAmount = $this->faker->randomNumber(4, true);
        $investmentReturns = $isSuccessful ?
            $investmentAmount * $strategy->yield :
            $investmentAmount * $strategy->relief;

        $investment = Investment::create([
            'user_id' => $user->id,
            'strategy_id' => $strategy->id,
            'successful' => $isSuccessful,
            'amount' => $investmentAmount,
            'returns' => $investmentReturns,
        ]);

        $update_payload = [
            'successful' => $this->faker->boolean,
            'amount' => $this->faker->randomNumber(4, true)
        ];

        $this->json('put', "api/investment/$investment->id")
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
        //todo: Find out why assert the status in the above line with 401
    }

}