<?php

namespace Tests\Unit;

use App\Period;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PeriodTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_an_owner()
    {
        $this->signInAdmin();
        $period = Period::findOrFail(1);

        $this->assertInstanceOf('App\USER', $period->owner()->first());
    }

    /** @test */
    public function it_has_a_period()
    {
        $this->signInAdmin();
        $period = Period::findOrFail(1);

        $this->assertInstanceOf('App\Organization', $period->organization()->first());
    }
}
