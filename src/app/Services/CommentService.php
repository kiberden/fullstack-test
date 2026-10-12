<?php

namespace App\Services;

use App\Models\CommentModel;
use CodeIgniter\Validation\ValidationInterface;

/**
 * Сервис управления комментариями.
 */
final class CommentService
{
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
     * @return list<array<string, mixed>>
     */
    public function getList(string $order = 'id', string $direction = 'ASC'): array
    {
        return $this->commentModel
            ->select('comments.*, users.email')
            ->join('users', 'users.id = comments.user_id')
            ->orderBy('comments.' . $order, $direction)
            ->findAll();
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
                'errors'  => ['auth' => 'Необходима идентификация'],
            ];
        }

        $payload = [
            'user_id' => $user['id'],
            'text' => $data['text'] ?? null,
        ];

        if (!$this->validation->run($payload, 'comment')) {
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
        if ($id <= 0 || $this->commentModel->find($id) === null) {
            return [
                'success' => false,
                'errors' => ['id' => 'Комментарий не найден'],
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
