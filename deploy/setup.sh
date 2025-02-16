#!/bin/bash
# before install
# get latest release folder
NEW_RELEASE="/opt/codedeploy-agent/deployment-root/${DEPLOYMENT_GROUP_ID}/${DEPLOYMENT_ID}/deployment-archive"
# set permissions
chown -R ${BRAND}:php-${BRAND} ${NEW_RELEASE}
chmod 2750 ${NEW_RELEASE}
setfacl -R -m m:r-X,u:${BRAND}:rwX,g:${PHP_USER}:r-X,o::-,d:u:${BRAND}:rwX,d:g:${PHP_USER}:r-X,d:o::- ${NEW_RELEASE}
setfacl -R -m u:${BRAND}:rwX,g:${PHP_USER}:rwX,o::-,d:u:${BRAND}:rwX,d:g:${PHP_USER}:rwX,d:o::- ${NEW_RELEASE}/{var,pub/media}
setfacl -R -m u:nginx:r-X,d:u:nginx:r-X "${NEW_RELEASE}"
