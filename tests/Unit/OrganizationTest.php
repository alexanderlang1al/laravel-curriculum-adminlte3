<?php

namespace Tests\Unit;

use App\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationTest extends TestCase
{
    use RefreshDatabase;

    protected $organization;

    public function setUp(): void
    {
        parent::setUp();
        $this->organization = Organization::first();
    }

    /** @test */
    public function it_has_a_state()
    {
        $this->assertInstanceOf('App\State', $this->organization->state()->first());
    }

    /** @test */
    public function it_has_a_country()
    {
        $this->assertInstanceOf('App\Country', $this->organization->country()->first());
    }

    /** @test */
    public function it_has_a_organization_type()
    {
        $this->assertInstanceOf('App\OrganizationType', $this->organization->type()->first());
    }

    /** @test */
    public function it_has_a_status()
    {
        $this->assertInstanceOf('App\StatusDefinition', $this->organization->status()->first());
    }
}
