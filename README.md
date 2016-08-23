OFX Parser
=================

[![Build Status](https://travis-ci.org/asgrim/ofxparser.svg?branch=master)](https://travis-ci.org/asgrim/ofxparser) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/asgrim/ofxparser/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/asgrim/ofxparser/?branch=master) [![Code Coverage](https://scrutinizer-ci.com/g/asgrim/ofxparser/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/asgrim/ofxparser/?branch=master) [![Latest Stable Version](https://poser.pugx.org/asgrim/ofxparser/v/stable)](https://packagist.org/packages/asgrim/ofxparser) [![License](https://poser.pugx.org/asgrim/ofxparser/license)](https://packagist.org/packages/asgrim/ofxparser)

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
$ofxParser = new \OfxParser\Parser();
$ofx = $ofxParser->loadFromFile('/path/to/your/bankstatement.ofx');

$bankAccount = reset($ofx->bankAccounts);

// Get the statement start and end dates
$startDate = $bankAccount->statement->startDate;
$endDate = $bankAccount->statement->endDate;

// Get the statement transactions for the account
$transactions = $bankAccount->statement->transactions;
```

Most common nodes are support. If you come across an inaccessible node in your OFX file, please submit a pull request!

## Fork & Credits

This is a fork of [grimfor/ofxparser](https://github.com/Grimfor/ofxparser) made to be framework independent. The source repo was designed for Symfony 2 framework, so credit should be given where credit due!
Heavily refactored by [Oliver Lowe](https://github.com/loweoj) and loosely based on the ruby [ofx-parser by Andrew A. Smith](https://github.com/aasmith/ofx-parser).
