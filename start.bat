@echo off

@echo Starting Tzivos Hashem VM

"C:\Program Files\Oracle\VirtualBox\VBoxManage.exe" startvm "Tzivos Hashem" --type headless

@echo Start Base-Commander Webpack server

start "th-js-server-webpack" cmd /k "cd base-commander && yarn start"

@echo Start Base-Commander Test Suite

start "th-js-server-testing" cmd /k "cd base-commander && yarn test"

@echo Starting VS Code

code .
