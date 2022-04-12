# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [4.0.3] - 2020-02-20
### Fixed
- Feedback to end-user when using vaulted payment methods

## [4.0.2] - 2019-12-09
### Fixed
- Replaced `getPrice` with `getBasePrice` in Level 2/3 data builder in order to correctly get a float
- Issue when trying to use a UK Maestro card to checkout

## [4.0.1] - 2019-11-27
### Fixed
- LPM allowed payment methods config value saved incorrectly as `null`

## [4.0.0] - 2019-11-20
### Added
- Local Payment Methods
  - Merchants can now offer up to 8 new payment methods for EU based customers
  - Bancontact, EPS, girpoay, iDEAL, Klarna Pay Now/SOFORT, MyBank, P24 and SEPA/ELV Direct Debit are now supported
- Minimum requirements have been updated to Magento 2.3, and PHP 7.2

## [3.4.1] - 2019-11-20
### Fixed
- Hotfix for bug that stopped stored cards being used when CVV Re-verification is disabled

## [3.4.0] - 2019-11-15
### Added
- M1 to M2 Stored Card migration tool
  - New `bin/magento braintree:migrate` console command to connect to your remote M1 database and potentially copy across customers
    stored cards. This should be run whilst Braintree is in Production mode.
- Kount ENS webhook
  - Allow "suspected fraud" orders in Magento to be accepted or decline by changing status in your Kount portal
- CVV Re-verification for Stored Cards
  - This option can be enabled so that registered Customers need to provide the CVV in order to use a Stored Card
- Information about Apple Pay on-boarding
- Information about Custom Fields

### Fixed
- Level 2/3 Processing data now only used for Credit/Debit card transactions and now includes shipping tax
- Correct state now set on the PayPal Onclick review page
- Bug where PayPal was not using updated shipping address if the customer changed it during checkout
- Bug that stopped Admins creating orders in the backend when Braintree was the only payment method
- API validation check now uses correct Store IDs when a multi-store is being used

### Removed
- Removed old PayPal `payee email` configuration option as it has been deprecated by Braintree

## [3.3.3]
### Fixed
- Updated PayPal Credit APR percentages

## [3.3.2] - 2019-09-26
### Fixed
- Level 2 / 3 Processing data should now only send shipping data if a shipping address is present.

## [3.3.1] - 2019-09-25
### Fixed
- Level 2 / 3 Processing data should now return strings for the float values as per the documentation

## [3.3.0] - 2019-09-18
### Added
- New payment methods; Venmo and ACH Direct Debit
- Both new payment methods are for merchants based in the US and will require you to speak with your Braintree Account 
Manager to enable the services on your account
- Level 2 /3 Processing information. For more details, see [here](https://developers.braintreepayments.com/reference/general/level-2-and-3-processing/overview)
- Braintree PHP SDK (3.40.0) is now included as part of the module in order to maintain BC with Magento 2.2

### Fixed
- PayPal not working with virtual products
- CVV validation bug (https://github.com/nicholasscottfish)
- Google Pay library now correctly excluded from JS minification

### Removed
- Unused Guzzle PSR7 library

## [3.2.1] - 2019-07-31
### Fixed
- Bug in backend create order

## [3.2.0] - 2019-07-31
### Added
- 3DS 2 now supported
- Basic validation to Dynamic Descriptor configuration options to alleviate errors in checkout

### Fixed
- PayPal breaking Grouped Product pages
- Return type stopping Swagger from compiling (https://github.com/Thundar)
- Handling of exceptions in GatewayCommand class that would show "blank" errors on checkout
- Broken CSS selector
- Giftcards not working with PayPal
- Reverted a change introduced in 3.1.2 where card details were only stored in the database if 
the Vault config option was enabled. This is because partial invoicing, refunds etc need the stored card data. However,
a bug in core Magento 2.3.1 means that if the Vault is turned off, cards are always shown in customer accounts

### Removed
- Layout options for PayPal buttons, due to the buttons now being rendered separately

## [3.1.3]
### Fixed
- Issue with Configurable Product prices
- Return type issue for Google Pay configuration

## [3.1.2]
### Added
- Callback to delete stored card in Braintree when Customer deletes card in account

### Fixed
- Vaulted cards now work with 3DS
- Order button "unstuck" after invalid card details/failed payment
- Stop cards always being stored after successful order
- No cart session exception handled correctly (https://github.com/shilpambb)
- PayPal
  - Credit instalments now sorted on Product page
  - Billing address now updated correctly
  - Quote updater no longer throws an error if store uses DB table prefix
  - Shipping address now used for Virtual Products
  - Voucher redirect loop fixed
  - 2nd address line now included (https://github.com/igor-imaginemage)
  - Credit calculator now uses correct total values (https://github.com/diazwatson)
  - Region now added to shipping address correctly on PayPal OneClick/Review screen
- Apple Pay
  - Shipping cost is no longer added multiple times
  - Apple Pay dialog now shows correct total on initial popup

## [3.1.1] - 2019-03-05
### Fixed
- Fix bug that stopped PayPal working on mini-cart

## [3.1.0] - 2019-02-27
### Added
- Functionality to add PayPal button to Product page

## [3.0.7] - 2019-01-30
### Fixed
- Vaulted cards now work correctly

[4.0.0]: https://github.com/genecommerce/module-braintree-magento2/compare/3.4.1...4.0.0
[3.4.1]: https://github.com/genecommerce/module-braintree-magento2/compare/3.4.0...3.4.1
[3.4.0]: https://github.com/genecommerce/module-braintree-magento2/compare/3.3.3...3.4.0
[3.3.3]: https://github.com/genecommerce/module-braintree-magento2/compare/3.3.2...3.3.3
[3.3.2]: https://github.com/genecommerce/module-braintree-magento2/compare/3.3.1...3.3.2
[3.3.1]: https://github.com/genecommerce/module-braintree-magento2/compare/3.3.0...3.3.1
[3.3.0]: https://github.com/genecommerce/module-braintree-magento2/compare/3.2.1...3.3.0
[3.2.1]: https://github.com/genecommerce/module-braintree-magento2/compare/3.2.0...3.2.1
[3.2.0]: https://github.com/genecommerce/module-braintree-magento2/compare/3.1.3...3.2.0
[3.1.3]: https://github.com/genecommerce/module-braintree-magento2/compare/3.1.2...3.1.3
[3.1.2]: https://github.com/genecommerce/module-braintree-magento2/compare/3.1.1...3.1.2
[3.1.1]: https://github.com/genecommerce/module-braintree-magento2/compare/3.1.0...3.1.1
[3.1.0]: https://github.com/genecommerce/module-braintree-magento2/compare/3.0.7...3.1.0
[3.0.7]: https://github.com/genecommerce/module-braintree-magento2/compare/3.0.6...3.0.7


