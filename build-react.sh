#!/bin/bash

# navigate to this file
cd "$(dirname "$0")"

echo '';
echo '***********************************************';
echo '********** Install PHP dependencies: **********';
echo '***********************************************';
echo '';

cd mashpia.com;
composer install;
cd ../;

echo '';
echo '***********************************************';
echo '********** Build Base-Commander App: **********';
echo '***********************************************';
echo '';

cd base-commander;
yarn install;
yarn build:prod;

# if we have a build folder
if [ -d build ]; then

  echo '';
  echo '***********************************************';
  echo '********** Moving new code to /new/ ***********';
  echo '***********************************************';
  echo '';

  # if there is something at /new. Delete it
  echo 'Deleting old builds...';
  if [ -d ../mashpia.com/public/new ];
    then rm -rf ../mashpia.com/public/new;
  fi;

  echo 'Copy new build...';
  # copy over and deploy the changes
  mkdir ../mashpia.com/public/new
  mv build/* ../mashpia.com/public/new/;
  mv build/.[!.]* ../mashpia.com/public/new/;
  rm -rf build; # delete the build folder

  echo '';
  echo '***********************************************';
  echo '************** Changes Deployed! **************';
  echo '***********************************************';

fi;

echo '';
echo '!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!';
echo '!!!!!!!!!!!!!!!!!!! ERROR !!!!!!!!!!!!!!!!!!!!!';
echo '!!!!!!!!!!!!! REACT BUILD FAILED !!!!!!!!!!!!!!';
echo '!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!';
