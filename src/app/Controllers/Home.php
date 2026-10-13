<?php

declare(strict_types=1);

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

        $query = CommentService::normalizeListQuery(
            $this->request->getGet('page'),
            $this->request->getGet('sort'),
            $this->request->getGet('dir')
        );

        $result = $commentService->getPaginatedList(
            $query['page'],
            CommentService::PER_PAGE,
            $query['order'],
            $query['direction']
        );

        return view('comments/index', [
            'title' => 'Комментарии',
            'comments' => $result['comments'],
            'pagination' => $result['pagination'],
            'sort' => $result['sorting']['sort'],
            'dir' => $result['sorting']['dir'],
            'user' => $userService->current(),
            'csrfName' => csrf_token(),
            'csrfHash' => csrf_hash(),
        ]);
    }
}
