#!/usr/bin/env bash

echo ">>> Updating projects"

cd /vagrant/webserver
composer update

cd /vagrant/consumer
composer update