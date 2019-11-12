# Hashid plugin documentation

## Drop-in Replacement Usage
If we want to just replace the numeric ids with hashids, we can use the default config.

```php
// Adding the behavior in your Table::initialize()
$this->addBehavior('Hashid.Hashid', ['recursive' => true, ...]);

// Saving a new record
$postData = [
    'username' => 'Hallo',
];
$user = $this->Users->newEntity($postData);
$this->Users->save($user);
```

The user entity now returns a `hashid` in the primary key field (usually `id`).
The same would happen on each find().

In our ctp file we can now keep all links as they were before:
```php
// $id contains 'jR' instead of 1
echo $this->Html->link(['action' => 'view', $user->id]);
```
URL `/users/view/1` becomes `/users/view/jR`.
And in debug mode (on your local computer probably) `/users/view/1` becomes `/users/view/jR-1`.

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

## Helper Usage
If you stick to the non-field way and you want to rather encode on demand in your view, you can use the helper to encode your IDs:
```php
// You must load the helper before
$this->loadHelper('Hashid.Hashid', $optionalConfigArray);

// In our ctp file we can now link to the hashed version
$hashid = $this->Hashid->encodeId($user->id);
echo $this->Html->link(['action' => 'view', $hashid]);
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

### updateAll()/deleteAll()
When using atomatic operations, you will have to manually encode/decode the ids,
because here no behavior callbacks are currently possible to intercept.

My tip would be to dynamically wrap each of those method calls in the model layer,
so if you at anytime remove or modify the behavior, the code will still work:
```php
$id = $yourEntity->id;
if ($this->hasBehavior('Hashid')) {
    $id = $this->decodeHashid($id);
}
return $this->updateAll([...], ['id' => $id]);
```

## Trait Usage
The trait is the key component holding the actual de- and encoding functionality.
You can put it on top of any class that needs hashid support:
```php
use Hashid\Model\HashidTrait;

class FooBar {

    use HashidTrait;

}
```
Now you got the `encodeId()` and `decodeHashid()` methods from above at your disposal.

## Validation

Make sure you do not use the default baked "integer" rule for your primary keys.
They will be strings when you patch, so you need to use only scalar() validation rule here.

## Additional Configuration
You can provide global configs via Configure and your own `app.php`:
```php
'Hashid' => [
    'salt' => 'Your own salt string' // This is important
],
```
You can set `'salt'` to `true` - this way it uses your current Configure salt (not recommended).
If you do not provide a salt it is very easy to retrieve the original numeric id from your hashid.

Further config options are:
- debug: Defaults to current Configure value, in debug mode it will append the numeric id (`jR-1`) for easier debugging.
- field: Field name to populate with hashids upon save() and find(), defaults to `null` (= primary key).
- recursive: If you want also associated fetched entities' ids hashid'd, defaults to `false`.
- findFirst: Set to true if you want each find('hashed') to return the `->first()` result, or to `firstOrFail` to fail if none can be found. Defaults to `null` (= disabled).

## SEO Notice
If you use this for building your URLS and if those are indexed (no `noindex` meta tag), you should be careful about changing the salt in production.
Changing the salt changes the hashids generated and thus also the URL. In that case you get 404s for the *old* URLs, often times losing
traffic and SEO juice. You would want to store all old hashids together with their ids in a table for a 301 redirect lookup.

## Security Notice

> Do you have a question or comment that involves "security" and "hashids" in the same sentence? Don't use Hashids.

This sentence on the hashids documentation says it all: This is to cloak the IDs, but it is not a real secure encryption algorithm.
