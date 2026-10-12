<?php

namespace App\Filters;

use App\Services\UserService;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Middleware: пускает на страницу комментариев только если
 * текущая session есть в таблице users.
 */
class IdentifyFilter implements FilterInterface
{
    /**
     * @param RequestInterface $request
     * @param $arguments
     *
     * @return RequestInterface|ResponseInterface|string|void|null
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $user = (new UserService())->current();

        if ($user !== null) {
            return null;
        }

        return service('response')->setBody(view('comments/identify', [
            'title' => 'Комментарии',
            'csrfName' => csrf_token(),
            'csrfHash' => csrf_hash(),
        ]));
    }

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param $arguments
     *
     * @return ResponseInterface
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): ResponseInterface
    {
        return $response;
    }
}
