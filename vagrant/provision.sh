#!/usr/bin/env bash
set -e

# Suppression de l'extension GeoIP qui conflicte avec la dépendance PHP
[ -f /etc/php/7.0/mods-available/geoip.ini ] && sudo rm /etc/php/7.0/mods-available/geoip.ini || true

# Configuration spécifique d'Apache
sudo a2enmod ssl
sudo a2dissite 000-default.conf
sudo cp /vagrant/vagrant/apache.conf /etc/apache2/sites-available/apache.conf
sudo a2ensite apache.conf
sudo service apache2 restart

# Stop Git from trying to guess author's name and email from system values
git config --global --add user.useConfigOnly true

cd /vagrant && make install
