# Laravel Module Maker

[![Latest Version on Packagist](https://img.shields.io/packagist/v/batinmustu/laravel-module-maker.svg?style=flat-square)](https://packagist.org/packages/batinmustu/laravel-module-maker)
[![Total Downloads](https://img.shields.io/packagist/dt/batinmustu/laravel-module-maker)](https://packagist.org/packages/batinmustu/laravel-module-maker)

This project appears to be a Laravel package named laravel-module-maker developed by batinmustu.
The package is designed to facilitate the creation of modules in a Laravel application.
It provides a command-line interface for generating module stubs based on predefined templates.

The package includes a set of predefined templates (stubs) for common module structures, such as 'category'.
Users can select which stubs to generate, and the package will create the necessary files and directories based on the selected template.
The stubs can be customized by the user, and the package provides a command for publishing the stub templates for customization.

The package is installed via Composer and its configuration can be published using `php artisan module-maker:publish` command.
The configuration includes the path to the stub templates folder.

## Installation

You can install the package via composer:

```bash
composer require batinmustu/laravel-module-maker
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="laravel-module-maker-config"
```

This is the contents of the published config file:

```php
return [
    'stub_template_folder' => resource_path('stubs/modules'),
];
```

Optionally, you can publish the stub templates using

```bash
php artisan module-maker:publish
```

## Core Stub Templates
The Laravel Module Maker package comes with a set of predefined templates (stubs) to help you generate modules quickly. Here are the core stub templates provided by the package:

| Template Name | Template Key | Stubs                                                                                                   |
|---------------|--------------|---------------------------------------------------------------------------------------------------------|
| Category      | category     | [Go to folder](https://github.com/batinmustu/laravel-module-maker/tree/master/resources/stubs/category) |

## Generate a New Module
To generate a new module using the Laravel Module Maker package, you can use the `module-maker` command provided by the package. Here's a step-by-step guide

1. Open your terminal and navigate to your Laravel project directory.
2. Run the `module-maker` command followed by the name of the module you want to create. The module name should be in StudlyCase (e.g., BlogCategory). You can also specify the template you want to use with the `--template` option. For example, if you want to use the 'category' template, you can do so like this:
   ```bash
   php artisan module-maker BlogCategory --template=category
   ```
3. The command will then ask you to select the stubs you want to generate. You can select multiple stubs by using `space` and clicking on the stubs you want to generate.
4. If the `--accept-risk` option is not set, the command will warn you about the potential risks of overwriting existing files that use the same path as the template stubs. You can choose to proceed or cancel the operation.
5. Once you confirm, the command will generate the module based on the selected template and stubs.

> **Note:** Remember, you can customize the stub templates by publishing them to your Laravel project and modifying them in the `resources/stubs/modules` directory.

## Customize The Stub Templates in The Laravel Module Maker

To customize the stub templates in the Laravel Module Maker package, you can follow these steps:

1. **Publish the stub templates:** The package provides a command to publish the stub templates. This will copy the stub templates from the package to your Laravel project. You can run this command in your terminal:
    ```bash
    php artisan module-maker:publish
    ```
2. **Locate the stub templates:** After publishing, the stub templates will be located in the `resources/stubs/modules` directory of your Laravel project, as specified in the `laravel-module-maker.php` configuration file.
3. **Customize the stub templates:** You can now customize the stub templates in this directory. You can modify the existing stubs or create new ones based on your requirements.

### Stub Template Parameters
The stub templates in the Laravel Module Maker package support parameters that can be replaced with actual values when generating the module. You can use these parameters in the stub files to dynamically generate content based on the module name and other inputs.

Here are the available parameters that you can use in the stub templates:

- `__Module___`: Replace module name with underscores. (e.g. `BlogCategory` becomes `Blog_Category`)
- `__Modules___`: Replace a plural form of the module name with underscores. (e.g. `BlogCategory` becomes `Blog_Categories`)
- `__module___`: Replace the module name in a format called snake case. (e.g. `BlogCategory` becomes `blog_category`)
- `__modules___`: Replace a plural form of the module name in snake case. (e.g. `BlogCategory` becomes `blog_categories`)
- `__Module__`: Replace the module name as it is. (e.g. `BlogCategory` stays as `BlogCategory`)
- `__Module-__`: Replace the module name with hyphens. (e.g. `BlogCategory` becomes `Blog-Category`)
- `__Module __`: Replace the module name in a format called headline case. (e.g. `BlogCategory` becomes `Blog Category`)
- `__Modules__`: Replace a plural form of the module name. (e.g. `BlogCategory` becomes `BlogCategories`)
- `__Modules-__`: Replace a plural form of the module name with hyphens. (e.g. `BlogCategory` becomes `Blog-Categories`)
- `__Modules __`: Replace a plural form of the module name in headline case. (e.g. `BlogCategory` becomes `Blog Categories`)
- `__module__`: Replace the module name in a format called camel case. (e.g. `BlogCategory` becomes `blogCategory`)
- `__module-__`: Replace the module name with hyphens. (e.g. `BlogCategory` becomes `blog-category`)
- `__module __`: Replace the module name with spaces. (e.g. `BlogCategory` becomes `blog category`)
- `__modules__`: Replace a plural form of the module name in camel case. (e.g. `BlogCategory` becomes `blogCategories`)
- `__modules-__`: Replace a plural form of the module name with hyphens. (e.g. `BlogCategory` becomes `blog-categories`)
- `__modules __`: Replace a plural form of the module name with spaces. (e.g. `BlogCategory` becomes `blog categories`)
- `__Namespace__`: Replace a namespace from a given path. (e.g. if your path is `app/Http/Controllers`, it will be transformed to `App\Http\Controllers`)
- `__migration__`: Replace a timestamp for migrations. (e.g. it could be something like `2023_12_31_235959`)

You can use these parameters in your stub files to generate dynamic content and file / folder name based on the module name and other inputs.

## What is The Blueprint File Used For?
The blueprint file, `module-blueprint.yml`, is a file that contains the structure of the module you want to generate. You can provide the module name, template, and stubs you want to generate in this file. The Laravel Module Maker package uses this file to generate the module based on the specified structure.

Here's an example of a `module-blueprint.yml` file:

```yaml
# module-blueprint.yml
BlogCategory:
   template: 'category'
   excludeStubs:
      - 'app/Http/Controllers/__Module__Controllers/__Module__Controller.php.stub'
NewsCategory:
   template: 'category'
```

In this example, we have two modules, `BlogCategory` and `NewsCategory`. Both modules use the `category` template to generate the module structure. However, we exclude the `__Module__Controller.php.stub` stub file from the `BlogCategory` module.

You can create a `module-blueprint.yml` file in the root directory of your Laravel project and specify the modules you want to generate along with the template and stubs you want to exclude.

You can then run the `module-maker:blueprint` command to generate the modules based on the blueprint file:


## Credits

- [batinmustu](https://github.com/batinmustu)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
