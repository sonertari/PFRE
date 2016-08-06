<?php
/* $pfre: lib.php,v 1.3 2016/08/05 22:30:06 soner Exp $ */

/*
 * Copyright (c) 2016 Soner Tari.  All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 * 1. Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 * 2. Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 * 3. All advertising materials mentioning features or use of this
 *    software must display the following acknowledgement: This
 *    product includes software developed by Soner Tari
 *    and its contributors.
 * 4. Neither the name of Soner Tari nor the names of
 *    its contributors may be used to endorse or promote products
 *    derived from this software without specific prior written
 *    permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE AUTHOR ``AS IS'' AND ANY EXPRESS OR
 * IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES
 * OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
 * IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT
 * NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF
 * THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

/** @file
 * Project-wide common functions.
 */

/** Escapes chars.
 *
 * Prevents double escapes by default.
 *
 * preg_quote() double escapes, thus is not suitable. It is not possible to
 * make sure that strings contain no escapes, because this function is used
 * over strings obtained
 * from config files too, which we don't have any control over.
 *
 * Example: $no_double_escapes as FALSE is used in the code to double escape
 * $ char.
 *
 * @param[in]	$str	string String to process.
 * @param[in]	$chars	string Chars to escape.
 * @param[in]	$no_double_escapes	boolean Whether to prevent double escapes.
 * @return Escaped string.
 */
function Escape($str, $chars, $no_double_escapes= TRUE)
{
	if ($chars !== '') {
		$chars_array= str_split($chars);
		foreach ($chars_array as $char) {
			$esc_char= preg_quote($char, '/');
			if ($no_double_escapes) {
				/// First remove existing escapes
				$str= preg_replace("/\\\\$esc_char/", $char, $str);
			}
			$str= preg_replace("/$esc_char/", "\\\\$char", $str);
		}
	}
 	return $str;
}

function FlattenArray(&$array)
{
	if (count($array) == 1) {
		/// @attention Don't use 0 as key to fetch the last value
		$array= $array[key($array)];
	}
}

function Output($msg)
{
	global $Output;

	if ($Output === '') {
		$Output= $msg;
	}
	else {
		$Output.= "\n".$msg;
	}
	// For transparent use of this function
	return $msg;
}

/** Sets or updates $Error with the given message.
 *
 * Error strings are accumulated in global $Error and returned to View.
 * 
 * @param[in]	$msg	string Error message.
 */
function Error($msg)
{
	global $Error;

	if ($Error === '') {
		$Error= $msg;
	}
	else {
		$Error.= "\n".$msg;
	}
	// For transparent use of this function
	return $msg;
}

/// For classifying gettext strings into files.
function _MENU($str)
{
	return _($str);
}

/// For classifying gettext strings into files.
function _CONTROL($str)
{
	return _($str);
}

/// For classifying gettext strings into files.
function _NOTICE($str)
{
	return _($str);
}

/// For classifying gettext strings into files.
function _TITLE($str)
{
	return _($str);
}

/// For classifying gettext strings into files.
function _HELPBOX($str)
{
	return _($str);
}

/// For classifying gettext strings into files.
function _HELPWINDOW($str)
{
	return _($str);
}

/// For classifying gettext strings into files.
function _TITLE2($str)
{
	return _($str);
}
?>
