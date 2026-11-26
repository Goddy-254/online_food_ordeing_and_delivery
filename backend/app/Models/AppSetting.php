<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A small key/value store for values managed from the admin settings page. */
class AppSetting extends Model
{
    protected $table = 'app_settings';

    protected $fillable = ['key', 'value'];
}
