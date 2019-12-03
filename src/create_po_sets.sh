#!/bin/sh
# Copyright (C) 2004-2019 Soner Tari
#
# This file is part of UTMFW.
#
# UTMFW is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# UTMFW is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with UTMFW.  If not, see <http://www.gnu.org/licenses/>.

#/** \file
# Generates all po file sets for a given locale using create_po.sh.
# Echoes the number of msgid's in each
# Merges the sets
#*/

if [ $# -lt 1 ]; then
	echo "Not enough arguments [1]: $#"
	exit 1
fi

LOCALE=$1

./create_po.sh . $LOCALE pfre_CONTROL.po _CONTROL
./create_po.sh . $LOCALE pfre_MENU.po _MENU
./create_po.sh . $LOCALE pfre_NOTICE.po _NOTICE
./create_po.sh . $LOCALE pfre_TITLE.po _TITLE
./create_po.sh . $LOCALE pfre_HELPBOX.po _HELPBOX
./create_po.sh . $LOCALE pfre__.po _

echo -n 'CONTROL: '
grep msgid View/locale/$LOCALE/LC_MESSAGES/pfre_CONTROL.po | wc -l

echo -n 'MENU: '
grep msgid View/locale/$LOCALE/LC_MESSAGES/pfre_MENU.po | wc -l

echo -n 'NOTICE: '
grep msgid View/locale/$LOCALE/LC_MESSAGES/pfre_NOTICE.po | wc -l

echo -n 'TITLE: '
grep msgid View/locale/$LOCALE/LC_MESSAGES/pfre_TITLE.po | wc -l

echo -n 'HELPBOX: '
grep msgid View/locale/$LOCALE/LC_MESSAGES/pfre_HELPBOX.po | wc -l

echo -n '_: '
grep msgid View/locale/$LOCALE/LC_MESSAGES/pfre__.po | wc -l

msgcat -s --use-first -o View/locale/$LOCALE/LC_MESSAGES/pfre.po View/locale/$LOCALE/LC_MESSAGES/pfre_*.po
