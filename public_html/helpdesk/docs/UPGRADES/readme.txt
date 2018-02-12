UPGRADE INSTRUCTIONS
Important: Any custom changes or modifications will be lost when the upgrade is performed
----------------------------------------------------------------------------------------------

v3.1+
-----------------

  Download the patch update and follow the instructions:
  https://www.maiansupport.com/download/patch/latest-version.zip


Other Versions:
-----------------

1. Make a backup of your support system and database.

2. Download the latest version of Maian Support and unzip to desktop:
   https://www.maiansupport.com/download.html

3. Remove 'control/connect.inc.php' & 'licence.lic' from the new version.

   Note that 'control/connect.inc.php' must be renamed as 'control/connect.php' for later versions.

4. If you added additional priority levels to 'control/priority-levels.php' in v2.1, ensure this file is in your
   installation before you upgrade the database.  This is not required if you are running 2.2 or higher already.

5. Overwrite your installation with the new file set of the latest version.

6. Access your support 'install/upgrade.php' file in a browser and follow the instructions.

   NOTE: install/upgrade.php NOT install/index.php

7. Once the upgrade is complete, remove or rename the 'install' folder.

8. Refer to the latest version installation instructions for any new folders that require permissions:
   "Step 4: Permissions" > docs/install_2.html

9. Refer to the latest version installation instructions for any new crontabs/job that may require setting up:
   "Step 7: Crontabs/Cronjobs" > docs/install_2.html

10. Finally, refer to the changelog to see whats new:
    https://www.maiansupport.com/changelog.txt


----------------
Problems
----------------

If the database update doesn`t work, try it again. Go into your database`s 'msp_settings' table and reset the 'softwareVersion'
value back to the previous version, then re-run the upgrade.

Your servers mysql error log may reveal details of why upgrades are failing. Or check the Maian Support 'logs' folder.

----------------
Changelog
----------------

https://www.maiansupport.com/changelog.txt