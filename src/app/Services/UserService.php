<?php

namespace App\Services;

use App\Models\UserModel;
use CodeIgniter\Session\Session;
use CodeIgniter\Validation\ValidationInterface;

/**
 * Класс сервис работы с пользователем.
 */
final class UserService
{
    private UserModel $userModel;
    private ValidationInterface $validation;
    private Session $session;

    /**
     * @param UserModel|null $userModel
     * @param ValidationInterface|null $validation
     * @param Session|null $session
     */
    public function __construct(
        ?UserModel $userModel = null,
        ?ValidationInterface $validation = null,
        ?Session $session = null
    ) {
        $this->userModel = $userModel ?? new UserModel();
        $this->validation = $validation ?? \Config\Services::validation();
        $this->session = $session ?? session();
    }

    /**
     * Создаёт пользователя или обновляет его session при повторном вводе email.
     *
     * @param array<string, mixed> $data
     *
     * @return array{success: bool, user?: array<string, mixed>, errors?: array<string, string>}
     * @throws \ReflectionException
     */
    public function createOrUpdate(array $data): array
    {
        $payload = [
            'email' => $data['email'] ?? null,
        ];

        if (!$this->validation->run($payload, 'user_identify')) {
            return [
                'success' => false,
                'errors' => $this->validation->getErrors(),
            ];
        }

        $email = strtolower(trim((string) $payload['email']));

        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            return [
                'success' => false,
                'errors' => ['email' => 'Некорректный email'],
            ];
        }

        $sessionId = session_id();
        $existing  = $this->userModel->where('email', $email)->first();

        if ($existing !== null) {
            $this->userModel->update($existing['id'], [
                'session' => $sessionId,
            ]);
            $user = $this->userModel->find($existing['id']);
        } else {
            $id = $this->userModel->insert([
                'email' => $email,
                'session' => $sessionId,
            ]);

            if ($id === false) {
                return [
                    'success' => false,
                    'errors' => $this->userModel->errors() ?: ['database' => 'Не удалось сохранить пользователя'],
                ];
            }

            $user = $this->userModel->find($id);
        }

        if ($user === null) {
            return [
                'success' => false,
                'errors' => ['database' => 'Пользователь не найден после сохранения'],
            ];
        }

        $this->session->set([
            'user_id' => (int) $user['id'],
            'user_email' => $user['email'],
        ]);

        return [
            'success' => true,
            'user' => $this->publicUser($user),
        ];
    }

    /**
     * Ищет пользователя по текущей session в таблице users.
     * Если запись есть — восстанавливает user_id в PHP-сессии.
     *
     * @return array<string, mixed>|null
     */
    public function current(): ?array
    {
        $user = $this->userModel->where('session', session_id())->first();

        if ($user === null) {
            return null;
        }

        $this->session->set([
            'user_id' => (int) $user['id'],
            'user_email' => $user['email'],
        ]);

        return $this->publicUser($user);
    }

    /**
     * Метод проверки найден ли текущий активный пользователь.
     *
     * @return bool
     */
    public function isIdentified(): bool
    {
        return $this->current() !== null;
    }

    /**
     * @param array<string, mixed> $user
     *
     * @return array{id: int, email: string, date_create: string|null}
     */
    private function publicUser(array $user): array
    {
        return [
            'id' => (int) $user['id'],
            'email' => $user['email'],
            'date_create' => $user['date_create'] ?? null,
        ];
    }
}
