<?php

namespace Controllers;

use App\Models\Investment;
use App\Models\Strategy;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Response;
use Tests\TestCase;

class UserControllerTests extends TestCase
{
    public function testIndexReturnsDataInValidFormat()
    {
        //procedure for get requests
        //test api call
        //check data format

        $this->json('get', 'api/user')
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure(
                [
                    'data' => [
                        '*' => [
                            'id',
                            'first_name',
                            'last_name',
                            'email',
                            'wallet' => [
                                'id',
                                'balance'
                            ]
                        ]
                    ]
                ]
            );
    }

    public function testUserIsCreatedSuccessfully()
    {
        //below format is applicable for post requests
        //create the data first to be used in testing
        //run the api call
        //check db

        $payload = [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->firstName,
            'email' => $this->faker->firstName,
        ];

        $this->json('post', 'api/user', $payload)
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJsonStructure(
                [
                    'data' => [
                        'id',
                        'first_name',
                        'last_name',
                        'email' .
                        'created_at',
                        'wallet' => [
                            'id', 'balance'
                        ]
                    ]
                ]
            );
        $this->assertDatabaseHas('users', $payload);
    }

    public function userIsShownCorrectly()
    {
        //every user has their own wallet
        $user = User::create(
            [
                'first_name' => $this->faker->firstName,
                'last_name' => $this->faker->lastName,
                'email' => $this->faker->email,
            ]
        );

        $wallet = Wallet::create([
            'user_id' => $user->id,
            'balance' => 0,
        ]);

        $this->json('get', "api/user/$user->id")
            ->assertStatus(Response::HTTP_OK)
            ->assertExactJson([
                'data' => [
                    'id' => $user->id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                    'created_at' => (string)$user->create_at,
                    'wallet' => [
                        'id' => $user->wallet->id,
                        'balance' => $user->wallet->balance
                    ]
                ]
            ]);
    }

    public function testUserIsDestroyed()
    {
        //create sample data
        //attempt writing the data to db
        //call api to delete data
        //assert that there is no data in db


        $userData = [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'email' => $this->faker->email,
        ];
        $user = User::create($userData);

        $this->json('delete', "api/user/$user->id")
            ->assertNoContent();
        $this->assertDatabaseMissing('users', $userData);
    }

    public function testUpdateUserReturnsCorrectData()
    {
        //create a user plus wallet (data)
        //create data to update initial data i.e payload
        //call api with the payload
        //check exact response format

        $user = User::create(
            [
                'first_name' => $this->faker->firstName,
                'last_name' => $this->faker->lastName,
                'email' => $this->faker->email,
            ]
        );
        Wallet::create(
            [
                'user_id' => $user->id,
                'balance' => 0
            ]
        );

        $payload = [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'email' => $this->faker->email,
        ];


        $this->json('put', "api/update/$user->id", $payload)
            ->assertStatus(Response::HTTP_OK)
            ->assertExactJson([
                'data' => [
                    'id' => $user->id,
                    'first_name' => $payload->firstName,
                    'last_name' => $payload->lastName,
                    'email' => $payload->email,
                    'created_at' => (string)$user->created_at,
                    'wallet' => [
                        'id' => $user->wallet->id,
                        'balance' => $user->wallet->balance
                    ]
                ]
            ]);
    }

    public function testGetUserInvestmentsForUser()
    {
        //create user data
        //create dummy Startegy data
        //create some more for the investment fields
        //test call to api

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

        $isSuccessful = $this->faker->boolean;
        $investmentAmount = $this->faker->randomNumber(6);
        $investmentReturns = $isSuccessful ?
            $investmentAmount * $strategy->yield :
            $investmentAmount * $strategy->relief;

        $investment = Investment::create(
            [
                'user_id' => $user->id,
                'strategy_id' => $strategy->id,
                'successful' => $isSuccessful,
                'amount' => $investmentAmount,
                'returns' => $investmentReturns
            ]
        );
        $this->json('get', "api/$user->id/investments")
            ->assertStatus(Response::HTTP_OK)
            ->assertJson(
                [
                    'data' => [
                        'id' => $investment->id,
                        'user_id' => $investment->user->id,
                        'strategy' => $investment->strategy->id,
                        'successful' => (bool)$investment->successful,
                        'amount' => $investment->amount,
                        'returns' => $investment->returns,
                        'created_at' => (string)$investment->created_at,
                    ]
                ]
            );
    }

}
