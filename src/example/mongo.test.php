<?php
    require_once __DIR__. '/../sysaengine.class.php';
    use sysaengine\mongodb;

    $m = new mongodb();
    $db = $m->conectar();
    if($db instanceof \MongoDB\Client){
        echo 'conectado com sucesso<br><br>';
    }

    $history = $m->selDB('history');
    if($history instanceof \MongoDB\Database){
        echo 'banco de dados history selecionado.<br><br>';
    }
?>