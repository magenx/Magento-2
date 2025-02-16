#!/bin/bash
# before install
# set permissions
chown -hR ${BRAND}:php-${BRAND} "\${NEW_RELEASE}"
chmod 2750 ${ROOT_PATH} "\${NEW_RELEASE}"
setfacl -R -m m:r-X,u:${BRAND}:rwX,g:${PHP_USER}:r-X,o::-,d:u:${BRAND}:rwX,d:g:${PHP_USER}:r-X,d:o::- "\${NEW_RELEASE}"
setfacl -R -m u:${OWNER}:rwX,g:${PHP_USER}:rwX,o::-,d:u:${OWNER}:rwX,d:g:${PHP_USER}:rwX,d:o::- "\${NEW_RELEASE}"/{var,pub/media}
setfacl -R -m u:nginx:r-X,d:u:nginx:r-X "\${NEW_RELEASE}"
