<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Registration extends Authenticatable
{public function getAuthIdentifierName()
    {
        return 'mobile'; // Use mobile instead of email
    }
    
    protected $table = 'registrations'; // Custom table name
    protected $fillable = ['firstname', 'lastname', 'mobile', 'password', 'created_at', 'updated_at'];
}
