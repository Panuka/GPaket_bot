<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 25.09.2015
 * Time: 11:34
 */

global $answers;
global $regexps;
$answers = array(
    array(
        'говна пакет', 'пидора ответ'
    )
);


$regexps = array(
    '/([^а-яА-Я]|^)(\н+\е+\т+)[!)[."\'*0-9?]*$/'
);