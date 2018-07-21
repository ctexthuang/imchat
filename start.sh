while :
do
echo "============================================"
echo "[y]确认拉取 [n]取消"
read check
	case $check in
		y)echo '============  git pull  ============';
		  git fetch --all  
		  git reset --hard origin/master 
		  git pull
		break;;
		n)exit
		break;;
	esac	
done

