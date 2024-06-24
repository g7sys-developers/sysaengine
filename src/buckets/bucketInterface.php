<?php

namespace sysaengine\buckets;

interface bucketInterface {
    public function upload(string $filepath);
    public function delete(string $key);
    public function getTemporaryUrl(string $key);
    public function exists(string $key);
    public function getBucketName() : string;
}