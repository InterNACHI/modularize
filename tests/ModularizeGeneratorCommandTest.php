<?php

namespace InterNACHI\Modularize\Tests;

use Illuminate\Console\Application;
use Illuminate\Console\GeneratorCommand;
use Illuminate\Filesystem\Filesystem;
use InterNACHI\Modular\Support\ModuleConfig;
use InterNACHI\Modular\Support\ModuleRegistry;
use InterNACHI\Modularize\Modularize;
use InterNACHI\Modularize\ModularizeGeneratorCommand;
use Mockery\MockInterface;

class ModularizeGeneratorCommandTest extends TestCase
{
	protected MockInterface $mock;
	
	protected string $path;
	
	protected string $written;
	
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
			$fs = $this->mock(Filesystem::class);
			
			// Check if file already exists
			$fs->shouldReceive('exists')->once()->andReturn(false);
			
			// Check if containing directory exists
			$fs->shouldReceive('isDirectory')->once()->andReturn(true);
			
			// Get stub
			$fs->shouldReceive('get')->once()->with('/tmp/stub')->andReturn(
				<<<'STUB'
				class={{ class }}
				namespace={{ namespace }}
				rootNamespace={{ rootNamespace }}
				STUB
			);
			
			// Actual generator
			$fs->shouldReceive('put')
				->zeroOrMoreTimes()
				->withArgs(function($path, $written) {
					$this->path = $path;
					$this->written = $written;
					return true;
				})
				->andReturn(99);
			
			return $artisan->resolve(new class($fs) extends GeneratorCommand {
				use ModularizeGeneratorCommand;
				
				protected $name = 'modularize:test-generator';
				
				protected $type = 'Generated';
				
				protected function getStub()
				{
					return '/tmp/stub';
				}
			});
		});
	}
	
	public function test_(): void
	{
		$this->mock->shouldReceive('module')
			->with('foo')
			->atLeast()->once()
			->andReturn(new ModuleConfig('foo', '/tmp', collect(['Modules\\Foo\\'])));
		
		$this->artisan('modularize:test-generator MakeThisPlease --module=foo');
		
		$this->assertEquals(
			$this->written,
			<<<'EXPECTED'
			class=MakeThisPlease
			namespace=Modules\Foo
			rootNamespace=App\
			EXPECTED
		);
	}
}
