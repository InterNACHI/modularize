<?php

namespace InterNACHI\Modularize;

use Illuminate\Support\Str;

/**
 * @mixin \Illuminate\Console\Command
 * @mixin \Illuminate\Console\GeneratorCommand
 */
trait ModularizeGeneratorCommand
{
	use Modularize;
	
	/**
	 * Get the default namespace for the class, injecting the module namespace when appropriate.
	 * 
	 * @param string $rootNamespace
	 * @return string
	 */
	protected function getDefaultNamespace($rootNamespace)
	{
		$namespace = parent::getDefaultNamespace($rootNamespace);
		
		if ($module = $this->module()) {
			if (! str_contains($rootNamespace, $module->namespaces->first())) {
				$find = rtrim($rootNamespace, '\\');
				$replace = rtrim($module->namespaces->first(), '\\');
				$namespace = str_replace($find, $replace, $namespace);
			}
		}
		
		return $namespace;
	}
	
	/**
	 * Only format class according to root namespace when outside a module.
	 *
	 * @param string $name
	 * @return string
	 */
	protected function qualifyClass($name)
	{
		$name = str_replace('/', '\\', ltrim($name, '\\/'));
		
		if ($module = $this->module()) {
			if (str_starts_with($name, $module->namespaces->first())) {
				return $name;
			}
		}
		
		return parent::qualifyClass($name);
	}
	
	/**
	 * Qualify the given model class base name.
	 *
	 * @param string $model
	 * @return string
	 */
	protected function qualifyModel(string $model)
	{
		if ($module = $this->module()) {
			$model = str_replace('/', '\\', ltrim($model, '\\/'));
			
			if (str_starts_with($model, $module->namespace())) {
				return $model;
			}
			
			return $module->qualify('Models\\'.$model);
		}
		
		return parent::qualifyModel($model);
	}
	
	/**
	 * Get the destination class path.
	 *
	 * @param string $name
	 * @return string
	 */
	protected function getPath($name)
	{
		if ($module = $this->module()) {
			$name = Str::replaceFirst($module->namespaces->first(), '', $name);
		}
		
		$path = parent::getPath($name);
		
		if ($module) {
			// Set up our replacements as a [find -> replace] array
			$replacements = [
				$this->laravel->path() => $module->namespaces->keys()->first(),
				$this->laravel->basePath('tests/Tests') => $module->path('tests'),
				$this->laravel->databasePath() => $module->path('database'),
			];
			
			// Normalize all our paths for compatibility's sake
			$normalize = static fn($path) => rtrim($path, '/').'/';
			
			$find = array_map($normalize, array_keys($replacements));
			$replace = array_map($normalize, array_values($replacements));
			
			// And finally apply the replacements
			$path = str_replace($find, $replace, $path);
		}
		
		return $path;
	}
	
	/**
	 * Call another console command, passing the module flag if it's set.
	 *
	 * @param \Symfony\Component\Console\Command\Command|string $command
	 * @param array $arguments
	 * @return int
	 */
	public function call($command, array $arguments = [])
	{
		if ($module = $this->option('module')) {
			$arguments['--module'] = $module;
		}
		
		return $this->runCommand($command, $arguments, $this->output);
	}
}
