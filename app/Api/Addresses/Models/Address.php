<?php
namespace GetCandy\Api\Addresses\Models;

use GetCandy\Api\Auth\Models\User;
use GetCandy\Api\Traits\Hashids;
use GetCandy\Api\Scaffold\BaseModel;

class Address extends BaseModel
{
    protected $hashids = 'user';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'address',
        'address_three',
        'address_two',
        'billing',
        'city',
        'country',
        'county',
        'firstname',
        'lastname',
        'shipping',
        'state',
        'zip'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}