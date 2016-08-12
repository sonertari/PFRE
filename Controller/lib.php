<?php
/* $pfre: lib.php,v 1.14 2016/08/10 04:39:43 soner Exp $ */

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
 * Defs and library functions for Controller.
 */

/** Functions and info strings used in shell arg control.
 *
 * @param[out]	func	Function to check type
 * @param[out]	desc	Info string to use when check failed
 */
$ArgTypes= array(
	FILEPATH	=>	array(
		'func'	=> 'IsFilePath',
		'desc'	=> _('Filepath wrong'),
		),
	NAME		=>	array(
		'func'	=> 'IsName',
		'desc'	=> _('Name wrong'),
		),
	NUM			=>	array(
		'func'	=> 'IsNumber',
		'desc'	=> _('Number wrong'),
		),
	SHA1STR	=>	array(
		'func'	=> 'IsSha1Str',
		'desc'	=> _('Not sha1 encrypted string'),
		),
	BOOL	=>	array(
		'func'	=> 'IsBool',
		'desc'	=> _('Not boolean'),
		),
	SAVEFILEPATH	=>	array(
		'func'	=> 'IsFilePath',
		'desc'	=> _('Filepath wrong'),
		),
	JSON	=>	array(
		'func'	=> 'IsJson',
		'desc'	=> _('Not JSON encoded string'),
		),
);

function IsNumber($str)
{
	return preg_match('/' . RE_NUM . '/', $str);
}

function IsName($str)
{
	return preg_match('/' . RE_NAME . '/', $str);
}

function IsJson($str)
{
	return json_decode($str) !== NULL;
}

function IsSha1Str($str)
{
	return preg_match('/' . RE_SHA1 . '/', $str);
}

function IsEmpty($str)
{
	return empty($str);
}

function IsBool($str)
{
	return preg_match('/' . RE_BOOL . '/', $str);
}

/** Compute and fill arg count variables.
 *
 * @param[in]	$commands	Available commands for the current model
 * @param[in]	$argv		Argument vector
 * @param[in]	$cmd		Method name, key to $commands
 * @param[out]	$actual		Given arg count
 * @param[out]	$expected	Expected arg count
 * @param[out]	$acceptable	Acceptable arg count
 * @param[out]	$check		Arg count used while validating
 */
function ComputeArgCounts($commands, $argv, $cmd, &$actual, &$expected, &$acceptable, &$check)
{
	$actual= count($argv);
	$expected= count($commands[$cmd]['argv']);

	$acceptable= $expected;
	for ($argpos= 0; $argpos < $expected; $argpos++) {
		$argtype= $commands[$cmd]['argv'][$argpos];
		if ($argtype & NONE) {
			$acceptable--;
		}
	}
	
	/// @attention There may be extra or missing args, hence min() here
	$check= min($actual, $expected);
}

/** Checks types of the arguments passed.
 *
 * The arguments are checked against the types listed in $commands.
 *
 * @param[in]	$commands	Available commands for the current model
 * @param[in]	$command	Method name, key to $commands
 * @param[in]	$argv		Argument vector
 * @param[out]	$check		Arg count used while validating
 * @return boolean Validation result
 *
 * @todo There are 2 types of argument checks in this project, which one to choose?
 */
function ValidateArgs($commands, $command, $argv, $check)
{
	global $ArgTypes;

	$helpmsg= $commands[$command]['desc'];
	$logmsg= $commands[$command]['desc'];
	
	$valid= FALSE;
	// Check each argument in order
	for ($argpos= 0; $argpos < $check; $argpos++) {
		$arg= $argv[$argpos];
		$argtype= $commands[$command]['argv'][$argpos];

		// Multiple types may match for an arg, hence the foreach loop
		foreach ($ArgTypes as $type => $conf) {
			// Acceptable types are bitwise ORed, hence the AND here
			if ($argtype & $type) {
				$validatefunc= $conf['func'];
				if ($validatefunc($arg)) {
					$valid= TRUE;

					if ($type & FILEPATH) {
						// Further check if file really exists
						exec("[ -e $arg ]", $output, $retval);
						if ($retval !== 0) {
							$valid= FALSE;

							$errormsg= "$command: $arg";
							Error(_('No such file').": $errormsg");
							pfrec_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, "No such file: $errormsg");
						}
					}

					if ($valid) {
						// One type succeded, hence do not check for other possible types for this arg
						break;
					}
				}
				else {
					$valid= FALSE;
					
					$helpmsg.= "\n"._($conf['desc']).': '.$arg;
					$logmsg.= "\n".$conf['desc'].': '.$arg;
					// Will keep checking if further types are possible for this arg
				}
			}
		}

		if (!$valid) {
			// One arg failed to check, do not run the func
			break;
		}
	}
	
	if (!$valid) {
		Error(_('Arg type check failed').": $helpmsg");
		pfrec_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, "Arg type check failed: $logmsg");
	}
	return $valid;
}
?>
