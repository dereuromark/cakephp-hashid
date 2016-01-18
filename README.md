# Hashid plugin for CakePHP
[![Build Status](https://api.travis-ci.org/dereuromark/cakephp-hashid.svg)](https://travis-ci.org/dereuromark/cakephp-hashid)
[![Coverage Status](https://coveralls.io/repos/dereuromark/cakephp-hashid/badge.svg)](https://coveralls.io/r/dereuromark/cakephp-hashid)
[![Minimum PHP Version](http://img.shields.io/badge/php-%3E%3D%205.4-8892BF.svg)](https://php.net/)
[![License](https://poser.pugx.org/dereuromark/cakephp-hashid/license)](https://packagist.org/packages/dereuromark/cakephp-hashid)
[![Total Downloads](https://poser.pugx.org/dereuromark/cakephp-hashid/d/total.svg)](https://packagist.org/packages/dereuromark/cakephp-hashid)
[![Coding Standards](https://img.shields.io/badge/cs-PSR--2--R-yellow.svg)](https://github.com/php-fig-rectified/fig-rectified-standards)

A CakePHP 3.x Plugin to
- easily use [hashids](https://github.com/ivanakimov/hashids.php) for your database table lookups
- cloak the actual numeric id behind the record (assuming you use a non public salt) for URLs and alike

Why not UUIDS?
- UUIDs can be up to 200x slower with growing DB tables, complex or heavy joins and especially with CakePHP default char(36). But even with the recommended binary(16) it would not be ideal.
- UUIDS often times completely replace the primary key, making it impossible to sort anymore on those records. This is especially problematic with data that gets inserted
at the same time (same datetime for created).
- UUIDS are often used to just cloak the numeric primary keys visilibity of how much gets inserted over time. But that is not what they should be used for.
If you want to synch data across DBs, then they are useful. But they should not be abused for other things.

Why hashids:
- They are super short, especially for the URL
- They are lightweight and [fast](https://github.com/ivanakimov/hashids.php#speed). They work on the fly and require no table fields or other setup, no overhead involved.

Bottom line: Use hashids when you do not want to expose your database ids to the user.

## Setup
```
composer require dereuromark/cakephp-hashid
```
and
```
bin/cake plugin load Hashid
```

## Usage
```php
// Adding the behavior in your Table initialize()
// We also want the entity to be populated with the `hashid` value
$this->addBehavior('Hashid.Hashid', ['field' => 'hashid']);

// Saving a new record
$postData = [
	'username' => 'Hallo'
];
$user = $this->Users->newEntity($postData);
$this->Users->save($user);
```

The user entity now contains a `hashid` field (not saved by default, only in the entity):
```php
// In our ctp file we can now link to this using the HtmlHelper
$hashid = $user->hashid;
echo $this->Html->link(['action' => 'view', $hashid]);
```
URL `/users/view/1` becomes `/users/view/jR`.

In our UsersController, we now check with this hashid instead:
```php
/**
 * @param string|null $hashid
 */
public function view($hashid = null) {
	$user = $this->Users->find('hashed', [HashidBehavior::HID => $hashid])->firstOrFail();
	...
}

```

Et voila. Easy and without overhead.

## Manual usage
Of course you can also encode and decode manually:
```php
// 1 => 'jR'
$hashid = $this->Users->encodeId($id);

// 'jR' => 1
$id = $this->Users->decodeHashid($hashid);

// Or if you got an entity
$this->Users->encode($user);
$hashid = $user->hashid;
```

## Helper usage
If you stick to the non-field way and you want to rather encode on demand in your view, you can use the helper to encode your IDs:
```
// In our ctp file we can now link to the hashed version
$hashid = $this->Hashid->encodeId($user->id);
echo $this->Html->link(['action' => 'view', $hashid]);
```

## Additional configuration
You can provide global configs via Configure and your own `app.php`:
```php
'Hashid' => [
	'salt' => 'Your own salt' // This is important
],
```
It is recommended to keep `'salt'` to `true` - this way it uses your current Configure salt.
But you can also set it to any custom string.
If you do not provide a salt it is very easy to retrieve the original numeric id from your hashid.

Further config options are:
- debug: Defaults to current Configure value, in debug mode it will append the numeric id (`jR-1`) for easier debugging.
- field: Field name to populate with hashids upon save() and find(), defaults to `null` (= disabled).
- tableField: If you want to store the generated hashids in the table under this field, defaults to `false` (= disabled). Set to true for the same field as `field`, otherwise the field name.
- first: Set to true if you want each find('hashed') to return the `->first()` result, or to `firstOrFail` to fail if none can be found. Defaults to `null` (= disabled).

You can set up a dedicated table field (like a slug field) for your hashid, but this is usually not necessary.
This can be useful if you want to change the salt, and thus the old URLs need to be reachable.
By default this is deactivated.

## Security notice

> Do you have a question or comment that involves "security" and "hashids" in the same sentence? Don't use Hashids.

This sentence on the hashids documentation says it all: This is to cloak the IDs, but it is not a real secure encryption algorithm.

## License
MIT
