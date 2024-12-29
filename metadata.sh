#!/bin/bash
#=================================================================================#
#        MagenX e-commerce stack for Magento 2                                    #
#        Copyright (C) 2013-present admin@magenx.com                              #
#        All rights reserved.                                                     #
#=================================================================================#
# Fetch metadata
cat <<'END' > /usr/local/bin/metadata
#!/bin/bash
METADATA_URL="http://169.254.169.254/latest"
# Function to get metadata
metadata() {
    local FIELD=$1
    # Fetch the token
    TOKEN=$(curl -sSf -X PUT "${METADATA_URL}/api/token" \
        -H "X-aws-ec2-metadata-token-ttl-seconds: 21600") || {
        echo "Error: Unable to fetch token. Ensure IMDSv2 is enabled." >&2
        exit 1
    }
    # Fetch the metadata value
    curl -sSf -X GET "${METADATA_URL}/meta-data/${FIELD}" \
        -H "X-aws-ec2-metadata-token: ${TOKEN}" || {
        echo "Error: Unable to fetch metadata for field '${FIELD}'." >&2
        exit 1
    }
}
if [ "$#" -eq 0 ]; then
    echo "Usage: $0 <metadata-field>"
    echo "Example: $0 instance-id"
    exit 1
fi
FIELD=$1
metadata "$FIELD"
END

chmod +x /usr/local/bin/metadata
