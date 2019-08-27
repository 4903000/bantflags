nginx_root=`pwd`/guix-env/nginx/
php_root=`pwd`/guix-env/php/
mysql_root=`pwd`/guix-env/mysql/

start_nginx() {
    nginx -p $nginx_root -c nginx-example.conf
    echo "nginx started!"
}
stop_nginx() {
    nginx -p $nginx_root -c nginx-example.conf -s quit
    echo "nginx stopped!"
}
start_php() {
    php-fpm -p $php_root -c $php_root/php.ini -y $php_root/php-fpm.conf \
	    -g php-fpm.pid
    echo "php started!"
}
stop_php() {
    cat $php_root/php-fpm.pid | xargs kill -SIGTERM
    echo "php stopped!"
}
start_mysql() {
    mysqld --defaults-file=$mysql_root/my.cnf -h $mysql_root/data/ --pid-file=mysql.pid &
    while ! test -S $mysql_root/data/mysql.sock; do
	echo "waiting for socket to appear..."
	sleep 1
    done
    echo "mysql started!"
}
stop_mysql() {
    cat $mysql_root/data/mysql.pid | xargs kill -SIGTERM
    echo "mysql stopped!"
}
reset_mysql() {
    read -p "Everything in $mysql_root/data will be removed. Are you sure? [y/n] " -n 1 -r
    echo
    if [[ $REPLY =~ [Yy]$ ]]
    then
	stop_mysql
	rm -rf $mysql_root/data/*
	mysql_install_db --defaults-file=$mysql_root/my.cnf -h $mysql_root/data/
	start_mysql
	mysql --defaults-file=$mysql_root/my.cnf --socket=$mysql_root/data/mysql.sock -u root < $mysql_root/database.sql
	echo "mysql has been reset!"
    fi
}
reset_dirs() {
    mkdir -p flags/actual_flags flags/dead_flags \
	  guix-env/mysql/data/ guix-env nginx/logs/ guix-env/php/log/
    touch console/suggestions_list.txt console/flags_list.html
}
if [ "$GUIX_ENVIRONMENT" ]
then
    # manage things here
    case $1 in
	quit)
	    stop_php
	    stop_nginx
	    stop_mysql
	    exit
	    ;;
	start|stop|reset)
	    $1_$2
	    ;;
	restart)
	    stop_$2
	    start_$2
	    ;;
	*)
    esac
else
    guix environment --ad-hoc nginx php php-imagick mariadb
fi
