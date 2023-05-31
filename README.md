# Multitenancy Laravel Package

[![Total Downloads](https://img.shields.io/packagist/dt/romegadigital/multitenancy.svg?style=flat-square)](https://packagist.org/packages/romegadigital/multitenancy)

This package provides a convenient way to add multitenancy to your Laravel application. It manages models and relationships for Tenants, identifies incoming traffic by subdomain, and associates it with a corresponding tenant. Users not linked with a specific subdomain or without a matching tenant in the Tenant table are presented with a 403 error.

**Note:** Any resources saved while accessing a scoped subdomain will automatically be saved against the current tenant, based on subdomain.

**Note:** The `admin` subdomain is reserved for the package to remove all scopes from users with a `Super Administrator` role.

## Table of Contents
- [Installation](#installation)
- [Usage](#usage)
- [Console Commands](#console-commands)
- [Nova Management](#managing-with-nova)
- [Testing](#testing-package)

## Installation

Use composer to install the package:

``` bash
composer require romegadigital/multitenancy
```

In Laravel 5.5 and newer, the service provider gets registered automatically. For older versions, add the service provider in the `config/app.php` file:

```php
'providers' => [
    // ...
    RomegaDigital\Multitenancy\MultitenancyServiceProvider::class,
];
```

Publish the config file with:

```bash
php artisan vendor:publish --provider="RomegaDigital\Multitenancy\MultitenancyServiceProvider" --tag="config"
```

Run the setup with:

```bash
php artisan multitenancy:install
```

This command will:
- Publish and migrate required migrations
- Add a `Super Administrator` role and `access admin` permission
- Create an `admin` Tenant model

## Usage

Apply the `RomegaDigital\Multitenancy\Traits\HasTenants` and `Spatie\Permission\Traits\HasRoles` traits to your User model(s):

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

Tenants require a name to identify the tenant and a subdomain that is associated with that user. Example:

`tenant1.example.com`

`tenant2.example.com`


**Note:** You define the base url `example.com` in the `config/multitenancy.php` file.


These Tenants could be added to the database like so:

```php
Tenant::create([
    'name'    => 'An Identifying Name',
    'domain'  => 'tenant1'
]);
Tenant::create([
    'name'    => 'A Second Customer',
    'domain'  => 'tenant2'
]);
```

You can then attach user models to the Tenant:

```php
$user = User::first();
Tenant::first()->users()->save($user);
```

Create Tenants, associate them with Users, and define access rules using provided Middleware. Check [the detailed usage guide](#detailed-usage-guide) for examples.


## Detailed Usage Guide
### 1. **Models and relationships:** 
Use Eloquent to access User's tenants (`User::tenants()->get()`) and Tenant's users (`Tenant::users()->get()`). Add new tenants and their associated users to the database.


### 2. **Middleware:** 
Add `TenantMiddleware` and `GuestTenantMiddleware` to your `app/Http/Kernel.php` file and apply them to routes.

#### Tenant Middleware

```php
protected $middlewareAliases = [
    // ...
    'tenant.auth' => \RomegaDigital\Multitenancy\Middleware\TenantMiddleware::class,
];
```

Then you can bring multitenancy to your routes using middleware rules:

```php
Route::group(['middleware' => ['tenant.auth']], function () {
    // ...
});
```

#### Guest Tenant Middleware

This package comes with `GuestTenantMiddleware` middleware which applies the tenant scope to all models and can be used for allowing guest users to access Tenant related pages. You can add it inside your `app/Http/Kernel.php` file.

```php
protected $middlewareAliases = [
    // ...
    'tenant.guest' => \RomegaDigital\Multitenancy\Middleware\GuestTenantMiddleware::class,
];
```

Then you can bring multitenancy to your routes using middleware rules:

```php
Route::group(['middleware' => ['tenant.guest']], function () {
    // ...
});
```


### 3. **Tenant Assignment for Models:** 
Make models tenant-aware by adding a trait and migration. Then apply tenant scoping automatically. This allows users to access `tenant1.example.com` and return the data from `tenant1` only.

For example, say you wanted Tenants to manage their own `Product`. In your `Product` model, add the `BelongsToTenant` trait. Then run the provided console command to add the necessary relationship column to your existing `products` table.

```php
use Illuminate\Database\Eloquent\Model;
use RomegaDigital\Multitenancy\Traits\BelongsToTenant;

class Product extends Model
{
    use BelongsToTenant;

    // ...
}
```
**Add tenancy to a model's table:** `php artisan multitenancy:migration products`

### 4. **Access to Current Tenant:** 
Use `app('multitenancy')->currentTenant()` to get the current tenant model.

### 5. **Admin Domain Access:** 
Assign the `Super Administrator` role to a user to enable access to the `admin` subdomain. Manually create an admin portal if necessary.

### 6. **Auto-assign Users to Tenants:** 
Enable `ignore_tenant_on_user_creation` setting to automatically assign users to the Tenant subdomain on which they are created.

### 7. **Give a user `Super Administration` rights:**

In order to access the `admin.example.com` subdomain, a user will need the `access admin` permission. This package relies on [Spatie's Laravel Permission](https://github.com/spatie/laravel-permission) package and is automatically included as a dependency when installing this package. We also provide a `Super Administrator` role on migration that has the relevant permission already associated with it. Assign the `Super Administrator` role to an admin user to provide the access they need. See the [Laravel Permission](https://github.com/spatie/laravel-permission) documentation for more on adding users to the appropriate role and permission.

The Super Administrator is a special user role with privileged access. Users with this role can access all model resources, navigate across different tenants' domains, and gain entry to the `admin` subdomain where all tenant scopes are disabled.

When a user is granted the `Super Administrator` role, they can freely access the `admin` subdomain. In this context, tenant scopes aren't applied. This privilege allows Super Administrators to manage data across all instances without requiring specific access to each individual tenant's account.

Give a user `Super Administration` rights: `php artisan multitenancy:super-admin admin@example.com`

## Managing with Nova

You can manage the resources of this package in Nova with the [MultitenancyNovaTool](https://github.com/romegadigital/MultitenancyNovaTool).

## Testing Package

Run tests with the command:

`php vendor/bin/testbench package:test`
