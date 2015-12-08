<?php
/**
 * Created by PhpStorm.
 * User: panuka
 * Date: 03.12.15
 * Time: 10:24
 */

use Telegram\Paket;

include 'vendor/autoload.php';
$gp = new Paket($_SERVER['DOCUMENT_ROOT']);
$gp->process();