#!/bin/bash
set -e

mkdir -p ~/ssl/
read -s -p "Enter passphrase: " pass

openssl genrsa -des3 -passout pass:${pass} -out ~/ssl/AlchemyRootCA.key 2048
openssl req -x509 -new -nodes -key ~/ssl/AlchemyRootCA.key -sha256 -days 1825 \
    -subj "/C=FR/ST=France/O=Alchemy, Inc./CN=Alchemy" \
    -passin pass:${pass} \
    -out ~/ssl/AlchemyRootCA.pem

echo "Done."

