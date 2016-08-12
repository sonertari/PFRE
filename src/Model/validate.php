<?php
/* $pfre: validate.php,v 1.3 2016/08/11 06:37:41 soner Exp $ */

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

define('RE_BOOL', '^[01]$');
define('RE_NAME', '^[\w_.-]{0,50}$');
define('RE_NUM', '^\d{1,20}$');
define('RE_SHA1', '^[a-f\d]{40}$');

// "Macro names must start with a letter, digit, or underscore, and may contain any of those characters"
$RE_ID= '[\w_-]{1,50}';
define('RE_ID', "^$RE_ID$");

$RE_MACRO_VAR= '\$' . $RE_ID;

/// @todo What are possible macro values?
define('RE_MACRO_VALUE', '^((\w|\$)[\w_.\/\-*]{0,49}|)$');

$RE_IF_NAME= '\w{1,20}';
$RE_IF_MODIF= '(|:(0|broadcast|network|peer))';

$RE_IF= "($RE_IF_NAME|$RE_MACRO_VAR)$RE_IF_MODIF";
define('RE_IF', "^$RE_IF$");

$RE_IF_PAREN= "\(\s*$RE_IF\s*\)";
define('RE_IFSPEC', "^(|!)($RE_IF|$RE_IF_PAREN)$");

$RE_PROTO= '[\w-]{1,50}';
define('RE_PROTOSPEC', "^($RE_PROTO|$RE_MACRO_VAR)$");

define('RE_AF', '^(inet|inet6)$');
define('RE_DIRECTION', '^(in|out)$');

$RE_IP= '\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}';
// pfctl gets stuck if there are no spaces around the dash -
$RE_IP_RANGE= "$RE_IP\s+\-\s+$RE_IP";
$RE_IP6= '[\w:.\/]{1,50}';

/// @todo Is dash - possible in hostnames?
$RE_HOSTNAME= '[\w.\/_]{1,100}';

$RE_ADDRESS_KEYWORDS= '(any|no\-route|self|urpf\-failed)';

$RE_WEIGHT= '(|\s+weight\s+\d{1,5})';

$RE_ADDRESS_BASE= "($RE_IF|$RE_IF_PAREN|$RE_HOSTNAME|$RE_ADDRESS_KEYWORDS|$RE_IP|$RE_IP_RANGE|$RE_IP6|$RE_MACRO_VAR)";
$RE_ADDRESS= "($RE_IF|$RE_IF_PAREN|$RE_HOSTNAME|$RE_ADDRESS_KEYWORDS|$RE_IP|$RE_IP_RANGE|$RE_IP6|$RE_MACRO_VAR)$RE_WEIGHT";
$RE_ADDRESS_NET= "$RE_ADDRESS_BASE\s*\/\s*\d{1,2}$RE_WEIGHT";

$RE_TABLE_VAR= "<$RE_ID>";

$RE_TABLE_ADDRESS= "($RE_HOSTNAME|$RE_IF|self|$RE_IP|$RE_IP6|$RE_MACRO_VAR)";
$RE_TABLE_ADDRESS_NET= "$RE_TABLE_ADDRESS\s*\/\s*\d{1,2}";
define('RE_TABLE_ADDRESS', "^(|!)($RE_TABLE_ADDRESS|$RE_TABLE_ADDRESS_NET)$");

$RE_HOST= "(|!)($RE_ADDRESS|$RE_ADDRESS_NET|$RE_TABLE_VAR$RE_WEIGHT)";

define('RE_HOST', "^$RE_HOST$");
define('RE_REDIRHOST', "^($RE_ADDRESS|$RE_ADDRESS_NET)$");

$RE_HOST_AT_IF= "$RE_HOST\s*@\s*$RE_IF";
$RE_IF_ADDRESS_NET= "\(\s*$RE_IF(|\s+$RE_ADDRESS|\s+$RE_ADDRESS_NET)\s*\)$";

define('RE_ROUTEHOST', "^($RE_HOST|$RE_HOST_AT_IF|$RE_IF_ADDRESS_NET)$");

$RE_PORT= '[\w<>=!:\s-]{1,50}';
define('RE_PORT', "^($RE_PORT|$RE_MACRO_VAR)$");

$RE_PORTSPEC= '[\w*:\s-]{1,50}';
define('RE_PORTSPEC', "^($RE_PORTSPEC|$RE_MACRO_VAR)$");

$RE_FLAGS= '([FSRPAUEW\/]{1,10}|any)';
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
define('RE_TYPE', '^[a-z-]{1,30}$');

define('RE_SOURCE_HASH_KEY', '^\w{16,}$');

define('RE_BLOCKPOLICY', '^(drop|return)$');
define('RE_STATEPOLICY', '^(if-bound|floating)$');
define('RE_OPTIMIZATION', '^(normal|high-latency|satellite|aggressive|conservative)$');
define('RE_RULESETOPTIMIZATION', '^(none|basic|profile)$');
define('RE_DEBUG', '^(emerg|alert|crit|err|warning|notice|info|debug)$');
define('RE_REASSEMBLE', '^(yes|no)$');

define('RE_BANDWIDTH', '^\d{1,16}(|K|M|G)$');
define('RE_BWTIME', '^\d{1,16}ms$');

define('RE_REASSEMBLE_TCP', '^tcp$');

define('RE_CONNRATE', '^\d{1,20}\/\d{1,20}$');
define('RE_SOURCETRACKOPTION', '^(rule|global)$');

define('RE_ICMPCODE', '^[\w-]{1,20}$');
?>