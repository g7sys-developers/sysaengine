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

    public static function json($data, int $status = 200, array $headers = [])
    {
        $response = new JsonResponse($data, $status, $headers);
        foreach ($response->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                header(sprintf('%s: %s', $name, $value), false);
            }
        }

        if (headers_sent()) {
            ob_clean();
        }
        
        http_response_code($response->getStatusCode());
        echo $response->getBody(); 
    }

    public static function redirect(string $url, int $status = 302, array $headers = []): Response
    {
        $response = new Response('php://temp', $status, array_merge(['Location' => $url], $headers));
        return $response;
    }

    public static function toast(string $message, string $type = 'success', int $status = 200, string $backtrace = '')
    {
        $json = [
            'toast' => [
                'message' => $message,
                'type' => $type,
                'backtrace' => $backtrace
            ]
        ];

        return self::json($json, $status);
    }
}
 