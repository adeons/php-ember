Php Ember
=========

A small server side library for PHP 5.3 which processes [JSON 
API](http://jsonapi.org) HTTP requests and generates compatible responses. The 
goal is Ember.js Data support with minimal configuration.

It is designed to work on top of almost any ORM instead of supporting only one.
This is done through adapter interfaces.

Currently only Yii 1.1 Active Record classes are supported.

Example
-------

An example Yii 1.1 controller using active record.

```php
use PhpEmber\AdapterContainer;
use PhpEmber\TypedSerializer;
use PhpEmber\RestHandler;
use PhpEmber\Yii\ActiveAdapter;
use Symfony\Component\HttpFoundation\Request;

class ApiController {
	
	function actionRest() {
		
		$adapters = new AdapterContainer; 
		
		// register a callback to set up a model adapter when 
		// needed where the first argument ("user" in this 
		// case) is the model type name.
		
		$adapters->register('user', function($adapters, $name) {
			
			// the adapter will load the ActiveRecord class 
			// from the type name ("user" => "User")
			return new ActiveAdapter($name, $adapters);
		});
		
		$serializer = new TypedSerializer;
		
		$handler = new RestHandler($adapters, $serializer);
		
		// php-ember uses Symfony 2 http-foundation to abstract
		// requests and responses
		$handler->run(Request::createFromGlobals())->send();
	}
}
```

And the URL patterns:

```php
array(
	'api/<scope>' => 'api/rest',
	'api/<scope>/<id>' => 'api/rest'
)
```

Then, the following routes are handled:

* GET /api/users all users.
* GET /api/users/:id specified user.
* POST /api/users creates a new user.
* PUT /api/users/:id updates an existing user.
* DELETE /api/users/:id deletes an existing user.

A response of GET /api/users:

```json
{
	"user": [{
		"id":"1",
		"name": "admin",
		"email": "admin@example.com"
	}]
}
```

Relations are supported:

```php
$adapters->register('user', function($adapters, $name) {
	
	$adapter = new ActiveAdapter($name, $adapters);
	
	// a "hasMany" relation
	// the true there means that related posts should be
	// sent to the client in the same request
	$adapter->enableRelation('posts', true);
	return $adapter;
});

$adapters->register('post', function($adapters, $name) {
	
	$adapter = new ActiveAdapter($name, $adapters);
	
	// "belongsTo" relation
	$adapter->enableRelation('user', true);
	return $adapter;
});
```

Now GET /api/users yields:

```json
{
	"user": [{
		"id": "1",
		"name": "admin",
		"email": "admin@example.com",
		"posts": ["1", "2"]
	}],
	
	"post": [{
		"id": "1",
		"user": "1",
		"title": "Hello World",
		"text": "A sample post"
	}, {
		"id": "2",
		"user": "1",
		"title": "Another post",
		"text": "Sample text here"
	}]
}
```

Also the route /api/posts is handled too.
