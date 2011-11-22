<?php
class Magallanes_Console_Colors {
	private static $foreground_colors = array ();
	private static $background_colors = array ();
	
	public function __construct() {
		// Set up shell colors
		self::$foreground_colors ['black'] = '0;30';
		self::$foreground_colors ['dark_gray'] = '1;30';
		self::$foreground_colors ['blue'] = '0;34';
		self::$foreground_colors ['light_blue'] = '1;34';
		self::$foreground_colors ['green'] = '0;32';
		self::$foreground_colors ['light_green'] = '1;32';
		self::$foreground_colors ['cyan'] = '0;36';
		self::$foreground_colors ['light_cyan'] = '1;36';
		self::$foreground_colors ['red'] = '0;31';
		self::$foreground_colors ['light_red'] = '1;31';
		self::$foreground_colors ['purple'] = '0;35';
		self::$foreground_colors ['light_purple'] = '1;35';
		self::$foreground_colors ['brown'] = '0;33';
		self::$foreground_colors ['yellow'] = '1;33';
		self::$foreground_colors ['light_gray'] = '0;37';
		self::$foreground_colors ['white'] = '1;37';
		
		self::$background_colors ['black'] = '40';
		self::$background_colors ['red'] = '41';
		self::$background_colors ['green'] = '42';
		self::$background_colors ['yellow'] = '43';
		self::$background_colors ['blue'] = '44';
		self::$background_colors ['magenta'] = '45';
		self::$background_colors ['cyan'] = '46';
		self::$background_colors ['light_gray'] = '47';
	}
	
	// Returns colored string
	public static function g($string, $foreground_color = null, $background_color = null) {
		$colored_string = "";
		
		// Check if given foreground color found
		if (isset ( self::$foreground_colors [$foreground_color] )) {
			$colored_string .= "\033[" . self::$foreground_colors [$foreground_color] . "m";
		}
		// Check if given background color found
		if (isset ( self::$background_colors [$background_color] )) {
			$colored_string .= "\033[" . self::$background_colors [$background_color] . "m";
		}
		
		// Add string and end coloring
		$colored_string .= $string . "\033[0m";
		
		return $colored_string;
	}
}
