#!/bin/sh
# Copyright (c) 2016 Soner Tari.  All rights reserved.
#
# Redistribution and use in source and binary forms, with or without
# modification, are permitted provided that the following conditions
# are met:
# 1. Redistributions of source code must retain the above copyright
#    notice, this list of conditions and the following disclaimer.
# 2. Redistributions in binary form must reproduce the above copyright
#    notice, this list of conditions and the following disclaimer in the
#    documentation and/or other materials provided with the distribution.
# 3. All advertising materials mentioning features or use of this
#    software must display the following acknowledgement: This
#    product includes software developed by Soner Tari
#    and its contributors.
# 4. Neither the name of Soner Tari nor the names of
#    its contributors may be used to endorse or promote products
#    derived from this software without specific prior written
#    permission.
#
# THIS SOFTWARE IS PROVIDED BY THE AUTHOR ``AS IS'' AND ANY EXPRESS OR
# IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES
# OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
# IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT,
# INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT
# NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
# DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
# THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
# (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF
# THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

#/** \file
# Generates gettext po file for the given locale.
#*/

CHECK_ARG_COUNT()
{
	# Check this function's arg count first
	if [ $# -lt 3 ]; then
		echo "$0: Not enough arguments [3]: $#"
		exit 1
	fi

	if [ $3 -lt $2 ]; then
		echo "$1: Not enough arguments [$2]: $3"
		exit 1
	fi
	return 0
}

CHECK_ARG_COUNT $0 3 $#

FOLDER=$1
LOCALE=$2
OUTPUT_FILE=$3

KEYWORD="-k --keyword=$4"
if [ "$4" = ALL -o ! "$4" ]; then
	# TODO: 'ALL' option is never used, remove.
	KEYWORD='--keyword=_CONTROL --keyword=_MENU --keyword=_NOTICE --keyword=_TITLE --keyword=_HELPBOX --keyword=_'
	echo "KEYWORD= $KEYWORD"
fi

echo "Generating php files list"
find $FOLDER -name "*.php" > files.txt

LOCALE_DIR="./View/locale/$LOCALE/LC_MESSAGES"
LOCALE_FILE="$LOCALE_DIR/$OUTPUT_FILE"

if [ ! -d $LOCALE_DIR ]; then
	echo -n "No such directory: $LOCALE_DIR, creating... "
	if ! mkdir -p $LOCALE_DIR; then
		echo "FAILED."
		exit 1
	fi
	echo "Successful."
fi

if [ ! -e $LOCALE_FILE ]; then
	echo -n "No such file: $LOCALE_FILE, creating... "
	if ! touch $LOCALE_FILE; then
		echo "FAILED."
		exit 1
	fi
	echo "Successful."
fi

echo "Generating gettext po file for $LOCALE"
if ! xgettext -L "PHP" -s \
		$KEYWORD \
		--no-location \
		--omit-header \
		--foreign-user \
		--copyright-holder="Soner Tari, The PFRE project" \
		--msgid-bugs-address="sonertari@gmail.com" \
		--package-name="PFRE" \
		--package-version="5.9" \
		-j -o $LOCALE_FILE \
		-f files.txt; then
	echo "FAILED generating $LOCALE_FILE"
	exit 1
fi

echo "Successfully generated $LOCALE_FILE"
exit 0
