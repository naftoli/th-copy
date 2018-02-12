echo "fix_permissions.sh -> Fixing file and folder permissions for SuPHP!";
echo "This script may take a while to run, please be paitient.";

echo "Changing all folders in public_html to 755....";
find /home/mashpia/public_html -type d -exec chmod 755 {} \;

echo "Changing all files in public_html to 644...."
find /home/mashpia/public_html -type f -exec chmod 644 {} \;

