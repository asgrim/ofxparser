OFX Parser
=================

OFX Parser is a PHP library designed to parse an OFX file downloaded from a financial institution into simple PHP objects. 

It supports multiple Bank Accounts, the required "Sign On" response, and recognises OFX timestamps.

## Installation

Simply require the package using [Composer](https://getcomposer.org/):

```bash
$ composer require asgrim/ofxparser
```

## Usage

You can access the nodes in your OFX file as follows:

```php
$ofxParser = new \OfxParser\Parser;
$ofx = $ofxParser->loadFromFile('/path/to/your/bankstatement.ofx');

// Get the statement start and end dates
$startDate = $ofx->BankAccount->Statement->startDate;
$endDate = $ofx->BankAccount->Statement->endDate;

// Get the statements for the current bank account
$transactions = $ofx->BankAccount->Statement->transactions;
```

Most common nodes are support. If you come across an inaccessible node in your OFX file, please submit a pull request!

## Fork & Credits

This is a fork of [grimfor/ofxparser](https://github.com/Grimfor/ofxparser) made to be framework independent. The source repo was designed for Symfony 2 framework, so credit should be given where credit due!
Heavily refactored by [Oliver Lowe](https://github.com/loweoj) and loosely based on the ruby [ofx-parser by Andrew A. Smith](https://github.com/aasmith/ofx-parser).
