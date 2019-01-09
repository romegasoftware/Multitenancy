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

];