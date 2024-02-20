<?php
namespace sysaengine;

class response {
    /**
     * To json response
     * 
     * @param array $data
     * @return void
     */
    public static function json(array $data, int $status) : void
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        http_response_code($status);
    }
}