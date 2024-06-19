#!/bin/bash 
# script should be used from version v1.1.8.2

helpFunction()
{
   echo ""
   echo "Usage: $0 -n experiment_name -p password"
   echo -e "\t-n Experiment name"
   echo -e "\t-p password"   
   exit 1 # Exit script after printing help
}

# Get the directory of the script
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )"

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

# Get the username of the invoking user
user="$SUDO_USER"

# Begin script
echo "Start installation"

# Go to install script folder
cd "$SCRIPT_DIR"
echo "Script dir: $SCRIPT_DIR"
echo "Sudo user: $SUDO_USER"

# Create necessary directories and set permissions
sudo -u "$user" mkdir -p ../../static
sudo chmod 777 ../../static
sudo chmod 777 ../../assets
sudo chmod 777 ../../css
echo "Prepare the asset and css folder"

# Set global variables of the experiment
sudo -u "$user" cp ../service/globals_untracked.default.php ../service/globals_untracked.php
sudo -u "$user" sed -i "s/__project_name__/${name}/g" ../service/globals_untracked.php
sudo -u "$user" sed -i "s/__password__/${password}/g" ../service/globals_untracked.php
echo "Set the global variables of the experiment"

# Create and initialize the database
sudo -u "$user" cp ../db/create_db.default.sql ../db/create_db.sql
sudo -u "$user" sed -i "s/__project_name__/${name}/g" ../db/create_db.sql
sudo -u "$user" sed -i "s/__password__/${password}/g" ../db/create_db.sql
sudo mysql < ../db/create_db.sql
echo "Creating database $name"

cat ../db/update_scripts/*.sql  > "../db/install_selfhelp.sql"
sudo mysql -D $name < ../db/install_selfhelp.sql
echo "Database $name initialized!"
echo "Installation is completed!"
