#!/bin/bash

# Suppression de l'extension GeoIP qui conflicte avec la dépendance PHP
[ -f /etc/php/7.0/mods-available/geoip.ini ] && sudo rm /etc/php/7.0/mods-available/geoip.ini || true

# Configuration spécifique d'Apache
sudo a2enmod ssl
sudo a2dissite 000-default.conf
sudo cp /vagrant/vagrant/apache.conf /etc/apache2/sites-available/apache.conf
sudo a2ensite apache.conf
sudo service apache2 restart

# Chrome Headless Setup
wget https://dl.google.com/linux/direct/google-chrome-stable_current_amd64.deb
sudo dpkg -i google-chrome-stable_current_amd64.deb
sudo apt-get install -y --fix-broken

cd /vagrant && make install
