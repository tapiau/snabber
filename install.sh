#!/usr/bin/env bash

EXE="/sbin/snabber"
DIR=`dirname $0`

echo "#!/usr/bin/php\n" > ${EXE}
echo "<?php\n" >> ${EXE}

cat $DIR/src/snapper.php | grep -v "<?php" >>${EXE}
cat $DIR/src/Url.php | grep -v "<?php" >>${EXE}
cat $DIR/src/BtrfsSnapper.php | grep -v "<?php" >>${EXE}

chmod a+x ${EXE}
