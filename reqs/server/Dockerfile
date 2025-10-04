####################################################################################
#                         Author: Abderrahmane Abdelouafi                          #
#                              File Name: Dockerfile                               #
#                      Creation Date: October 04, 2025 14:14 PM                    #
#                      Last Updated: October 04, 2025 14:49 PM                     #
#                           Source Language: dockerfile                            #
#                                                                                  #
#                             --- Code Description ---                             #
#  Dockerfile for Apache image build with PHP 8.1, including required extensions,  #
#                              SSL, mod_rewrite, Composer, and libraries.          #
####################################################################################

# Use the official PHP Apache image
FROM php:8.1-apache

# Install required extensions and utilities
RUN apt-get update && apt-get install -y \
    git \
    vim \
    unzip \
    openssl \
    libgd-dev \
    curl && docker-php-ext-install pdo pdo_mysql mysqli gd

# Enable Apache mod_rewrite and SSL modules
RUN a2enmod rewrite ssl

# Set the working directory
WORKDIR /var/www/html

# Copy application files to the container's document root
COPY . . 

# Copy the entrypoint script
COPY tools/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Copy the Apache SSL configuration
COPY config/default-ssl.conf /etc/apache2/sites-available/

# Remove the default virtual host configuration
RUN a2dissite 000-default && rm /etc/apache2/sites-available/000-default.conf

# Enable SSL site (certificates will be generated at runtime via entrypoint)
RUN a2ensite default-ssl

# Create the directory for Composer libraries
RUN mkdir -p /var/www/composer/

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install the Google API Client, PHPMailer libraries via Composer
RUN composer require google/apiclient:"^2.1" --working-dir=/var/www/composer/ --no-interaction && \
    composer require phpmailer/phpmailer --working-dir=/var/www/composer/ --no-interaction

# Expose the ports for HTTP and HTTPS
EXPOSE 80 443

# Set the entrypoint to handle SSL certificate generation at startup
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
