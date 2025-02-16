 #!/bin/bash
# after install
# get latest release folder
NEW_RELEASE="/opt/codedeploy-agent/deployment-root/\${DEPLOYMENT_GROUP_ID}/\${DEPLOYMENT_ID}/deployment-archive"
# craete symlink shared folders
SHARED="/home/${BRAND}/shared"
ln -nfs "\${SHARED}/var" "\${NEW_RELEASE}/var"
ln -nfs "\${SHARED}/pub/media" "\${NEW_RELEASE}/pub/media"
# Check if the media directory is an EFS mount
if ! df -T "\${NEW_RELEASE}/pub/media" | grep -q "efs"; then
echo "The media directory is not an EFS mount."
echo "Deployment error!"
exit 1
fi
# Perform symlink swap to point to the new release
PUBLIC_HTML="/home/${BRAND}/public_html"
ln -nfs "\${NEW_RELEASE}" "\${PUBLIC_HTML}"
