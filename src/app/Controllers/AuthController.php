<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\UserService;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Контроллер управления авторизацией.
 */
class AuthController extends BaseController
{
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
        $this->userService = new UserService();
    }

    /**
     * Текущий пользователь по связке session ↔ users.session.
     */
    public function me(): ResponseInterface
    {
        $user = $this->userService->current();

        return $this->response->setJSON([
            'success' => true,
            'identified' => $user !== null,
            'user' => $user,
        ]);
    }

    /**
     * Первый вход: email → createOrUpdate + запись текущей session.
     *
     * @return ResponseInterface
     * @throws \ReflectionException
     */
    public function identify(): ResponseInterface
    {
        $data = $this->request->getJSON(true) ?? $this->request->getPost();
        $result = $this->userService->createOrUpdate(is_array($data) ? $data : []);

        return $this->response
            ->setStatusCode($result['success'] ? 200 : 422)
            ->setJSON($result);
    }
}
