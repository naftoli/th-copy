# Tzivos Hashem Backend / Legacy Source Code Repository

This repository contains all the code needed to startup the mashpia.com ( tzivos hashem ) web-app.

Please note that there are many files in here which are not being used at the moment. Such as .xls and .csv files used in the creation of tasks and seed data.

### Does not include:

* User uploaded images ( stored on server )
* Up To Date Database Schema ( Do not have proper database seperation yet )

## Setup instructions

### Server Deployment:

#### System Requirments:

1. CentOS 7
2. PHP 7.2
3. MySQL
    1. For schema please see current production database
4. Apache 2.4+

#### Instructions:

1. Clone the `master`/`beta`/`alpha` branch to the desired deployment location.
2. Edit `httpd.conf` ( or relevent apache configuration ) to point to the `public` directory in this repository.
3. Configure the DNS to be `mashpia.com` ( or a subdirectory of it ).
    1. If you do not do this please make sure that the database is accessible at `localhost:3306`

### Local Development:

#### Requirments:

1. VT-x / AMD-V support ( enabled )
2. 64bit Operating System ( Windows/Mac/Linux )
3. 2GB available RAM
4. VirtualBox
5. `mashpia.ova` image.

#### Setup:

1. Clone the repository to the desired location on your computer. Preferably in a place that is easy to access.

2. Setup Virtualbox:
    1. Install the VirtualBox and the VirtualBox Extension Pack from https://www.virtualbox.org/wiki/Downloads

    2. Import the `mashpia.ova` image with `file -> Import Appliance` (`Ctrl - I` on Windows ) in virtual box.

    3. Select the new image and press `Settings` and verify the following options:
        1. `General -> Basic`: Type should be `Linux` and Version should be `Red Hat 64-bit`
        2. `Storage -> Controller SATA`: This should be set to `Mashpia.vdi`
        3. `Network`: There should be two Adapters enabled:
            1. Adapter 1 should be set to `NAT` for internet access
            2. Adapter 2 should be attached to a `Host-only Adapter` (see Setup step 2.4)
        4. `Shared Folders` Please edit the existing shared folder ( or create a new one with the following properties ):
            1. Folder name **must** be `tzivoshashem`.
            2. Folder path **must** point to the *repository root* on **your system** ( this may be case sensitve )
            3. Auto-mount should be `enabled`
            4. Read only should be `disabled`

    4. Select `file -> Host Network Manager` ( `Ctrl - W` on Windows ). Make sure you set the adapter you pointed to in setp 2.3.2 with the following settings:
        1. IPv4 Address/Mask: `192.168.56.1/24`
        2. DHCP Server: `Enabled`

    5. Once logged in, open a web browser on your *host computer* (not virtual machine) and navigate to the following IP address: `192.168.56.101`. You should now see the login page for `mashpia.com`

3. To ease development you may want to direct this IP address to `mashpia.local` in your hosts file. For instructions on how to edit your Operating Systems Hosts file please see this page: https://www.howtogeek.com/howto/27350/beginner-geek-how-to-edit-your-hosts-file/

### Access the machine:

1. Start the Virtual Machine and wait for it to prompt you with a login screen for the user `mashpia`.

2. Login with the password `tzivos5778` ( this is also your sudo and root password )

### Change PHP version

Add the following to a .htacess folder in development
```
<FilesMatch \.php$>
    SetHandler "proxy:fcgi://127.0.0.1:90XX"
</FilesMatch>
```

replace XX with `72` for `7.2.13` (default) and `56` for `5.6.39`

***Please note that this will break production and should be used for development only.***
