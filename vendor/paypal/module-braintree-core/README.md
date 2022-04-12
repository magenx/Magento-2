# Braintree Payments

Module PayPal\Braintree implements integration with the Braintree payment system.

## Overview

This module overwrites the original Magento Braintree module, to provide additional features and bug fixes.

## Available Payment Methods
* Credit Card
    * Visa
    * Mastercard
    * Amex
    * Discover
    * JCB
    * Diners
    * Maestro
    * Restrictions apply.
* PayPal
* PayPal Credit
    * US and UK only. Restrictions apply.
* Google Pay
* Apple Pay
* Venmo (US only)
* ACH Direct Debit (US only)

## Additional Features

### M1 to M2 Stored Card migration tool
If you are looking to migrate to M2 and want to offer the best experience for existing customers by migrating their stored
credit cards, this is now possible with the new console command.

To use the new command, ensure that
- Your M1 database is online and accessible
- Your M2 store is in Braintree Production mode
- You have already migrated the customers from M1 to M2

Run the following command on your M2 server

`bin/magento braintree:migrate --host=<HOSTNAME_OR_IP> --dbname=<DB_NAME>`

You will be prompted for the DB Username and Password and after that, the tool will query your M1 DB, find any stored cards
and locate them in your Braintree account (this is why you must run it with Braintree in Production mode).
Any matching records that are found are then queried in your M2 database, and the card details* are stored for that customer.

<small>
* Credit Card information is stored by way of a token that matches a Vault record in Braintree.
No sensitive card data is ever exposed.
</small>

### Kount ENS Webhook
If your Kount and Braintree accounts have been linked, you can now configure Braintree with your Kount Merchant ID to
enable the ENS webhook. Add the ENS URL to your Kount portal (more info in the configuration options) and any orders
that get flagged as "Review" or "Escalate" can be accepted or declined through Kount. The ENS webhook in Magento will
pick up this status change and handle the Magento Order accordingly.
More information available [here](https://articles.braintreepayments.com/guides/fraud-tools/advanced/kount-custom).

### Custom Fields
If you would like to add [Custom Fields](https://articles.braintreepayments.com/control-panel/custom-fields) to your
Braintree transactions, we provide an example module [here](https://github.com/genecommerce/module-braintree-customfields-example)
that can be used to create a custom module for your store to add these fields.