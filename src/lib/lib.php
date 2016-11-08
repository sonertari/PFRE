<?php
/*
 * Copyright (C) 2004-2016 Soner Tari
 *
 * This file is part of PFRE.
 *
 * PFRE is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * PFRE is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with PFRE.  If not, see <http://www.gnu.org/licenses/>.
 */

/** @file
 * Project-wide common functions.
 */

/**
 * Validates the given file path.
 * 
 * If we are not testing the controller, we should expect regular file paths.
 * For example, _Include or LoadAnchor type of rules accept regular paths, not test ones.
 * 
 * @attention ? should never appear in regex patterns, this is better than using / or | chars.
 * 
 * @param string $filepath File path to validate.
 */
function IsFilePath($filepath)
{
	global $PF_CONFIG_PATH, $TMP_PATH, $TEST_DIR_PATH;

	return
		// pf configuration files
		preg_match("?^($TEST_DIR_PATH|)$PF_CONFIG_PATH/\w[\w.\-_]*$?", $filepath)
		|| preg_match("?^($TEST_DIR_PATH|)/etc/\w[\w.\-_]*$?", $filepath)
		// Uploaded tmp files
		|| preg_match("?^($TEST_DIR_PATH|)$TMP_PATH/\w[\w.\-_]*$?", $filepath);
}

/**
 * Converts an array to a simple value.
 * 
 * @attention Don't use 0 as key to fetch the last value; the last key index may not be 0.
 * 
 * @param array $array Array to flatten.
 */
function FlattenArray(&$array)
{
	if (count($array) == 1) {
		$array= $array[key($array)];
	}
}
?>
