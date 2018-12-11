#!/bin/bash

# navigate to this file
cd "$(dirname "$0")"

echo '***********************************************';
echo '************** Pull code changes **************';
echo '***********************************************';
echo '';
cd ../; # go to the repository root
git pull;

echo '';
echo '***********************************************';
echo '********** Install PHP dependencies: **********';
echo '***********************************************';
echo '';

cd mashpia.com;
composer install;
cd ../deploy;

# if we have a build folder
if [ -d build ]; then

  echo '';
  echo '***********************************************';
  echo '********** Moving new code to /new/ ***********';
  echo '***********************************************';
  echo '';

  # if there is something at /new. Delete it
  if [ -d ../mashpia.com/public/new ];
    then rm -rf ../mashpia.com/public/new;
  fi;

  # copy over and deploy the changes
  mkdir ../maspia.com/public/new
  mv build/* ../mashpia.com/public/new/;
  mv build/.[!.]* ../mashpia.com/public/new/;
  rm -rf build; # delete the build folder

fi;
echo '';
echo '***********************************************';
echo '************** Changes Deployed! **************';
echo '***********************************************';
