#!/bin/bash 
# script should be used from version v1.1.8.2

helpFunction()
{
   echo ""
   echo "Usage: $0 -u user_name -n experiment_name -p password"
   echo -e "\t-n Experiment name"
   echo -e "\t-p password"   
   echo -e "\t-u OS user owner of the files"   
   exit 1 # Exit script after printing help
}

while getopts "n:p:u:" opt
do
   case "$opt" in
      n ) name="$OPTARG" ;;
      p ) password="$OPTARG" ;;      
	  u ) user="$OPTARG" ;; # user under the installation is done     
      ? ) helpFunction ;; # Print helpFunction in case parameter is non-existent
   esac
done

# Print helpFunction in case parameters are empty
if [ -z "$name" ] || [ -z "$password" ] || [ -z "$user" ]
then
   echo "Some or all of the parameters are empty";
   helpFunction
fi

# Begin script in case all parameters are correct
echo "Start installation"

# got to install script folder. Keep it as a basic path 
cd /home/$user/$name/server/utils

sudo chmod 777 ../../assets
sudo chmod 777 ../../css
echo "Prepare the asset and css folder"

sudo -u $user cp ../service/globals_untracked.default.php ../service/globals_untracked.php
sudo -u $user sed -i "s/__experiment_name__/${name}/g" ../service/globals_untracked.php
sudo -u $user sed -i "s/__password__/${password}/g" ../service/globals_untracked.php
echo "Set the global variables of the experiment"

sudo -u $user cp ../db/create_db.default.sql ../db/create_db.sql
sudo -u $user sed -i "s/__experiment_name__/${name}/g" ../db/create_db.sql
sudo -u $user sed -i "s/__password__/${password}/g" ../db/create_db.sql
sudo mysql < ../db/create_db.sql
echo "Creating database $name"

sudo mysql -D $name < ../db/selfhelp_initial.sql
echo "Databse $name initialized!"
cat ../db/FUN_PRO_VIEWS/*.sql | sudo mysql -u $name -p$password -D $name
echo "Functions, views and proceuderes are created!"

echo "Setting up appache"
sudo -u $user cp ../../server/apache.default.conf ../../server/apache.conf
sudo -u $user sed -i "s/__experiment_name__/${name}/g" ../../server/apache.conf
sudo -u $user sed -i "s/__user__/${user}/g" ../../server/apache.conf
cd /etc/apache2/sites-available
sudo ln -s /home/$user/$name/server/apache.conf $name.conf
cd ../sites-enabled
sudo ln -s ../sites-available/$name.conf .
sudo service apache2 restart
echo "https://selfhelp.psy.unibe.ch/$name should be online!"
echo "Installation is done!"
