<?php
namespace RomegaDigital\Multitenancy\Tests;

use Illuminate\Database\Eloquent\Model;
use RomegaDigital\Multitenancy\Traits\BelongsToTenant;

class Product extends Model
{
    use BelongsToTenant;

    public $timestamps = false;
    protected $fillable = ['name', 'tenant_id'];
}