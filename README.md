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

## Drop-in Replacement Usage
If we want to just replace the numeric ids with hashids, we can use the default config.

```php
// Adding the behavior in your Table initialize()
$this->addBehavior('Hashid.Hashid', ['recursive' => true, ...]);

// Saving a new record
$postData = [
	'username' => 'Hallo'
];
$user = $this->Users->newEntity($postData);
$this->Users->save($user);
```

The user entity now contains a `hashid` in the primary key field (usually `id`).
The same would happen on each find().

In our ctp file we can now keep all links as they were before:
```php
// $id contains 'jR' instead of 1
echo $this->Html->link(['action' => 'view', $user->id]);
```
URL `/users/view/1` becomes `/users/view/jR`.

In our UsersController, we now check with this hashid instead behind the scenes:
```php
/**
 * @param string|null $id
 */
public function view($id = null) {
	$user = $this->Users->get($id);
	...
}
```

Et voila. Activated easily and without changing any existing code.

You can also use any find() method, just as you normally would:
```php
$user = $this->Users->find()->where(['id' => $id])->firstOrFail();
```

If you re-save the entity, it will just use the primary key again internally, so it's safe to modify and perist entity data.

## Semi-automatic Usage
We can also use a separate field for the hashid:
```php
$this->addBehavior('Hashid.Hashid', ['field' => 'hashid']);

// Lookups with hashids
$user = $this->Users->find('hashed', [HashidBehavior::HID => $hashid])->first();

// But also all normal find()/get() would contain the hashid in the entity
$user = $this-User->get($id);

// Output in your ctp
echo $this->Html->link(['action' => 'view', $user->hashid]);
```

## Manual usage
Of course you can also encode and decode manually:
```php
$this->addBehavior('Hashid.Hashid', ['field' => 'hid']);

// 1 => 'jR'
$hid = $this->Users->encodeId($id);

// 'jR' => 1
$id = $this->Users->decodeHashid($hid);

// Or if you got an entity
$this->Users->encode($user);
$hid = $user->hid;
```

## Additional configuration
You can provide global configs via Configure and your own `app.php`:
```php
'Hashid' => [
	'salt' => 'Your own salt' // This is important
],
```
If you do not provide a salt it is very easy to retreive the original numeric id from your hashid.

Further config options are:
- field: Field name to populate with hashids upon save() and find(), defaults to `null` (= primary key)
- recursive: If you want also associated fetched entities' ids hashid'd, defaults to `false`.
- findFirst: Set to true if you want each find('hashed') to return the `->first()` result, or to `firstOrFail` to fail if none can be found. Defaults to `null` (= disabled).

## Security notice

> Do you have a question or comment that involves "security" and "hashids" in the same sentence? Don't use Hashids.

This sentence on the hashids documentation says it all: This is to cloak the IDs, but it is not a real secure encryption algorithm.

## License
MIT
