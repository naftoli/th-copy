echo 'Pull code changes:'
cd ../../
git pull

echo 'Install PHP dependencies:'

cd mashpia.com
composer install
cd ../deploy

echo ''
echo 'Deleting existing mashpia.com/public/new/'
rm -rf ../mashpia.com/public/new/*

echo 'Moving new code to mashpia.com/public/new/'
mkdir ../mashpia.com/public/new
mv build/* ../mashpia.com/public/new
mv build/.[!.]* ../mashpia.com/public/new
rm -rf build # delete the build folder

echo 'Changes Deployed'
