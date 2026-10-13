<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CommentModel;
use CodeIgniter\Validation\ValidationInterface;

/**
 * Сервис управления комментариями.
 */
final class CommentService
{
    public const PER_PAGE = 3;

    public const DEFAULT_SORT = 'id';

    public const DEFAULT_DIR = 'desc';

    /** @var list<string> */
    public const ALLOWED_SORTS = ['id', 'date_create'];

    /** @var list<string> */
    public const ALLOWED_DIRS = ['asc', 'desc'];

    private CommentModel $commentModel;
    private ValidationInterface $validation;
    private UserService $userService;

    /**
     * @param CommentModel|null $commentModel
     * @param ValidationInterface|null $validation
     * @param UserService|null $userService
     */
    public function __construct(
        ?CommentModel $commentModel = null,
        ?ValidationInterface $validation = null,
        ?UserService $userService = null
    ) {
        $this->commentModel = $commentModel ?? new CommentModel();
        $this->validation = $validation ?? \Config\Services::validation();
        $this->userService = $userService ?? new UserService();
    }

    /**
     * Получить список комментариев.
     *
     * @return list<array<string, mixed>>
     */
    public function getList(string $order = 'id', string $direction = 'DESC'): array
    {
        return $this->commentModel
            ->select('comments.*, users.email')
            ->join('users', 'users.id = comments.user_id')
            ->orderBy('comments.' . $order, $direction)
            ->findAll();
    }

    /**
     * Нормализация GET-параметров списка: page, sort, dir (whitelist).
     *
     * @return array{
     *     page: int,
     *     sort: string,
     *     dir: string,
     *     order: string,
     *     direction: string
     * }
     */
    public static function normalizeListQuery(
        int|string|null $page = 1,
        ?string $sort = null,
        ?string $dir = null
    ): array {
        $sort = strtolower((string) $sort);
        $dir  = strtolower((string) $dir);

        if (! in_array($sort, self::ALLOWED_SORTS, true)) {
            $sort = self::DEFAULT_SORT;
        }

        if (! in_array($dir, self::ALLOWED_DIRS, true)) {
            $dir = self::DEFAULT_DIR;
        }

        return [
            'page' => max(1, (int) ($page ?? 1)),
            'sort' => $sort,
            'dir' => $dir,
            'order' => $sort,
            'direction' => strtoupper($dir),
        ];
    }

    /**
     * Query-string для ссылок списка (пагинация / сортировка).
     */
    public static function buildListQuery(int $page, string $sort, string $dir): string
    {
        $normalized = self::normalizeListQuery($page, $sort, $dir);

        return http_build_query([
            'page' => $normalized['page'],
            'sort' => $normalized['sort'],
            'dir' => $normalized['dir'],
        ]);
    }

    /**
     * Получить страницу комментариев с метаданными пагинации и сортировки.
     *
     * @return array{
     *     comments: list<array<string, mixed>>,
     *     pagination: array{
     *         page: int,
     *         perPage: int,
     *         total: int,
     *         totalPages: int,
     *         pages: list<int|null>
     *     },
     *     sorting: array{sort: string, dir: string}
     * }
     */
    public function getPaginatedList(
        int $page = 1,
        int $perPage = self::PER_PAGE,
        string $order = 'id',
        string $direction = 'DESC'
    ): array {
        $normalized = self::normalizeListQuery($page, $order, $direction);
        $perPage = max(1, $perPage);
        $page = $normalized['page'];
        $order = $normalized['order'];
        $direction = $normalized['direction'];

        $total = $this->commentModel
            ->join('users', 'users.id = comments.user_id')
            ->countAllResults();

        $totalPages = $total > 0 ? (int) ceil($total / $perPage) : 0;

        if ($totalPages > 0 && $page > $totalPages) {
            $page = $totalPages;
        }

        $comments = [];

        if ($total > 0) {
            $comments = $this->commentModel
                ->select('comments.*, users.email')
                ->join('users', 'users.id = comments.user_id')
                ->orderBy('comments.' . $order, $direction)
                ->findAll($perPage, ($page - 1) * $perPage);
        }

        return [
            'comments'   => $comments,
            'pagination' => [
                'page' => $page,
                'perPage' => $perPage,
                'total' => $total,
                'totalPages' => $totalPages,
                'pages' => $this->buildPageWindow($page, $totalPages),
            ],
            'sorting' => [
                'sort' => $normalized['sort'],
                'dir' => $normalized['dir'],
            ],
        ];
    }

    /**
     * Окно страниц: текущая ±2, первая и последняя; пропуски — null (многоточие).
     *
     * @return list<int|null>
     */
    private function buildPageWindow(int $current, int $totalPages): array
    {
        if ($totalPages <= 0) {
            return [];
        }

        if ($totalPages === 1) {
            return [1];
        }

        $visible = [];

        foreach (range(max(1, $current - 2), min($totalPages, $current + 2)) as $page) {
            $visible[$page] = true;
        }

        $visible[1] = true;
        $visible[$totalPages] = true;

        $pages = [];
        $last = 0;

        foreach (range(1, $totalPages) as $page) {
            if (! isset($visible[$page])) {
                continue;
            }

            if ($last !== 0 && $page - $last > 1) {
                $pages[] = null;
            }

            $pages[] = $page;
            $last = $page;
        }

        return $pages;
    }

    /**
     * @param array $data
     *
     * @return array
     *
     * @throws \ReflectionException
     */
    public function create(array $data): array
    {
        $user = $this->userService->current();

        if ($user === null) {
            return [
                'success' => false,
                'errors' => ['auth' => 'Необходима идентификация'],
            ];
        }

        $payload = [
            'user_id' => $user['id'],
            'text' => $data['text'] ?? null,
        ];

        if (! $this->validation->run($payload, 'comment')) {
            return [
                'success' => false,
                'errors' => $this->validation->getErrors(),
            ];
        }

        $id = $this->commentModel->insert($payload);

        if ($id === false) {
            return [
                'success' => false,
                'errors' => $this->commentModel->errors() ?: ['database' => 'Не удалось создать комментарий'],
            ];
        }

        $comment = $this->commentModel
            ->select('comments.*, users.email')
            ->join('users', 'users.id = comments.user_id')
            ->find($id);

        return [
            'success' => true,
            'comment' => $comment,
        ];
    }

    /**
     * @return array{success: bool, errors?: array<string, string>}
     */
    public function delete(int $id): array
    {
        $user = $this->userService->current();

        if ($user === null) {
            return [
                'success' => false,
                'errors' => ['auth' => 'Необходима идентификация'],
            ];
        }

        if ($id <= 0) {
            return [
                'success' => false,
                'errors' => ['id' => 'Комментарий не найден'],
            ];
        }

        $comment = $this->commentModel->find($id);

        if ($comment === null) {
            return [
                'success' => false,
                'errors' => ['id' => 'Комментарий не найден'],
            ];
        }

        if ((int) $comment['user_id'] !== (int) $user['id']) {
            return [
                'success' => false,
                'errors' => ['forbidden' => 'Можно удалить только свой комментарий'],
            ];
        }

        if (! $this->commentModel->delete($id)) {
            return [
                'success' => false,
                'errors' => $this->commentModel->errors() ?: ['database' => 'Не удалось удалить комментарий'],
            ];
        }

        return ['success' => true];
    }
}
