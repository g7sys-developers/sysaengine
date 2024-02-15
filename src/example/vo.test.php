<?php
    require_once __DIR__. '/../sysaengine.class.php';
    use \sysaengine\vo;

    $vo = new vo('ribeiraogg', 'accesslevel', 'accesslevel');
    $vo->id_accesslevel = 1;
    echo $vo->id_accesslevel. '<br>';

    $vo->emite_documento_fiscal = false;
    echo $vo->emite_documento_fiscal. '<br>';

    $vo->frvd_set_order_accesslevel(4);
    echo $vo->fm_get_order_accesslevel();
?>