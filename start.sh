#! /bin/bash

# open the VM
printf "Starting Tzivos Hashem VM\n";
VBoxManage startvm "Tzivos Hashem" --type headless;

# open the editor
printf "\nOpen Code Editor"
code .;

printf "\nWait for 5 Seconds till VM is alive and ready\n"
sleep 5;

printf "\nOpen Base-Commander dev and testing servers\n"
cd base-commander;

# start the webpack server for base commander
gnome-terminal --tab -- bash -c 'yarn start; read line';

# start the tests for base commander
gnome-terminal --tab -- bash -c 'yarn test; read line';

printf "\nGoing back to project root\n"
cd ../;

echo 'Enviroment opened';