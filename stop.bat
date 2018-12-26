@echo off

@echo Killing VirtualBox VM
"C:\Program Files\Oracle\VirtualBox\VBoxManage.exe" controlvm "Tzivos Hashem" poweroff --type headless

@echo Killing all "th-js-server-" processes
taskkill /FI "WindowTitle eq th-js-server-*" /T /F

PAUSE
