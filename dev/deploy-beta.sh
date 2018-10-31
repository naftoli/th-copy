echo 'Deleting /beta/'
rm -rf /home/mashpia/public_html/beta/*

echo 'Moving new code to /beta/'
mv /home/mashpia/dev/build/* /home/mashpia/public_html/beta/

echo 'Pull server changes'
git pull

echo '/beta/ deployed'
