sudo groupadd docker
sudo usermod -aG docker $USER
sudo chown "$USER":docker /var/run/docker.sock
