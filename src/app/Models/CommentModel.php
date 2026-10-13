<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

/**
 * Модель комментариев.
 */
class CommentModel extends Model
{
    protected $table = 'comments';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'user_id',
        'text',
    ];
}
