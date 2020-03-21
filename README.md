# Hashid Plugin for CakePHP
[![Build Status](https://api.travis-ci.org/dereuromark/cakephp-hashid.svg?branch=master)](https://travis-ci.org/dereuromark/cakephp-hashid)
[![Coverage Status](https://codecov.io/gh/dereuromark/cakephp-hashid/branch/master/graph/badge.svg)](https://codecov.io/gh/dereuromark/cakephp-hashid)
[![Latest Stable Version](https://poser.pugx.org/dereuromark/cakephp-hashid/v/stable.svg)](https://packagist.org/packages/dereuromark/cakephp-hashid)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%205.6-8892BF.svg)](https://php.net/)
[![License](https://poser.pugx.org/dereuromark/cakephp-hashid/license)](https://packagist.org/packages/dereuromark/cakephp-hashid)
[![Total Downloads](https://poser.pugx.org/dereuromark/cakephp-hashid/d/total.svg)](https://packagist.org/packages/dereuromark/cakephp-hashid)
[![Coding Standards](https://img.shields.io/badge/cs-PSR--2--R-yellow.svg)](https://github.com/php-fig-rectified/fig-rectified-standards)

Exposes [hashids](https://github.com/ivanakimov/hashids.php) as drop-in replacement for your numeric primary keys.


**DEPRECATED** This plugin is deprecated in favor of the more robust and future proof solution through exposing a dedicated field. See **[Expose plugin](https://github.com/dereuromark/cakephp-expose)**.


## A CakePHP plugin to
- cloak the actual numeric primary key behind the record (assuming you use a non public salt) for URLs, APIs and alike
- build short unique IDs (Even PHP_INT_MAX `2.147.483.647` becomes `lXQAALg` for example, so `length <= 7` for the hashid)

This branch is for use with **CakePHP 3.6+**. See [version map](https://github.com/dereuromark/cakephp-hashid/wiki#cakephp-version-map) for details.

### Why hashids?
- They are super short, especially for the URL
- They are lightweight and [fast](https://github.com/ivanakimov/hashids.php#speed). They work on the fly and require no table fields, no code changes. No overhead involved except for enabling the behavior.
- You do not lose sorting capability as with UUIDs.
- You can use hashids if you do not want to expose your database ids to the user - while not compromising speed - as a balance trait-off.

### Why not UUIDS?
- UUIDs can be up to 200x slower with growing DB tables, complex or heavy joins and especially with CakePHP default char(36). But even with the recommended binary(16) it would not be ideal.
- UUIDS often times completely replace the primary key, making it impossible to sort anymore on those records. This is especially problematic with data that gets inserted
at the same time (same datetime for created).
- UUIDS are often used to just cloak the numeric primary keys visibility of how much gets inserted over time. But that is not what they should be used for.
If you want to synch data across DBs, then they are useful. But they should not be abused for other things.

**UPDATE** This is actually not true if you combine both AIID and UUIDs and use UUIDs only for external lookup but keep AIID for all internal joins and operations. As such the Expose plugin fully replaces this now.


## Demo
See https://sandbox3.dereuromark.de/sandbox/hashids

## Setup
```
composer require dereuromark/cakephp-hashid
```
and
```
bin/cake plugin load Hashid
```

## Usage
See [Documentation](docs).

## Alternatives

See [this nice article](https://tomharrisonjr.com/uuid-or-guid-as-primary-keys-be-careful-7b2aa3dcb439) on a different approach that would use both -allowing "int" for internal and "uuid" for external reference.
