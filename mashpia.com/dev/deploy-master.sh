echo 'Deleting /new/'
rm -rf /home/mashpia/public_html/new/*

echo 'Moving new code to /new/'
mv /home/mashpia/dev/build/* /home/mashpia/public_html/new

echo 'Pull server changes'
git pull

echo '/new/ deployed'
