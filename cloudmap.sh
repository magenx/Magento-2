#!/bin/bash
#=================================================================================#
#        MagenX e-commerce stack for Magento 2                                    #
#        Copyright (C) 2013-present admin@magenx.com                              #
#        All rights reserved.                                                     #
#=================================================================================#
# Get all parameters
. /usr/local/bin/parameterstore
. /usr/local/bin/metadata

# Cloudmap registration
cat <<END > /usr/local/bin/cloudmap-register
#!/bin/bash
. /usr/local/bin/metadata
if ! grep -q "\${INSTANCE_IP}  \${INSTANCE_HOSTNAME}" /etc/hosts; then
  echo "\${INSTANCE_IP}  \${INSTANCE_HOSTNAME}" >> /etc/hosts
fi
hostnamectl set-hostname \${INSTANCE_HOSTNAME}

aws servicediscovery register-instance \
  --region \${REGION"} \
  --service-id \${CLOUDMAP_SERVICE_ID} \
  --instance-id \${INSTANCE_ID} \
  --attributes AWS_INSTANCE_IPV4=\${INSTANCE_IP}
END

cat <<END > /usr/local/bin/cloudmap-deregister
#!/bin/bash
. /usr/local/bin/metadata
aws servicediscovery deregister-instance \
  --region \${REGION} \
  --service-id \${CLOUDMAP_SERVICE_ID} \
  --instance-id \${INSTANCE_ID}
END

cat <<END > /etc/systemd/system/cloudmap-register.service
[Unit]
Description=Register AWS CloudMap service on boot
Requires=network-online.target
After=network-online.target

[Service]
Type=oneshot
KillMode=process
RemainAfterExit=yes

ExecStart=/usr/local/bin/cloudmap-register

[Install]
WantedBy=multi-user.target
END

cat <<END > /etc/systemd/system/cloudmap-deregister.service
[Unit]
Description=Deregister AWS CloudMap service on shutdown
Requires=network-online.target

DefaultDependencies=no
Before=shutdown.target reboot.target halt.target hibernate.target

[Service]
Type=oneshot
ExecStart=/usr/local/bin/cloudmap-deregister
RemainAfterExit=no

[Install]
WantedBy=halt.target reboot.target shutdown.target hibernate.target
END

systemctl enable cloudmap-deregister.service cloudmap-register.service
systemctl start cloudmap-register.service
