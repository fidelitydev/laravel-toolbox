<?php

namespace Knighttower\Toolbox\Helpers;

class StringHelper
{
    /**
     * Remove underscore or dashes.
     *
     * @param string $string
     * @return string
     */
    private static function sanitize(string $string): string
    {
        return ucwords(str_replace(['_', '-'], ' ', $string));
    }

    /**
     * Function to convert strings into Title Case.
     *
     * @param string $string
     * @return string
     */
    public static function toTitleCase(?string $string): ?string
    {
        if (empty($string)) {
            return null;
        }

        $lowerList = [
            'of','a','the','and','an','or','nor','but','is','if','then','else','when',
            'at','from','by','on','off','for','in','out','to','into','with'
        ];

        $string = self::sanitize($string);

        $toLower = explode(' ', $string);

        foreach ($toLower as $key => $word) {
            if ($key === 0 || !in_array($word, $lowerList)) {
                $toLower[$key] = ucwords($word);
            }
        }

        return implode(' ', $toLower);
    }
}
