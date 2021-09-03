#!/bin/bash
nohup  php  ./stock_script.php > ./logs/'stock_script_'`date +%y-%m-%d_%H%M%S`'.out' 2>&1 &