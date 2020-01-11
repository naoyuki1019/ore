#!/bin/bash
if [ $# -ne 4 ]; then
  echo 'コマンド引数不正'
  exit 9
fi

ftp -nv << EOF
open ${1} ${2}
user ${3} ${4}
binary
mkdir /aaaatest
mkdir /aaaatest/dir2
mkdir /test
mkdir /test/dir1
mkdir /test/dir2
put /var/www/html/ore-lib/tests/tmp/upload1.txt /test/dir1/uploaded1.txt
put /var/www/html/ore-lib/tests/tmp/upload2.txt /test/dir2/uploaded2.txt
put /var/www/html/ore-lib/tests/tmp/upload2.txt /aaaatest/dir2/uploaded2.txt
close
bye
EOF

exit 0
