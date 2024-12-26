#!/bin/bash
#=================================================================================#
#        MagenX e-commerce stack for Magento 2                                    #
#        Copyright (C) 2013-present admin@magenx.com                              #
#        All rights reserved.                                                     #
#=================================================================================#
# Fetch metadata
cat <<END > /usr/local/bin/metadata
#!/bin/bash
AWSTOKEN=\$(curl -X PUT "http://169.254.169.254/latest/api/token" -H "X-aws-ec2-metadata-token-ttl-seconds: 600")
REGION=\$(curl -s -H "X-aws-ec2-metadata-token: \${AWSTOKEN}" http://169.254.169.254/latest/meta-data/placement/region)
INSTANCE_ID=\$(curl -s -H "X-aws-ec2-metadata-token: \${AWSTOKEN}" http://169.254.169.254/latest/meta-data/instance-id)
INSTANCE_HOSTNAME=\$(curl -s -H "X-aws-ec2-metadata-token: \${AWSTOKEN}" http://169.254.169.254/latest/meta-data/tags/instance/Hostname)
INSTANCE_NAME=\$(curl -s -H "X-aws-ec2-metadata-token: \${AWSTOKEN}" http://169.254.169.254/latest/meta-data/tags/instance/Instance_Name)
INSTANCE_TYPE=\$(curl -s -H "X-aws-ec2-metadata-token: \${AWSTOKEN}" http://169.254.169.254/latest/meta-data/instance-type)
INSTANCE_IP=\$(curl -s -H "X-aws-ec2-metadata-token: \${AWSTOKEN}" http://169.254.169.254/latest/meta-data/local-ipv4)

# Export variables
export REGION="\${REGION}"
export INSTANCE_ID="\${INSTANCE_ID}"
export INSTANCE_HOSTNAME="\${INSTANCE_HOSTNAME}"
export INSTANCE_NAME="\${INSTANCE_NAME}"
export INSTANCE_TYPE="\${INSTANCE_TYPE}"
export INSTANCE_IP="\${INSTANCE_IP}"
END
chmod +x /usr/local/bin/metadata
