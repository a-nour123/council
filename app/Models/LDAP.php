<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LDAP extends Model
{
    protected $table = 'ldap_settings';

    protected $fillable = [
        'hosts',
        'port',
        'base_dn',
        'username',
        'filter',
        'version',
        'password',
        'timeout',
        'follow',
        'ssl',
        'tls'
    ];
}
