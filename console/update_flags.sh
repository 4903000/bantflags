#! /bin/bash

montage *.png -geometry 16x11+0+0 ../../montage.png
ls -1 *.png | sed -e 's/\..*$//' | tee  flag_list.txt  ../../flag_list_api2.txt > /dev/null
