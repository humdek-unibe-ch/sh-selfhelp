#!/bin/bash 
user=www

helpFunction()
{
   echo ""
   echo "Usage: $0 -n experiment_name -p password"
   echo -e "\t-n Experiment name"
   echo -e "\t-p password"   
   exit 1 # Exit script after printing help
}

while getopts "n:p:" opt
do
   case "$opt" in
      n ) name="$OPTARG" ;;
      p ) password="$OPTARG" ;;      
      ? ) helpFunction ;; # Print helpFunction in case parameter is non-existent
   esac
done

# Print helpFunction in case parameters are empty
if [ -z "$name" ] || [ -z "$password" ]
then
   echo "Some or all of the parameters are empty";
   helpFunction
fi

# Begin script in case all parameters are correct

chmod 777 ../../assets
chmod 777 ../../css
echo "Prepare the asset and css folder"

sudo -u $user cp ../service/globals_untracked.default.php ../service/globals_untracked.php
sudo -u $user sed -i "s/__experiment_name__/${name}/g" ../service/globals_untracked.php
sudo -u $user sed -i "s/__password__/${password}/g" ../service/globals_untracked.php
echo "Set the global variables of the experiment"

sudo -u $user cp ../db/privileges.default.sql ../db/privileges.sql
sudo -u $user sed -i "s/__experiment_name__/${name}/g" ../db/privileges.sql
echo "Prepare the database script"

sudo -u $user cp ../db/create_db.default.sql ../db/create_db.sql
sudo -u $user sed -i "s/__experiment_name__/${name}/g" ../db/create_db.sql
sudo -u $user sed -i "s/__password__/${password}/g" ../db/create_db.sql
sudo mysql < ../db/create_db.sql
echo "Creating database $name"

sudo mysql -D $name < ../db/selfhelp_initial.sql
echo "Databse $name initialized!"
sudo mysql -u $name -p$password -D $name < ../db/FUN_PRO_VIEWS/fun_pro_views.sql
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