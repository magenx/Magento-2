#!/bin/bash
# application start
# check if magento setup:upgrade required
upgrade () {
bin/magento setup:db:status --no-ansi -n
  if [[ $? -eq 2 ]]; then
    bin/magento setup:upgrade --keep-generated --no-ansi -n
  fi
sudo cacheflush
}
# then run it only on one instance
bash /user/local/bin/leader && upgrade
