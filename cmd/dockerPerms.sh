sudo groupadd docker
sudo usermod -aG docker $USER
sudo chown "$USER":"$USER" /var/run/docker.sock
