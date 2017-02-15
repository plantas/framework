<?php

class GridSummary {

	public static function sum($args) {
		$values = $args[Grid::SF_VALUES];
		return array_sum($values);
	}

	public static function count($args) {
		$values = $args[Grid::SF_VALUES];
		$i = 0;
		foreach ($values as $v) {
			if ($v) $i++; 
		}
		return $i;
	}

	public static function avg($args) {
		$cnt = count($args[Grid::SF_VALUES]);
		if ($cnt == 0) return 0;
		return self::sum($args) / $cnt;
	}

	// sumira format hh:mm TODO hh:mm:ss - dodati novu funkciju
	public static function sumTime($args) {
		$values = $args[Grid::SF_VALUES];
		$sum = 0;
		foreach ($values as $v) {
//			if (preg_match('!(\d{1,2}):(\d{2}):(\d{2})!', $v, $m)) {
//				$sum += $m[1] * 3600; 
//				$sum += $m[2] * 60;
//				$sum += $m[3];
//			} else 
			if (preg_match('!(\d{1,2}):(\d{2})!', $v, $m)) {
				$sum += $m[1] * 60; 
				$sum += $m[2];
			}
		}
		if ($sum == 0) return 0;

		$hours = (int) ($sum / 60);
		$minutes = (int) ($sum - $hours * 60);
		return sprintf('%02d:%02d', $hours, $minutes);

//		// format
//		$out = '';
//		while ($sum > 0) {
//			$rest = (int) $sum % 60;
//			$sum = ($sum - $rest) / 60;
//			$out = sprintf('%02d', $rest) . ($out ? ':' : '') . $out;
//		}
//		return $out;
	}
}
