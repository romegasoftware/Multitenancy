<?php

return [
    /*
    |--------------------------------------------------------------------------
    | User Model
    |--------------------------------------------------------------------------
    |
    | This is the model you are using for Users that will be attached to the
    | Tenant instance. Users must be attached to domains in order to have
    | access to tenant instance.
    */

    'user_model' => \App\User::class,

    /*
    |--------------------------------------------------------------------------
    | Base URL
    |--------------------------------------------------------------------------
    |
    | This is the URL you would like to serve as the base of your app. It
    | should not contain a prefix (ie: http://, https://).
    | By default, it will attempt to use the host name with the TLD and domain
    | name stripped.
    | (ie: subdomain.master.example.com will return subdomain.master)
    |
    | Default: null
    */

    'base_url' => env('MULTITENANCY_BASE_URL', null),

    /*
    |--------------------------------------------------------------------------
    | Roles
    |--------------------------------------------------------------------------
    |
    | The values (on the right) determine how the roles with
    | keys (on the left) are being named in the database.
    */

    'roles' => [
        'super_admin' => 'Super Administrator',
    ],

    /*
    |--------------------------------------------------------------------------
    | Tenant Model
    |--------------------------------------------------------------------------
    |
    | This is the model you are using for Tenants that will be attached to the
    | User instance. It would be recommended to extend the Tenant model as
    | defined in the package, but if you replace it, be sure to implement
    | the RomegaDigital\Multitenancy\Contracts\Tenant contract.
    */

    'tenant_model' => \RomegaDigital\Multitenancy\Models\Tenant::class,

    'table_names' => [
        /*
         * We need to know which table to setup foreign relationships on.
         */

        'users' => 'users',

        /*
         * If overwriting `tenant_model`, you may also wish to define a new table
         */

        'tenants' => 'tenants',

        /*
         * Define the relationship table for the belongsToMany relationship
         */

        'tenant_user' => 'tenant_user',
    ],

    /*
    |--------------------------------------------------------------------------
    | Redirect Route
    |--------------------------------------------------------------------------
    |
    | This is the name of the route users who aren't logged in will be redirected to
    */

    'redirect_route' => 'login',

    /*
    |--------------------------------------------------------------------------
    | Ingore Tenant on User creation
    |--------------------------------------------------------------------------
    |
    | By default a user is assigned the tenant it is created on. If you create
    | a user while being on the `admin` tenant, this would assign the created
    | user the `admin` tenant automatically. If you don't want to get tenants
    | assigned to users automatically simply disable this setting by setting
    | it to false.
    */

    'ignore_tenant_on_user_creation' => false,
];
