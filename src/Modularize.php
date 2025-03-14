<?php

namespace InterNACHI\Modularize;

use Illuminate\Contracts\Container\BindingResolutionException;
use InterNACHI\Modular\Support\ModuleConfig;
use InterNACHI\Modular\Support\ModuleRegistry;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Symfony\Component\Console\Input\InputOption;

/** @mixin \Illuminate\Console\Command */
trait Modularize
{
	/**
	 * Get the module configuration for the requested module
	 *
	 * @return \InterNACHI\Modular\Support\ModuleConfig|null
	 */
	protected function module(): ?ModuleConfig
	{
		if ($name = $this->option('module')) {
			try {
				$registry = $this->getLaravel()->make(ModuleRegistry::class);
			} catch (BindingResolutionException) {
				throw new InvalidOptionException('You must have "internachi/modular" installed to use the --module option.');
			}
			
			if ($module = $registry->module($name)) {
				return $module;
			}
			
			throw new InvalidOptionException(sprintf('The "%s" module does not exist.', $name));
		}
		
		return null;
	}
	
	/**
	 * Register the --module option during command configuration
	 */
	protected function configure()
	{
		parent::configure();
		
		$this->getDefinition()->addOption(
			new InputOption(
				'--module',
				null,
				InputOption::VALUE_REQUIRED,
				'Run inside an application module'
			)
		);
	}
}
