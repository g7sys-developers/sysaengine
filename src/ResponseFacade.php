<?php
namespace sysaengine;

use Laminas\Diactoros\Response;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\Stream;

class ResponseFacade
{
    public static function html(string $content, int $status = 200, array $headers = []): Response
    {
        $body = new Stream('php://temp', 'wb+');
        $body->write($content);
        $body->rewind();

        $response = new Response($body, $status, array_merge(['Content-Type' => 'text/html'], $headers));
        return $response;
    }

    public static function json($data, int $status = 200, array $headers = []): JsonResponse
    {
        return new JsonResponse($data, $status, $headers);
    }

    public static function redirect(string $url, int $status = 302, array $headers = []): Response
    {
        $response = new Response('php://temp', $status, array_merge(['Location' => $url], $headers));
        return $response;
    }
}
