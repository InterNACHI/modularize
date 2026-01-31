<div style="float: right;">
	<a href="https://github.com/internachi/modularize/actions" target="_blank">
		<img
			src="https://github.com/internachi/modularize/workflows/PHPUnit/badge.svg"
			alt="Build Status"
		/>
	</a>
	<a href="https://packagist.org/packages/internachi/modularize" target="_blank">
        <img
            src="https://poser.pugx.org/internachi/modularize/v/stable"
            alt="Latest Stable Release"
        />
	</a>
	<a href="./LICENSE" target="_blank">
        <img
            src="https://poser.pugx.org/internachi/modularize/license"
            alt="MIT Licensed"
        />
    </a>
</div>

# Modularize

Traits for package authors to add [internachi/modular](https://github.com/internachi/modular) support to their Laravel commands.

## Installation

```bash
composer require internachi/modularize
```

## Usage

Add the `Modularize` trait to your package commands:

```php
use Illuminate\Console\Command;
use InterNACHI\Modularize\Support\Modularize;

class SomeCommand extends Command
{
    use Modularize;

    public function handle()
    {
        if ($module = $this->module()) {
            // Command was called with --module, $module is a ModuleConfig class
            // with name, base path, namespaces, and helper methods.
        }
    }
}
```

If you're using Laravel file generator commands, add the `ModularizeGeneratorCommand` trait:

```php
use Illuminate\Console\GeneratorCommand;
use InterNACHI\Modularize\Support\ModularizeGeneratorCommand;

class MakeWidget extends GeneratorCommand
{
    use ModularizeGeneratorCommand;

    // ...
}
```

This adds a `--module` option to your command. When used, generated files are placed in the module directory with correct namespacing.
