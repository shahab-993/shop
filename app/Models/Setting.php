<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'shop_name',
        'phone',
        'email',
        'address',
        'logo_path',
        'currency',
        'default_locale',
        'invoice_footer',
    ];
}
