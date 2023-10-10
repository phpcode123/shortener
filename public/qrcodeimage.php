<?php

include "./qrcode.php";

$url        = isset($_GET["qr"]) ? $_GET["qr"] : 'https://shorturl.hk/';
$errorLevel = 'H'; // 纠错级别：L、M、Q、H
$PointSize  = '10'; // 点的大小：1到10,用于手机端4就可以了
$margin     = '1'; // 边距

function createqr($value,$errorCorrectionLevel,$matrixPointSize,$margin) {
   return QRcode::png($value, false, $errorCorrectionLevel, $matrixPointSize, $margin);
}

createqr($url, $errorLevel, $PointSize, $margin);