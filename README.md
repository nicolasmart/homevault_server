<p align="center">
  <img height="100" src="https://raw.githubusercontent.com/nicolasmart/homevault_server_windows_easyphp/main/eds-www/res/drawables/homevault_logo_big.svg"/>
</p>

## HomeVault Server

HomeVault is a cloud storage service you can easily host at home. The server part works on any platform that supports PHP and MySQL. The apps offer file storage, automatic photo backup and more coming soon.

If you're looking for the client apps, the server includes a web based one, acessible at the default HTTP port and you can use the same address in the [HomeVault Android app](https://github.com/nicolasmart/homevault_client_android).

### Setup instructions for the major platforms

To get started with HomeVault, you simply need to get a PHP server and an empty MySQL database running. Afterwards just copy the HomeVault Server content from this repository to the default HTTP directory, load the server address in your browser and follow the simple steps shown on screen. After logging into the administrator account you can add more users from the top right user menu.

Luckily all of that is much easier to do than it sounds.

#### Platforms with an available snapshot (the HomeVault snapshot eliminates the need for any setup and just runs the server on `localhost`):
- [Windows](https://github.com/nicolasmart/homevault_server_windows_easyphp)

#### Instructions for a PHP and MySQL setup on other platforms:
- [Ubuntu](https://www.digitalocean.com/community/tutorials/how-to-install-linux-apache-mysql-php-lamp-stack-on-ubuntu-20-04)
- [CentOS 7](https://www.digitalocean.com/community/tutorials/how-to-install-linux-apache-mysql-php-lamp-stack-on-centos-7)
- [Raspberry Pi](https://randomnerdtutorials.com/raspberry-pi-apache-mysql-php-lamp-server/)
- [Synology NAS](https://www.synology.com/en-nz/knowledgebase/DSM/tutorial/Service_Application/How_to_host_a_website_on_Synology_NAS)
- [macOS](https://jasonmccreary.me/articles/install-apache-php-mysql-mac-os-x-catalina/)

After installing the server successfully, you need to enable port forwarding to the default HTTP port on your router. You can learn how to do that [here](https://www.noip.com/support/knowledgebase/general-port-forwarding-guide/). Unless your IP is static (most aren't) using a dynamic DNS service is recommended for your own comfort. One of the most popular free services that offer that is [No-IP](https://www.noip.com/).