#! /bin/bash

# close the VM
echo 'Closing Tzivos Hashem VM'
VBoxManage controlvm "Tzivos Hashem" poweroff --type headless

echo 'Killing all node processes since pids are incorrect'
killall -KILL node

echo 'Enviroment Closed.'