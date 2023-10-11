<?php

namespace Controllers;

use Illuminate\Http\Response;
use Tests\TestCase;

class StrategyControllerTests extends TestCase
{
    //index returns data in valid format
    //shown correctly
    public function testStrategyIsShownCorrectly(){
        $this->json('get','api/strategy')
            ->assertStatus(Response::HTTP_OK)
            ->assertExactJson([
                'data'=>[
                    'id',
                    ''
                ]
            ])
    }

    //storedcorrectly
    //updatedcorrectly
    //deletedcorrectly
}