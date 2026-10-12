<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Модель пользователя.
 */
class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'email',
        'session',
    ];
}
