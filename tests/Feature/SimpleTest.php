<?php

namespace Tests\Feature;

use Tests\TestCase;

class SimpleTest extends TestCase
{
    public function test_first(): void
    {
        $this->assertTrue(true);
    }

    public function test_second(): void
    {
        $this->assertEquals(2, 1 + 1);
    }

    public function test_third(): void
    {
        $this->assertNotEmpty('hello');
    }
}