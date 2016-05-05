<?php
/**
 * Created by PhpStorm.
 * User: panuka
 * Date: 28.03.16
 * Time: 1:11
 */

namespace GpaketBundle\Text;


Class Similarity {

    private static $e = 0.009;
    private static $font_size = 15;

    static private function getImageWithText($text) {
        /* Create Imagick objects */
        $image = new \Imagick();
        $draw = new \ImagickDraw();

        /* Font properties */
        $draw->setFont('fonts/Arial.ttf');
        $draw->setFontSize(self::$font_size);
        $metrics = $image->queryFontMetrics($draw, $text);
        $draw->annotation(0, $metrics['ascender'], $text);

        /* Create image */
        $image->newImage($metrics['textWidth'], $metrics['textHeight'], new \ImagickPixel('none'));
        $image->drawImage($draw);
        return $image;
    }

    public static function isSimilarity($_a, $_b) {
        if ($_a == $_b)
            return true;
        $a = self::getImageWithText($_a);
        $r = $a->compareImages(self::getImageWithText($_b), \Imagick::METRIC_MEANSQUAREERROR);
        return $r[1]<=self::$e;
    }

    // false - en, true - rus
    public static function isCyr($str) {
        preg_match_all("/([a-z])/ui", $str, $en);
        preg_match_all("/([а-я])/ui", $str, $ru);
        return count($ru[0])>count($en[0]);
    }
}