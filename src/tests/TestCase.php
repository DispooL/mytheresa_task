<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /**
     * @param array $expected
     * @param array $actual
     *
     * @return void
     */
    public function assertExactValidationRules(array $expected, array $actual)
    {
        $this->assertEquals($this->normalizeRules($expected), $this->normalizeRules($actual));
    }

    /**
     * @param array $rules
     *
     * @return array
     */
    private function normalizeRules(array $rules)
    {
        return array_map([$this, 'expandRules'], $rules);
    }

    /**
     * @param $rule
     *
     * @return string[]
     */
    private function expandRules($rule)
    {
        return is_string($rule) ? explode('|', $rule) : $rule;
    }
}
