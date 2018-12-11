# Mashpia.local Certificates

### These files where generated when configuring self-signed certificates for `mashpia.local`. The main files are explained below.


## Files:

### ca.pem

This file is the CA that you need to add to your system to trust the certificate. On linux import this into chrome to trust the certificate.

### ca.srl

This is the serial number that was generated. Just in case...

### privkey.pem

This is the CA private key ( to use when generating more certs )

### server_v3.ext

This contains the v3 extension configurations for the certificate. Add domain names during the generation process by adding `DNS.X <dns_name>` to the end of the file

### server.crt

This is the servers certificate

### server.key

This is the servers private key

### server.crt.cnf

This is a configuration file used when generating the keys

## Commands Used:

1. Create a 2048 bit Certificate Authority (CA) private key: `sudo openssl genrsa -out privkey.pem 2048`
2. Create a self signed CA certificate: `sudo openssl req -new -x509 -days 3650 -nodes -key privkey.pem  -sha256 -out ca.pem`
3. Create a server Certificate Signing Request (CSR) and server private key: `sudo openssl req -new -nodes -out server.csr -keyout server.key -config server.csr.cnf`
4. Create the server certificate: `sudo openssl x509 -req -in server.csr -CA ca.pem -CAkey privkey.pem  -CAcreateserial -out server.crt -days 3650  -extfile server_v3.ext `