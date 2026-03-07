# Chat Field

[![Latest Version on Packagist](https://img.shields.io/packagist/v/bergstar/chat-field.svg?style=flat-square)](https://packagist.org/packages/bergstar/chat-field)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/bergstar/chat-field/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/bergstar/chat-field/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/bergstar/chat-field/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/bergstar/chat-field/actions?query=workflow%3A"Fix+PHP+code+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/bergstar/chat-field.svg?style=flat-square)](https://packagist.org/packages/bergstar/chat-field)

`bergstar/chat-field` provides a Filament 5 form field that renders a record-attached chat window directly inside a form. It stores a single thread per owner record, supports message text and file attachments, and automatically resolves the deepest saved singular relationship record when the field is nested inside a relationship section.

## Installation

You can install the package via composer:

```bash
composer require bergstar/chat-field
```

If you use Filament Panels with a custom theme, add the package views to your theme CSS:

```css
@source '../../../../vendor/bergstar/chat-field/resources/**/*.blade.php';
```

Publish the package assets:

```bash
php artisan vendor:publish --tag="chat-field-config"
php artisan vendor:publish --tag="chat-field-migrations"
```

Then run your migrations:

```bash
php artisan migrate
```

## Usage

Add the field to any Filament form:

```php
use Toolborg\ChatField\ChatField;

ChatField::make('chat')
```

For nested singular relationships, add it inside a relationship layout component:

```php
use Filament\Schemas\Components\Section;
use Toolborg\ChatField\ChatField;

Section::make('Meta')
    ->relationship('meta')
    ->schema([
        ChatField::make('chat'),
    ])
```

Behavior:

- One thread is stored per owner record.
- The current authenticated user becomes the message author.
- If the owner record does not exist yet, the field renders a save-first state instead of attaching to the wrong model.
- Nested singular relationship sections use the deepest saved record in the chain.

## Configuration

The config file lets you override:

- thread and message model classes
- author display name column
- messages per page
- upload disk, directory, visibility, mime types, and size limits

Default shape:

```php
return [
    'author_name_column' => 'name',
    'messages_per_page' => 10,
    'uploads' => [
        'disk' => 'public',
        'directory' => 'chat-field-attachments',
        'visibility' => 'public',
        'max_file_size' => 12288,
        'max_files' => 10,
    ],
];
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](.github/SECURITY.md) on how to report security vulnerabilities.

## Credits

- [Dmitry Kuzmenko](https://github.com/bergstar)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
