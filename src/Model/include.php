<?php
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
 * Includes, defines, and functions used in the Model.
 */

$ROOT= dirname(dirname(dirname(__FILE__)));
$SRC_ROOT= dirname(dirname(__FILE__));

require_once($SRC_ROOT . '/lib/defs.php');
require_once($SRC_ROOT . '/lib/setup.php');
require_once($SRC_ROOT . '/lib/lib.php');

require_once($MODEL_PATH.'/validate.php');

require_once($MODEL_PATH.'/lib/RuleSet.php');
require_once($MODEL_PATH.'/lib/Rule.php');
require_once($MODEL_PATH.'/lib/Timeout.php');
require_once($MODEL_PATH.'/lib/State.php');
require_once($MODEL_PATH.'/lib/FilterBase.php');
require_once($MODEL_PATH.'/lib/Filter.php');
require_once($MODEL_PATH.'/lib/Antispoof.php');
require_once($MODEL_PATH.'/lib/Anchor.php');
require_once($MODEL_PATH.'/lib/NatBase.php');
require_once($MODEL_PATH.'/lib/NatTo.php');
require_once($MODEL_PATH.'/lib/BinatTo.php');
require_once($MODEL_PATH.'/lib/RdrTo.php');
require_once($MODEL_PATH.'/lib/AfTo.php');
require_once($MODEL_PATH.'/lib/DivertTo.php');
require_once($MODEL_PATH.'/lib/DivertPacket.php');
require_once($MODEL_PATH.'/lib/Route.php');
require_once($MODEL_PATH.'/lib/Macro.php');
require_once($MODEL_PATH.'/lib/Table.php');
require_once($MODEL_PATH.'/lib/Queue.php');
require_once($MODEL_PATH.'/lib/Scrub.php');
require_once($MODEL_PATH.'/lib/Option.php');
require_once($MODEL_PATH.'/lib/Limit.php');
require_once($MODEL_PATH.'/lib/LoadAnchor.php');
require_once($MODEL_PATH.'/lib/Include.php');
require_once($MODEL_PATH.'/lib/Comment.php');
require_once($MODEL_PATH.'/lib/Blank.php');

/**
 * Shell command argument types.
 *
 * @attention PHP is not compiled, otherwise would use bindec()
 * 
 * @warning Do not use bitwise shift operator either, would mean 100+ shifts for constant values!
 */
define('NONE',			1);
define('FILEPATH',		2);
define('NAME',			4);
define('NUM',			8);
define('SHA1STR',		16);
define('BOOL',			32);
define('SAVEFILEPATH',	64);
define('JSON',			128);

$Output= '';
$Error= '';

/**
 * Sets or updates $Output with the given message.
 *
 * Output strings are accumulated in global $Output var and returned to View.
 * 
 * We return the given $msg param as is, so that we can use this function to print
 * the same message in the logs too.
 * 
 * @param string $msg Output message.
 */
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

/**
 * Sets or updates $Error with the given message.
 *
 * Error strings are accumulated in global $Error var and returned to View.
 * 
 * We return the given $msg param as is, so that we can use this function to print
 * the same message in the logs too.
 * 
 * @param string $msg Error message.
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

/**
 * Wrapper for controller error logging via syslog.
 *
 * A global $LOG_LEVEL is set in setup.php.
 *
 * @param int $prio	Log priority checked against $LOG_LEVEL
 * @param string $file Source file the function is in
 * @param string $func Function where the log is taken
 * @param int $line	Line number within the function
 * @param string $msg Log message
 */
function pfrec_syslog($prio, $file, $func, $line, $msg)
{
	global $LOG_LEVEL, $LOG_PRIOS;

	try {
		openlog('pfrec', LOG_PID, LOG_LOCAL0);
		
		if ($prio <= $LOG_LEVEL) {
			$func= $func == '' ? 'NA' : $func;
			$log= "$LOG_PRIOS[$prio] $file: $func ($line): $msg\n";
			if (!syslog($prio, $log)) {
				if (!fwrite(STDERR, $log)) {
					echo $log;
				}
			}
		}
		closelog();
	}
	catch (Exception $e) {
		echo 'Caught exception: ',  $e->getMessage(), "\n";
		echo "pfrec_syslog() failed: $prio, $file, $func, $line, $msg\n";
		// No need to closelog(), it is optional
	}
}

/**
 * Escapes chars.
 *
 * Prevents double escapes by default.
 *
 * preg_quote() double escapes, thus is not suitable. It is not possible to
 * make sure that strings contain no escapes, because this function is used
 * over strings obtained from config files too, which we don't have any control over.
 *
 * Example: $no_double_escapes as FALSE is used in the code to double escape the $ char.
 *
 * @param string $str String to process.
 * @param string $chars Chars to escape.
 * @param bool $no_double_escapes Whether to prevent double escapes.
 * @return string Escaped string.
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
?>