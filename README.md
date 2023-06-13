# GraphQL Factory
> ⚠️ This is a work in progress that might never reach a 1.x release

This library converts arbitrary data model classes into types that can be used with [webonyx/graphql-php](https://github.com/webonyx/graphql-php) using PHP's Reflection API.
It does so by recursively mapping the object's public properties to a scalar type provided by GraphQL. It does **not** overwrite the default resolver from the library.

## Usage
### Initializing
The factory requires a dependency resolver when resolving queries; see "Resolving with dependencies" for more details on that.
```php
$graphQLFactory = new \Filecage\GraphQLFactory\Factory($dependencyResolver)
```

### Mapping a data model

```php
// Let's define a model
class Person {
    function __construct (
        public readonly string $name
    ) {}
}

// And another model that references the Person
class User {
    function __construct (
        public readonly int $id,
        public readonly Person $person,
    ) {}
}

// Then generate the `ObjectType` for usage with webonyx
$objectType = $graphQLFactory->forType(User::class);
```

### Defining Queries
Queries must inherit from the [`Query`](src/Queries/Query.php) class and provide a description, a return type class name and, if provided, any arguments.
They also have to implement a resolver method that takes the same arguments as a resolve function in webonyx/graphql-php:

Let's build a query to get our user models based on the ID:
```php
class GetUser extends \Filecage\GraphQLFactory\Queries\Query {
    private const USERS = [
        1 => 'David',
        2 => 'Also David, but better' 
    ];

    function __construct() {
        parent::__construct(
            description: 'Allows loading the only user we know about',
            returnTypeClassName: User::class,
            arguments: new \Filecage\GraphQLFactory\Queries\Argument(
                name: 'id',
                description: "The user's ID",
                type: \GraphQL\Type\Definition\Type::int()  
            )
        );
    }
    
    function resolve(mixed $rootValue = null, array $arguments = []) : ?User {
        $user = self::USERS[$arguments['id']] ?? null;
        if ($user === null) {
            return null;
        }
        
        return new User($arguments['id'], new Person($user));
    }
}
```
Using this query as entry point, we can generate a schema and print it
```php
$queryType = $graphQLFactory->forQuery(GetUser::class); // the factory builds one query type out of an arbitrary number of queries
$schema = new \GraphQL\Type\Schema(['query' => $queryType);

echo \GraphQL\Utils\SchemaPrinter::doPrint($schema);
```

This gives us the following result:
```graphql
type Query {
  "Allows loading the only user we know about"
  GetUser(
    "The user's ID"
    id: Int
  ): User
}

type User {
  id: Int!
  person: Person!
}

type Person {
  name: String!
}
```

### Resolving with dependencies
It is most likely that when resolving a query, you would want to use a dependency like a database connection or an API client or whatever.
To do so, a `Query` may return a `callable` instead of an object or `null`. This callable will then be passed to the previously defined
`$dependencyResolver` that allows hooking in for the usage of a dependency resolver.

How dependencies are resolved is totally up to the user implementation.

An example setup using [Creator](https://github.com/filecage/creator), a modern reflection-based auto-wiring dependency resolver, could look like this:
```php
$creator = new \Creator\Creator();
$graphQLFactory = new \Filecage\GraphQLFactory\Factory(fn (callable $resolved) => $creator->invoke($resolved));
```
Having set up a dependency resolver like Creator, our query example could now look like this:
```php
class GetUser extends \Filecage\GraphQLFactory\Queries\Query {
    function __construct() {
        parent::__construct(
            description: 'Allows loading users from our UserLoader',
            returnTypeClassName: User::class,
            arguments: new \Filecage\GraphQLFactory\Queries\Argument(
                name: 'id',
                description: "The user's ID",
                type: \GraphQL\Type\Definition\Type::int()  
            )
        );
    }
    
    function resolve(mixed $rootValue = null, array $arguments = []) : callable {
        return fn(UserLoader $userLoader): ?User => $userLoader->loadUserById($arguments['id']);
    }
}
```