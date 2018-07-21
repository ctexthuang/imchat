#!/bin/bash
echo '============  git pull  ============';

git pull origin master;

echo '============  git push  ============';

git add api config model public server vendor Server.php README.md
# git rm -r --cached vendor/wensi/log
# git rm -r --cached vendor/wensi/tmp


git add u.sh 
git add start.sh 


git commit -am "正常提交"

git push origin master


