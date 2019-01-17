# Multitenancy Package

This package is meant to be a quick and easy way to add multitenancy to your Laravel application. It simply creates models and relationships for Tenants and models. The package identifies the incoming traffic by subdomain, and finds a corresponding tenant in the Tenant table. If none are found or the user is not associated with a particular subdomain, the user is met with a 403 error.

The `admin` subdomain is reserved for the package. It is used to automatically remove all scopes from users with a `access admin` permission.

To scope a resource to the currently accessed subdomain, you simply need to add a [single trait](#tenant-assignment-for-other-models) to the model and add a [foreign key relationship](#console-commands) to the model's table. The package middleware will automatically apply the scopes for the relevant models.

Any resources saved while accessing a scoped subdomain will automatically be saved against the current tenant, based on subdomain.

- [Multitenancy Package](#multitenancy-package)
  - [Installation](#installation)
  - [Usage](#usage)
    - [Middleware](#middleware)
    - [Tenant Assignment for Models](#tenant-assignment-for-models)
    - [Providing Access to Admin Domain](#providing-access-to-admin-domain)
  - [Console Commands](#console-commands)
  - [Managing with Nova](#managing-with-nova)


## Installation

You can install the package via composer:

``` bash
composer require romegadigital/multitenancy
```

In Laravel 5.5 the service provider will automatically get registered. In older versions of the framework just add the service provider in `config/app.php` file:

```php
'providers' => [
    // ...
    RomegaDigital\Multitenancy\MultitenancyServiceProvider::class,
];
```

You can publish the config file with:

```bash
php artisan vendor:publish --provider="RomegaDigital\Multitenancy\MultitenancyServiceProvider" --tag="config"
```

You can automate most of the setup by running:

```bash
php artisan multitenancy:install
```

It will:
- `publish` and `migrate` required migrations
- add a `Super Administrator` role and `access admin` permission
- add an `admin` Tenant model

## Usage

First, add the `RomegaDigital\Multitenancy\Traits\HasTenants` and `Spatie\Permission\Traits\HasRoles` traits to your User model(s):

```php
use Spatie\Permission\Traits\HasRoles;
use RomegaDigital\Multitenancy\Traits\HasTenants;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasTenants, HasRoles;

    // ...
}
```

The package relies on Eloquent, so you may access the User's tenants using `User::tenants()->get()`.

Inversely, you may access the Tenant's users with `Tenant::users()->get()`.

Tenants require a name to identify the tenant and and a subdomain that is associated with that user. 

`tenant1.example.com`

`tenant2.example.com`

They would be added to the database like so:

```php
Tenant::createMany([
    [
        'name'    => 'An Identifying Name',
        'domain'  => 'tenant1'
    ],
    [
        'name'    => 'A Second Customer',
        'domain'  => 'tenant2'
    ]
]);
```

You can then attach users to the tenant:

```php
Tenant::first()->save($user);
```

### Middleware

This package comes with `TenantMiddleware` middleware. You can add it inside your `app/Http/Kernel.php` file.

```php
protected $routeMiddleware = [
    // ...
    'tenant' => \RomegaDigital\Multitenancy\Middlewares\TenantMiddleware::class,
];
```

Then you can bring multitenancy to your routes using middleware rules:

```php
Route::group(['middleware' => ['tenant']], function () {
    // ...
});
```

### Tenant Assignment for Models

Models can automatically inherit scoping of the current tenant by adding a trait and migration to a model. This would allow users to access `tenant1.example.com` and return only the data to `tenant1`. 

For example, say you wanted Tenants to manage their own `Product`. In your `Product` model, simply add the `BelongsToTenant` trait. Then run the [provided console command](#console-commands) to add the necessary relationship column to your existing `products` table.

```php
use Illuminate\Database\Eloquent\Model;
use RomegaDigital\Multitenancy\Traits\BelongsToTenant;

class Product extends Model
{
    use BelongsToTenant;

    // ...
}
```

> **hint** 
> If the user is assigned `Super Administrator` access, they will be able to access your `admin` subdomain and the tenant scope will not register. This allows you to manage all the data across all the instances without needing individual access to each Tenant's account.

### Providing Access to Admin Domain

In order to access the `admin.example.com` subdomain, a user will need the `access admin` permission. This package relies on [Spatie's Laravel Permission](https://github.com/spatie/laravel-permission) package and is automatically included as a dependency when installing this package. We also provide a `Super Administrator` role on migration that has the relevant permission already associated with it. You may simply assign the `Super Administrator` role to an admin user to provide the access they need. See their documentation on how to add users to the appropriate role and permission.

The Admin Portal subdomain will automatically be created during [installation](#installation), but you can manually add it like this:

```php
Tenant::create([
    'name'      => 'Admin Portal',
    'domain'    => 'admin'
]);
```

## Console Commands

You can generate a migration to add tenancy to an existing model's table using

```bash
php artisan multitenancy:migration products
```

Assigning a user `Super Administration` rights and the `admin` tenant can be done using

```bash
php artisan multitenancy:super-admin admin@example.com
```

## Managing with Nova

Checkout the [Nova Package](#) created to manage the resources utilized in this package.
