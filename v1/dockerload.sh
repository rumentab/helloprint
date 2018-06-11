#!/usr/bin/env bash

echo ">>> Building docker containers"

echo ">>>>>> Building webserver container"

cd /vagrant/webserver/
docker-compose up -d

echo ">>>>>> Building producer container"

cd /vagrant/producer/
docker-compose up -d

echo ">>>>>> Building consumer container"

cd /vagrant/consumer/
docker-compose up -d

echo ">>> Containers installed!"
