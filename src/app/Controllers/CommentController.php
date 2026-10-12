<?php

namespace App\Controllers;

use App\Services\CommentService;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Контроллер обработки api запросов на комментарий.
 */
class CommentController extends BaseController
{
    private CommentService $commentService;

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
            ->setJSON($result);
    }

    /**
     * @param int $id
     *
     * @return ResponseInterface
     */
    public function delete(int $id): ResponseInterface
    {
        $result = $this->commentService->delete($id);

        return $this->response
            ->setStatusCode($result['success'] ? 200 : 404)
            ->setJSON($result);
    }
}
