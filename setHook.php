<?php
/**
 * Created by PhpStorm.
 * User: panuka
 * Date: 03.12.15
 * Time: 11:09
 */

use Telegram\Paket;

include 'vendor/autoload.php';
$tg = new Paket($_SERVER['DOCUMENT_ROOT']);
$tg->setHook();