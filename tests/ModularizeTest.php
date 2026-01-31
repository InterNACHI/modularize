<?php

namespace InterNACHI\Modularize\Tests;

use Closure;
use Illuminate\Console\Application;
use Illuminate\Console\Command;
use InterNACHI\Modular\Support\ModuleConfig;
use InterNACHI\Modular\Support\ModuleRegistry;
use InterNACHI\Modularize\Modularize;
use Mockery\MockInterface;
use Symfony\Component\Console\Exception\InvalidOptionException;

class ModularizeTest extends TestCase
{
	protected MockInterface $mock;
	
	protected Closure $callback;
	
	protected function setUp(): void
	{
		parent::setUp();
		
		$this->callback = fn() => $this->module();
		
		$this->app->instance(
			ModuleRegistry::class,
			$this->mock(ModuleRegistry::class, function(MockInterface $mock) {
				$this->mock = $mock;
			})
		);
		
		Application::starting(function(Application $artisan) {
			return $artisan->resolve(new class($this->callback) extends Command {
				use Modularize;
				
				protected $name = 'modularize:test';
				
				public function __construct(protected Closure $callback)
				{
					parent::__construct();
				}
				
				public function handle()
				{
					$this->callback->call($this);
				}
			});
		});
	}
	
	public function test_it_looks_up_module_when_option_is_passed(): void
	{
		$expected = new ModuleConfig('foo', '/tmp');
		$actual = null;
		
		$this->callback = function() use (&$actual) {
			$actual = $this->module();
		};
		
		$this->mock->shouldReceive('module')
			->with('foo')
			->once()
			->andReturn($expected);
		
		$this->artisan('modularize:test --module=foo');
		
		$this->assertSame($expected, $actual);
	}
	
	public function test_it_throw_an_exception_on_an_invalid_option(): void
	{
		$this->mock->shouldReceive('module')
			->with('foo')
			->once()
			->andReturnNull();
		
		$this->expectException(InvalidOptionException::class);
		
		$this->artisan('modularize:test --module=foo');
	}
	
	public function test_it_returns_null_when_no_module_is_passed(): void
	{
		$actual = false;
		
		$this->callback = function() use (&$actual) {
			$actual = $this->module();
		};
		
		$this->mock->shouldNotReceive('module');
		
		$this->artisan('modularize:test');
		
		$this->assertNull($actual);
	}
}
