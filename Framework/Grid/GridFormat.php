<?php

class GridFormat {

	public static function imgSrc($args) {
		if ($args[Grid::FF_VALUE]) {
			return '<img src="'.$args[Grid::FF_VALUE].'" />';
		}
		return '';
	}

	public static function url($args) {
		if ($args[Grid::FF_VALUE]) {
			return '<a href="'.$args[Grid::FF_VALUE].'" target="_blank">' . Util::escape($args[Grid::FF_VALUE]) . '</a>';
		}
		return '';
	}

	public static function email($args) {
		if ($args[Grid::FF_VALUE]) {
			return '<a href="mailto:'.$args[Grid::FF_VALUE].'">' . Util::escape($args[Grid::FF_VALUE]) . '</a>';
		}
		return '';
	}

	public static function datetime($args) {
		if ($args[Grid::FF_VALUE] == null) return '';

		return Util::formatDateTime($args[Grid::FF_VALUE]);
	}

	public static function date($args) {
		if ($args[Grid::FF_VALUE] == null) return '';

		return Util::formatDate($args[Grid::FF_VALUE]);
	}

	public static function boolean($args) {
		return Util::formatBoolean($args[Grid::FF_VALUE]);
	}
}
