# Helloprint Task - Distributed Login System

## Requirements
1. VirtualBox - [here](https://www.virtualbox.org/)
2. Vagrant - [here](https://www.vagrantup.com/downloads.html)
3. Virtualization enabled in BIOS (if needed)

## Get the project files
### Pull the project from [github](https://github.com/rumentab/helloprint.git)
Go to an empty folder and type:
````
C:\Users\User\Documents\Projects> git clone https://github.com/rumentab/helloprint.git Helloprint_Project
````
    
## Configure
### Vagrant:
Open the Vagrant.sample file in the v1 folder of the project. Make a copy named Vagrantfile. On line 8 replace 
````
  config.vm.network "public_network", ip: "<free ip address from your network range>" 
````
with a free IP address from your local network range, i.e.
````
  config.vm.network "public_network", ip: "192.168.1.123"
````

### Project config files
Every APP (webserver, producer, consumer) has it's owbn config file
* sources
    - webserver: config.sample.json
    - producer: config.sample.php
    - consumer: config.sample.php
    
Copy every one of them in the same folder and remove the *sample* from the filename.
Mainly in the config files you have to put the ip address you placed in the Vagrantfile like this:
````
'address' => [
    'consumer'  => "ip.of.consumer:4000",      // see v1/Vagrantfile
] 
````
to 
````
'address' => [
    'consumer'  => "192.168.1.123:4000",      // see v1/Vagrantfile
]

````

Open the SQL file sources/consumer/sql/install_db.sql

and place the email addrss of the user you want to be created at line 23:

from
````MySQL
SET @email = 'xxxx@xxxx.xx';
````
to
````MySQL
SET @email = 'user.email@example.com';
````
### Starting project for the first time

Open a terminal window and go into the projects v1 folder
````
C:\Users\User\Documents\Projects\Helloprint_Project> cd v1
````
and power up the virtual machine
````
C:\Users\User\Documents\Projects\Helloprint_Project\v1> vagrant up
````
If started for the firs time, it could take up to 10 minutes everything to be initialized.
You could be asked by the vagrant VM what interface to use for bridge like:
````
1) Intel(R) 82567LM-3 Gigabit Network Connection
2) Hyper-V Virtual Ethernet Adapter #2
==> HelloPrint_Project: When choosing an interface, it is usually the one that is
==> HelloPrint_Project: being used to connect to the internet.
    HelloPrint_Project: Which interface should the network bridge to?
````
Select the number of the interface which is used for the LAN connection of the host machine, it's 1 in this case.

If during the initialization of the VM the process stopps with an error message, then do the following:
````
C:\Users\User\Documents\Projects\Helloprint_Project\v1> vagrant reload --provision
````

If everything is OK, the last line in the terminal should be:
````
    HelloPrint_Project: Generating autoload files
````
Then everything is ready :)

## Run the project
### Description
VM has 3 independant containers created in it - webserver, producer and consumer. Every one of the 3 containers has it's own IP access URL.
Consider you've placed the 192.168.1.123 IP address in the Vagrantfile, then every container could be accessed as follows:

- webserver: http://192.168.1.123:4000
- producer: http://192.168.1.123:4100
- consumer: http://192.168.1.123:4200

### Start the project
Open a browser window and type the webserver URL. You should be redirected to the login page. 

That's all. Enjoy!

### Enter in container
Use the following commands
````
C:\Users\User\Documents\Projects\Helloprint_Project\v1> vagrant ssh
vagrant@virtualbox# sudo docker container list
vagrant@virtualbox# sudo docker exec -it <name of the container> bash
root@A1B2C3G4#
````


## Stop the project
Open a terminal window and go to the project's v1 folder.
````
C:\Users\User\Documents\Projects\Helloprint_Project\v1> vagrant halt
````
This will power off the VM. 
If you want to remove the vagrant files from your system, run
````
C:\Users\User\Documents\Projects\Helloprint_Project\v1> vagrant destroy
````

This will destroy the VM.

## Notices
1. Please do not move or remove any folder within the project folder.