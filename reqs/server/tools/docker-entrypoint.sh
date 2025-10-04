####################################################################################
#                         Author: Abderrahmane Abdelouafi                          #
#                          File Name: docker-entrypoint.sh                         #
#                      Creation Date: October 04, 2025 14:01 PM                    #
#                      Last Updated: October 04, 2025 14:47 PM                     #
#                           Source Language: shellscript                           #
#                                                                                  #
#                             --- Code Description ---                             #
#     Docker entrypoint script for Apache container initialization. Generates      #
#    self-signed SSL certificates (4096-bit RSA, SHA-256, valid 365 days) if not   #
#   present in mounted volumes, ensuring certificate persistence across restarts.  #
#       Starts Apache in foreground mode for container lifecycle management.       #
####################################################################################

#!/bin/bash
set -e

SSL_CERTS_DIR="/etc/apache2/ssl/certs"
CERTS_PRV_KEYS_DIR="/etc/apache2/ssl/private"

# Create directories if they don't exist
mkdir -p "$SSL_CERTS_DIR"
mkdir -p "$CERTS_PRV_KEYS_DIR"

# Generate SSL certificates if they don't exist
if [ ! -f "$SSL_CERTS_DIR/server.crt" ] || [ ! -f "$CERTS_PRV_KEYS_DIR/server.key" ]; then
    echo "SSL certificates not found. Generating new self-signed certificates..."
    
    # Generate private key for the certificate (4096-bit)
    openssl genrsa -out "$CERTS_PRV_KEYS_DIR/server.key" 4096
    
    # Generate self-signed SSL certificate (valid for 365 days)
    openssl req -new -x509 -sha256 -days 365 \
        -subj "/C=MA/L=Ben Guerir/O=LEETMAKERS/OU=TECH/CN=leetmakers.com" \
        -key "$CERTS_PRV_KEYS_DIR/server.key" \
        -out "$SSL_CERTS_DIR/server.crt"
    
    echo "SSL certificates generated successfully."
else
    echo "SSL certificates already exist. Skipping generation."
fi

# Start Apache in the foreground
exec apache2-foreground
