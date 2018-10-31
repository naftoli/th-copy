echo 'Deleting /api/build/'
rm -rf /home/mashpia/public_html/api/build/*

echo 'Moving new code to /api/build/'
mv /home/mashpia/dev/build/* /home/mashpia/public_html/api/build

echo 'Pull server changes'
git pull

echo '/api/build/ deployed'
