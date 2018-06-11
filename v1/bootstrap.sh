#!/usr/bin/env bash

echo ">>> Installing base utilities"

# Install base items
sudo apt-get install -y curl apt-transport-https ca-certificates software-properties-common

echo ">>> Installing Docker"

# Install Docker
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo apt-key add -

# Add Docker Repo
sudo add-apt-repository "deb [arch=amd64] https://download.docker.com/linux/ubuntu $(lsb_release -cs)  stable"
sudo apt-get update

# Install Docker CE
sudo apt-get install -y docker-ce
sudo systemctl enable docker

# Give vagrant user rights to run docker
sudo groupadd docker
sudo usermod -aG docker $USER

# Install docker-compose
sudo curl -L https://github.com/docker/compose/releases/download/1.21.2/docker-compose-$(uname -s)-$(uname -m) -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose
docker-compose --version

echo ">>> Docker installed"

echo ">>> Installing Composer"

sudo apt-get install -y composer