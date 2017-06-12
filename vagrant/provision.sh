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

# Chrome Headless Setup
wget https://dl.google.com/linux/direct/google-chrome-stable_current_amd64.deb
sudo dpkg -i google-chrome-stable_current_amd64.deb || \
sudo apt-get install -y --fix-broken

# Configuration spécifique de supervisor
sudo cp /vagrant/vagrant/supervisor.conf /etc/supervisor/conf.d/wizaplace.conf
sudo mkdir -p /var/log/chrome-headless/
sudo service supervisor restart

echo "export TEST_WEBSERVER_URL=http://127.0.0.1/app_test.php/" >> /home/vagrant/.zprofile

cd /vagrant && make install
