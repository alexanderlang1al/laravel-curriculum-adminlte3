<?php

namespace Tests\Unit;

use App\State;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StateTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_a_country()
    {
        $state = State::findOrFail('DE-RP'); //country_code == 'DE'

        $this->assertInstanceOf('App\Country', $state->country()->first());
    }
}
