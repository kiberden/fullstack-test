<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\CommentService;
use App\Services\UserService;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Контроллер обработки api запросов на комментарий.
 */
class CommentController extends BaseController
{
    private CommentService $commentService;

    private UserService $userService;

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param LoggerInterface $logger
     *
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->commentService = new CommentService();
        $this->userService = new UserService();
    }

    /**
     * Список комментариев с пагинацией.
     *
     * @return ResponseInterface
     */
    public function index(): ResponseInterface
    {
        $query = CommentService::normalizeListQuery(
            $this->request->getGet('page'),
            $this->request->getGet('sort'),
            $this->request->getGet('dir')
        );

        $result = $this->commentService->getPaginatedList(
            $query['page'],
            CommentService::PER_PAGE,
            $query['order'],
            $query['direction']
        );

        return $this->response->setJSON([
            'success' => true,
            'comments' => $result['comments'],
            'pagination' => $result['pagination'],
            'sorting' => $result['sorting'],
            'html' => $this->renderListHtml($result),
        ]);
    }

    /**
     * HTML списка и пагинации из тех же partials, что и SSR-страница.
     *
     * @param array{
     *     comments: list<array<string, mixed>>,
     *     pagination: array<string, mixed>,
     *     sorting: array{sort: string, dir: string}
     * } $result
     *
     * @return array{list: string, pagination: string}
     */
    private function renderListHtml(array $result): array
    {
        $user = $this->userService->current() ?? ['id' => 0];

        return [
            'list' => view('comments/partials/list_items', [
                'comments' => $result['comments'],
                'user' => $user,
            ]),
            'pagination' => view('comments/partials/pagination', [
                'pagination' => $result['pagination'],
                'sort' => $result['sorting']['sort'],
                'dir' => $result['sorting']['dir'],
            ]),
        ];
    }

    /**
     * @return ResponseInterface
     *
     * @throws \ReflectionException
     */
    public function create(): ResponseInterface
    {
        $data = $this->request->getJSON(true) ?? $this->request->getPost();
        $result = $this->commentService->create(is_array($data) ? $data : []);
        $status = 422;

        if ($result['success']) {
            $status = 201;
        } elseif (isset($result['errors']['auth'])) {
            $status = 401;
        }

        return $this->response
            ->setStatusCode($status)
            ->setJSON($this->withCsrf($result));
    }

    /**
     * @param int $id
     *
     * @return ResponseInterface
     */
    public function delete(int $id): ResponseInterface
    {
        $result = $this->commentService->delete($id);
        $status = 200;

        if (! $result['success']) {
            if (isset($result['errors']['auth'])) {
                $status = 401;
            } elseif (isset($result['errors']['forbidden'])) {
                $status = 403;
            } else {
                $status = 404;
            }
        }

        return $this->response
            ->setStatusCode($status)
            ->setJSON($this->withCsrf($result));
    }

    /**
     * CSRF cookie HttpOnly + regenerate: клиент не читает cookie, отдаём новый hash в JSON.
     *
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    private function withCsrf(array $payload): array
    {
        $payload['csrfHash'] = csrf_hash();

        return $payload;
    }
}
