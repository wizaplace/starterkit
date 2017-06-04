#!/bin/bash

# Suppression de l'extension GeoIP qui conflicte avec la dépendance PHP
[ -f /etc/php/7.0/mods-available/geoip.ini ] && sudo rm /etc/php/7.0/mods-available/geoip.ini || true

# Configuration spécifique d'Apache
a2enmod ssl
a2dissite 000-default.conf
cp /vagrant/vagrant/apache.conf /etc/apache2/sites-available/apache.conf
a2ensite apache.conf
service apache2 restart

cd /vagrant && make install
