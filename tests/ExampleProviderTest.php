<?php

namespace App\tests;

use App\Client\DefaultClient;
use App\ExampleProvider;
use PHPUnit\Framework\TestCase;

class ExampleProviderTest extends TestCase
{
    protected $provider;
    protected $expected;

    protected function setUp(): void
    {
        $stub           = $this->createMock(DefaultClient::class);
        $this->expected = [['id' => 1, 'author' => '1', 'text' => '1'], ['id' => 2, 'author' => '2', 'text' => '2']];
        $stub->method('execute')
            ->willReturn($this->expected);
        $this->provider = new ExampleProvider();
    }

    public function testGet()
    {
        $this->assertSame($this->expected, $this->provider->getExamples());
    }

    public function testPost()
    {
        $this->assertSame($this->expected, $this->provider->addExample(['id' => 2, 'author' => '2', 'text' => '2']));
    }

    public function testPut()
    {
        $this->assertSame($this->expected, $this->provider->updateExample(2, ['author' => '2']));
    }
}
