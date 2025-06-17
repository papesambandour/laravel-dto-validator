# Laravel DTO Validator (DigitalPi)

This package allows you to use DTOs with attribute-based validation, just like in Spring Boot.

## 🚀 Features

- Auto-inject DTOs into controllers from Request
- Validate with PHP 8+ attributes (like #[Rules([])])
- UploadedFile support

## 📦 Installation

```bash
composer require digitalpi/laravel-dto-validator
```

## 🧩 Setup

Register the middleware in your `app/bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \DigitalPi\DtoValidator\Middleware\InjectDtoMiddleware::class,
        ]);
        //or
        $middleware->api(append: [
            \DigitalPi\DtoValidator\Middleware\InjectDtoMiddleware::class,
        ]);
    })
```

## 📁 Example

```php
use DigitalPi\DtoValidator\Attributes\Rules;
use Illuminate\Http\UploadedFile;

class UserDTO
{
    #[Rules(['required'])]
    public ?string $username;

    #[Rules(['required'])]
    public ?string $phone;

    #[Rules(['required', 'file', 'mimes:jpg,png'])]
    public ?UploadedFile $avatar;
}
```

```php
use App\DTO\UserDTO;
use DigitalPi\DtoValidator\DtoValidator;

class UserController
{
    public function store(UserDTO $dto, DtoValidator $validator)
    {
        $validator->validate($dto);
        $path = $dto->avatar->store('avatars');

        return response()->json([
            'username' => $dto->username,
            'stored' => $path,
        ]);
    }
}
```

## ✅ Requirements

- PHP 8.1+
- Laravel 10 or 11 or 12
