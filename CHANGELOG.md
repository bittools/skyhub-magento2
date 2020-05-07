# Changelog
All notable changes to this project will be documented in this file.

## [Unreleased]

## 1.0.8 - 2020-05-07
### Changed
- Bugfix: skip some observers when order is updated by queue
- Ignore canceled order in queue if it not exists on store

## 1.0.7 - 2020-02-04
### Changed
- Bugfix: integrate orders with configurable products
- Save customer taxvat on sales_order table
- Add Clear Queue action to all queue pages
- Improvement to update customer and address data on order integration process

## 1.0.6 - 2019-12-10
### Changed
- Send order requests without status field
- Add endpoint on REST API to allow set order invoice_key

## 1.0.5 - 2019-09-11
### Changed
- Set customer taxvat on vat_id address field
- Fix order importing when country code, on SkyHub addresses JSON's node, was 3 chars (ISO3)

## 1.0.4 - 2019-02-05
### Changed
- Fixing the product's integration. Images wasn't been sent to skyhub.

## 1.0.0 - 2018-08-09
### Added
- Products integration
- Product attributes integration
- Customer attributes integration
- Product categories integration
- Orders integration
- Multi store accounts
- Base module
