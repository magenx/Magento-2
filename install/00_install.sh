#!/bin/bash
#=================================================================================#
#        MagenX e-commerce stack for Magento 2                                    #
#        Copyright (C) 2013-present admin@magenx.com                              #
#        All rights reserved.                                                     #
#=================================================================================#


# SETTINGS
NGINX_VERSION="1.27.3*"
PHP_VERSION="8.3"
PHP_PACKAGES=(cli fpm common mysql zip gd mbstring curl xml bcmath intl soap oauth apcu)
# Linux packages
LINUX_PACKAGES="nfs-common unzip git python3-pip acl attr imagemagick binutils pkg-config libssl-dev"

apt -qqy update
apt -qq -y install ${LINUX_PACKAGES}

# EFS UTILS
aws ssm send-command \
    --document-name "AWS-ConfigureAWSPackage" \
    --instance-ids "instance-IDs" \
    --parameters '{"action":["Install"],"name":["AmazonEFSUtils"],"installationType":["In-place update"]}' \
    --comment "Install AmazonEFSUtils"

# NGINX INSTALLATION
echo "deb [signed-by=/usr/share/keyrings/nginx-archive-keyring.gpg] http://nginx.org/packages/mainline/debian `lsb_release -cs` nginx" > /etc/apt/sources.list.d/nginx.list
curl https://nginx.org/keys/nginx_signing.key | gpg --dearmor | tee /usr/share/keyrings/nginx-archive-keyring.gpg >/dev/null
echo -e "Package: *\nPin: origin nginx.org\nPin: release o=nginx\nPin-Priority: 900\n" > /etc/apt/preferences.d/99nginx
apt-get -qq update -o Dir::Etc::sourcelist="sources.list.d/nginx.list" -o Dir::Etc::sourceparts="-" -o APT::Get::List-Cleanup="0"
apt-get -qqy -o Dpkg::Options::="--force-confold" install nginx=${NGINX_VERSION}
systemctl enable nginx

# PHP INSTALLATION
curl -o /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg
echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" > /etc/apt/sources.list.d/php.list
apt-get -qq update -o Dir::Etc::sourcelist="sources.list.d/php.list" -o Dir::Etc::sourceparts="-" -o APT::Get::List-Cleanup="0"
apt-get -qqy -o Dpkg::Options::="--force-confold" install php${PHP_VERSION} ${PHP_PACKAGES[@]/#/php${PHP_VERSION}-} php-pear
 
