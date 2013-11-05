<?php
/*
 * This file is part of the Magallanes package.
*
* (c) Andrés Montañez <andres@andresmontanez.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

class Mage_Console_Colors
{
    private static $foreground_colors = array(
        'black' => '0;30',
        'dark_gray' => '1;30',
        'blue' => '0;34',
        'light_blue' => '1;34',
        'green' => '0;32',
        'light_green' => '1;32',
        'cyan' => '0;36',
        'light_cyan' => '1;36',
        'red' => '0;31',
        'light_red' => '1;31',
        'purple' => '0;35',
        'light_purple' => '1;35',
        'brown' => '0;33',
        'yellow' => '1;33',
        'light_gray' => '0;37',
        'white' => '1;37'

    );

    // Returns colored string
    public static function color($string)
    {
        foreach (self::$foreground_colors as $key => $code) {
            $replaceFrom = array(
                '<' . $key . '>',
            	'</' . $key . '>'
            );
            $replaceTo = array(
                "\033[" . $code . 'm',
                "\033[0m"
            );

            $string = str_replace($replaceFrom, $replaceTo, $string);
        }

        return $string;
    }
}
