<?php

namespace App\Controllers;

use App\Services\CommentService;
use App\Services\UserService;

class Home extends BaseController
{
    /**
     *  Список комментариев.
     *  Доступен только после IdentifyFilter (session есть в users).
     *
     * @return string
     */
    public function index(): string
    {
        $commentService = new CommentService();
        $userService = new UserService();

        return view('comments/index', [
            'title' => 'Комментарии',
            'comments' => $commentService->getList(),
            'user' => $userService->current(),
            'csrfName' => csrf_token(),
            'csrfHash' => csrf_hash(),
        ]);
    }
}
