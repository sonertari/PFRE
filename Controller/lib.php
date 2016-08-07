<?php
/* $pfre: lib.php,v 1.9 2016/08/07 00:45:30 soner Exp $ */

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

/** Wrapper for controller error logging via syslog.
 *
 * A global $LOG_LEVEL is set in setup.php.
 *
 * @param[in]	$prio	Log priority checked against $LOG_LEVEL
 * @param[in]	$file	Source file the function is in
 * @param[in]	$func	Function where the log is taken
 * @param[in]	$line	Line number within the function
 * @param[in]	$msg	Log message
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

define('RE_BOOL', '^[01]$');
define('RE_NAME', '^[\w_.-]{0,50}$');
define('RE_NUM', '^\d{1,20}$');
define('RE_SHA1', '^[a-f\d]+$');

// "Macro names must start with a letter, digit, or underscore, and may contain any of those characters"
$RE_ID= '[\w_-]{1,50}';
define('RE_ID', "^$RE_ID$");

$RE_MACRO_VAR= '\$' . $RE_ID;

/// @todo What are possible macro values?
define('RE_MACRO_VALUE', '^(\w|\$)[\w_.\/\-*]{0,50}$');

$RE_IF_NAME= '\w{1,20}';
$RE_IF= "($RE_IF_NAME|$RE_MACRO_VAR)(|:\w+)";
define('RE_IF', "^$RE_IF$");

$RE_IF_PAREN= "\($RE_IF\)";
define('RE_IFSPEC', "^(|!)($RE_IF|$RE_IF_PAREN)$");

$RE_PROTO= '[\w-]{1,50}';
define('RE_PROTOSPEC', "^($RE_PROTO|$RE_MACRO_VAR)$");

define('RE_AF', '^(inet|inet6)$');
define('RE_DIRECTION', '^(in|out)$');

$RE_IP= '\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}';
$RE_IP_RANGE= "$RE_IP\s+\-\s+$RE_IP";
$RE_IP6= '[\w:.\/]+';

/// @todo Is dash - possible in hostnames?
$RE_HOSTNAME= '[\w.\/_]{1,100}';

$RE_ADDRESS_KEYWORDS= '(any|no\-route|self|urpf\-failed)';

$RE_ADDRESS_BASE= "($RE_IF_NAME|$RE_IF_PAREN|$RE_HOSTNAME|$RE_ADDRESS_KEYWORDS|$RE_IP|$RE_IP_RANGE|$RE_IP6|$RE_MACRO_VAR)";
$RE_ADDRESS= "($RE_IF_NAME|$RE_IF_PAREN|$RE_HOSTNAME|$RE_ADDRESS_KEYWORDS|$RE_IP|$RE_IP_RANGE|$RE_IP6|$RE_MACRO_VAR)(|\s+weight\s+\d+)";
$RE_ADDRESS_NET= "$RE_ADDRESS_BASE\s*\/\s*\d{1,2}(|\s+weight\s+\d+)";

$RE_TABLE_VAR= "<$RE_ID>";

$RE_TABLE_ADDRESS= "($RE_HOSTNAME|$RE_IF_NAME|self|$RE_IP|$RE_IP6|$RE_MACRO_VAR)";
$RE_TABLE_ADDRESS_NET= "$RE_TABLE_ADDRESS\s*\/\s*\d{1,2}";
define('RE_TABLE_ADDRESS', "^(|!)($RE_TABLE_ADDRESS|$RE_TABLE_ADDRESS_NET)$");

$RE_HOST= "(|!)($RE_ADDRESS|$RE_ADDRESS_NET|$RE_TABLE_VAR)";

define('RE_HOST', "^$RE_HOST$");
define('RE_REDIRHOST', "^($RE_ADDRESS|$RE_ADDRESS_NET)$");

$RE_HOST_AT_IF= "$RE_HOST\s*@\s*$RE_IF_NAME";
$RE_IF_ADDRESS_NET= "\($RE_IF_NAME(|\s+$RE_ADDRESS|\s+$RE_ADDRESS_NET)\)$";

define('RE_ROUTEHOST', "^($RE_HOST|$RE_HOST_AT_IF|$RE_IF_ADDRESS_NET)$");

$RE_PORT= '[\w<>=!:\s-]{1,50}';
define('RE_PORT', "^($RE_PORT|$RE_MACRO_VAR)$");

$RE_PORTSPEC= '[\w*:\s-]{1,50}';
define('RE_PORTSPEC', "^($RE_PORTSPEC|$RE_MACRO_VAR)$");

$RE_FLAGS= '[FSRPAUEWany\/]{1,10}';
define('RE_FLAGS', "^($RE_FLAGS|$RE_MACRO_VAR)$");

$RE_W_1_10= '^\w{1,10}$';
define('RE_W_1_10', "^($RE_W_1_10|$RE_MACRO_VAR)$");

define('RE_STATE', '^(no|keep|modulate|synproxy)$');
define('RE_PROBABILITY', '^[\d.]{1,10}(|%)$');

$RE_OS= '[\w.*:\/_\s-]{1,50}';
define('RE_OS', "^($RE_OS|$RE_MACRO_VAR)$");

define('RE_ANCHOR_ID', '^[\w_\/*-]{1,100}$');

define('RE_BLANK', "^\n{0,10}$");
/// @todo Should we disallow $ and ` chars in comments?
//define('RE_COMMENT_INLINE', '^[^$`]{0,100}$');
define('RE_COMMENT_INLINE', '^[\s\S]{0,100}$');
define('RE_COMMENT', '^[\s\S]{0,1000}$');

define('RE_ACTION', '^(pass|match|block)$');
define('RE_BLOCKOPTION', '^(drop|return|return-rst|return-icmp|return-icmp6)$');

/// @todo Enum types instead
define('RE_TYPE', '^[a-z-]{0,30}$');

define('RE_SOURCE_HASH_KEY', '^\w{16,}$');

define('RE_BLOCKPOLICY', '^(drop|return)$');
define('RE_STATEPOLICY', '^(if-bound|floating)$');
define('RE_OPTIMIZATION', '^(normal|high-latency|satellite|aggressive|conservative)$');
define('RE_RULESETOPTIMIZATION', '^(none|basic|profile)$');
define('RE_DEBUG', '^(emerg|alert|crit|err|warning|notice|info|debug)$');
define('RE_REASSEMBLE', '^(yes|no)$');

define('RE_BANDWIDTH', '^\w{1,16}(|K|M|G)$');
define('RE_BWTIME', '^\w{1,16}ms$');

define('RE_REASSEMBLE_TCP', '^tcp$');

define('RE_CONNRATE', '^\d{1,20}\/\d{1,20}$');
define('RE_SOURCETRACKOPTION', '^(rule|global)$');

define('RE_ICMPCODE', '^[\w-]{1,20}$');

/// @attention PHP is not compiled, otherwise would use bindec()
/// @warning Do not use bitwise shift operator either, would mean 100+ shifts for constant values!
/// Shell command argument types
define('NONE',			1);
define('FILEPATH',		2);
define('NAME',			4);
define('NUM',			8);
define('SHA1STR',		16);
define('BOOL',			32);
define('SAVEFILEPATH',	64);
define('JSON',			128);

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

function IsFilePath($str)
{
	global $PF_CONFIG_PATH, $TMP_PATH;

	return
		// For CVS Tag displayed in the footer
		preg_match('|^/var/www/htdocs/pfre/View/\w[\w./\-_]*$|', $str)
		// pf configuration files
		|| preg_match("|^$PF_CONFIG_PATH/\w[\w.\-_]*$|", $str)
		|| preg_match("|^/etc/\w[\w.\-_]*$|", $str)
		// Uploaded tmp files
		|| preg_match("|^$TMP_PATH/\w[\w.\-_]*$|", $str);
}

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

function IsInlineAnchor($str, $force= FALSE)
{
	global $LOG_LEVEL, $Nesting;

	$result= FALSE;
	
	$max= $Nesting + 1 > 2;
	if ($max) {
		Error("Validation Error: Reached max nesting for inline anchors: <pre>" . htmlentities(print_r($str, TRUE)) . '</pre>');
		pfrec_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Validation Error: Reached max nesting for inline anchors: $str");
	}

	if (!$max || $force) {
		$Nesting++;
		$ruleSet= new RuleSet();
		$result= $ruleSet->parse($str, $force);
		if (!$result) {
			if (LOG_DEBUG <= $LOG_LEVEL) {
				Error('Validation Error: Invalid inline rules, parser output: <pre>' . htmlentities(print_r(json_decode(json_encode($ruleSet), TRUE), TRUE)) . '</pre>');
			}
			pfrec_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, 'Validation Error: Invalid inline rules: ' . print_r(json_decode(json_encode($ruleSet), TRUE), TRUE));
		}
		$Nesting--;
	}
	return $result;
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
