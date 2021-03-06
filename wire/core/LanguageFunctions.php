<?php namespace ProcessWire;

/**
 * ProcessWire Language Functions
 *
 * Provide GetText like language translation functions to ProcessWire
 * 
 * ProcessWire 3.x, Copyright 2018 by Ryan Cramer
 * https://processwire.com
 *
 */

/**
 * Perform a language translation
 * 
 * ~~~~~~
 * echo __('This is translatable text');
 * echo __('Translatable with current file as textdomain', __FILE__); 
 * echo __('Translatable with other file as textdomain', '/site/templates/_init.php');
 * ~~~~~~
 * 
 * @param string $text Text for translation. 
 * @param string $textdomain Textdomain for the text, may be class name, filename, or something made up by you. If omitted, a debug backtrace will attempt to determine it automatically.
 * @param string $context Name of context - DO NOT USE with this function for translation as it won't be parsed for translation. Use only with the _x() function, which will be parsed. 
 * @return string Translated text or original text if translation not available.
 *
 */
function __($text, $textdomain = null, $context = '') {
	static $useLimit = null;
	if(!wire('languages')) return $text; 
	if(!$language = wire('user')->language) return $text; 
	/** @var Language $language */
	if(!$language->id) return $text; 
	if($useLimit === null) {
		$useLimit = version_compare(PHP_VERSION, '5.4.0') >= 0;
	}
	if(is_null($textdomain)) {
		if($useLimit) {
			// PHP 5.4.0 or newer
			$traces = @debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
		} else if(defined('DEBUG_BACKTRACE_IGNORE_ARGS')) {
			// PHP 5.3.6 or newer
			$traces = @debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
		} else {
			// older PHP (deprecated)
			$traces = @debug_backtrace();
		}
		if(isset($traces[0]) && $traces[0]['file'] != __FILE__) {
			$textdomain = $traces[0]['file'];
		} else if(isset($traces[1]) && $traces[1]['file'] != __FILE__) {
			$textdomain = $traces[1]['file'];
		}
		if(is_null($textdomain)) $textdomain = 'site';
	} else if($textdomain === 'common') {
		// common translation
		$textdomain = 'wire/modules/LanguageSupport/LanguageTranslator.php';
	}
	$value = $language->translator()->getTranslation($textdomain, $text, $context);
	if($value === "=") {
		$value = $text;
	} else if($value === "+") {
		$v = $language->translator()->commonTranslation($text);
		$value = empty($v) ? $text : $v;
	} else {
		$value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8', false);
	}
	return $value;
}

/**
 * Perform a language translation in a specific context
 * 
 * Used when to text strings might be the same in English, but different in other languages. 
 * 
 * ~~~~~
 * echo _x('Click for more', 'button');
 * echo _x('Click for more', 'text-link'); 
 * ~~~~~
 * 
 * @param string $text Text for translation. 
 * @param string $context Name of context
 * @param string $textdomain Textdomain for the text, may be class name, filename, or something made up by you. If omitted, a debug backtrace will attempt to determine automatically.
 * @return string Translated text or original text if translation not available.
 *
 */
function _x($text, $context, $textdomain = null) {
	return __($text, $textdomain, $context); 	
}

/**
 * Perform a language translation with singular and plural versions
 * 
 * ~~~~~
 * $items = array(...);
 * $qty = count($items);
 * echo _n('Found one item', 'Found multiple items', $qty); 
 * echo sprintf(_n('Found one item', 'Found %d items', $qty), $qty);
 * ~~~~~
 * 
 * @param string $textSingular Singular version of text (when there is 1 item)
 * @param string $textPlural Plural version of text (when there are multiple items or 0 items)
 * @param int $count Quantity of items, should be 0 or more.
 * @param string $textdomain Textdomain for the text, may be class name, filename, or something made up by you. If omitted, a debug backtrace will attempt to determine automatically.
 * @return string Translated text or original text if translation not available.
 *
 */
function _n($textSingular, $textPlural, $count, $textdomain = null) {
	return $count == 1 ? __($textSingular, $textdomain) : __($textPlural, $textdomain); 	
}


