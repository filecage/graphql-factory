# GraphQL Factory
> ⚠️ This is a work in progress that might never reach a 1.x release

This library converts arbitrary data model classes into types that can be used with [webonyx/graphql-php](https://github.com/webonyx/graphql-php) using PHP's Reflection API.
It does so by recursively mapping the object's public properties to a scalar type provided by GraphQL. It overwrites webonyx' default resolver where necessary
(see [Resolving Data Fields](#resolving-getter-methods)).

## Usage
### Initializing
The factory requires a dependency resolver when resolving queries; see "Resolving with dependencies" for more details on that.
```php
$graphQLFactory = new \Filecage\GraphQL\Factory\Factory($dependencyResolver)
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

### Defining IDs
To make use of GraphQLs `ID` type (and its caching benefits), add the `Identifier` attribute to a property or a getter/promoted method:
```php
class User {
    function __construct (
        #[\Filecage\GraphQL\Annotations\Attributes\Identifier]
        public readonly int $id,
        public readonly Person $person,
    ) {}
}
```
Only `int`, `string` or `Stringable` types can be used as `Identifier`.

### Defining Queries
Queries must inherit from the [`Query`](src/Queries/Query.php) class and provide a description, a return type class name and, if provided, any arguments.
They also have to implement a resolver method that takes the same arguments as a resolve function in webonyx/graphql-php:

Let's build a query to get our user models based on the ID:
```php
class GetUser extends \Filecage\GraphQL\Factory\Queries\Query {
    private const USERS = [
        1 => 'David',
        2 => 'Also David, but better' 
    ];

    function __construct() {
        parent::__construct(
            description: 'Allows loading the only user we know about',
            returnTypeClassName: User::class,
            arguments: new \Filecage\GraphQL\Factory\Queries\Argument(
                name: 'id',
                description: "The user's ID",
                type: \GraphQL\Type\Definition\Type::id()  
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

#### Using schema data models or enums as argument
When using classes or enums as argument types that is also in use for returning data in your schema, you need to define the exact type instance
from your schema or the schema generation will fail because you're using the same type name twice.

To do that in a convenient way, you can reference your type using the `ArgumentType` class. The factory will then consider its internally knowledge
of this class and make sure that the same type instance is being used:
```php
enum UserType {
    case Admin;
    case User;
}

class GetUserByType extends \Filecage\GraphQL\Factory\Queries\Query {
    private const USERS = [
        1 => ['David', UserType::User],
        2 => ['Also David, but better', UserType::Admin] 
    ];

    function __construct() {
        parent::__construct(
            description: 'Allows loading the first user with a certain type',
            returnTypeClassName: User::class,
            arguments: new \Filecage\GraphQL\Factory\Queries\Argument(
                name: 'id',
                description: "The user's type",
                type: new \Filecage\GraphQL\Factory\Queries\ArgumentType(UserType::class)  
            )
        );
    }
    
    function resolve(mixed $rootValue = null, array $arguments = []) : ?User {
        /** @var UserType $type */
        $type = $arguments['type'];
        foreach (self::USERS as $id => [$user, $userType]) {
            if ($userType === $type) {
                return new User($id, new Person($user));
            }
        }
        
        return null;
    }
}
```

#### Using other custom types inside the schema
Additionally, you can use `TypePromise` to pass a promise that eventually resolves to a `Type` with access to the `Factory` to access
schema entities during schema creation. This can also be used to access types like enums and is a more advanced alternative to `ArgumentType`

```php
class UserMutationInput implements \Filecage\GraphQL\Factory\Interfaces\TypePromise {
    static function resolveType(\Filecage\GraphQL\Factory\Factory $factory) : \GraphQL\Type\Definition\Type{
        return new \GraphQL\Type\Definition\InputObjectType([
            'name' => 'UserMutationInput',
            'fields' => [
                'userType' => [
                    'type' => $factory->forType(UserType::class)
                ]            
            ]
        ]);
    }
}
```

and use it in `Argument`:

```php
new \Filecage\GraphQL\Factory\Queries\Argument(
    name: 'user',
    description: "The user mutation",
    type: new UserMutationInput  
)
```

Each returned type will be cached inside the factory instance with the class name as cache key.

### Resolving with dependencies
It is most likely that when resolving a query, you would want to use a dependency like a database connection or an API client or whatever.
To do so, a `Query` may return a `callable` instead of an object or `null`. This callable will then be passed to the previously defined
`$dependencyResolver` that allows hooking in for the usage of a dependency resolver.

How dependencies are resolved is totally up to the user implementation.

An example setup using [Creator](https://github.com/filecage/creator), a modern reflection-based auto-wiring dependency resolver, could look like this:
```php
$creator = new \Creator\Creator();
$graphQLFactory = new \Filecage\GraphQL\Factory\Factory(fn (callable $resolved) => $creator->invoke($resolved));
```
Having set up a dependency resolver like Creator, our query example could now look like this:
```php
class GetUser extends \Filecage\GraphQL\Factory\Queries\Query {
    function __construct() {
        parent::__construct(
            description: 'Allows loading users from our UserLoader',
            returnTypeClassName: User::class,
            arguments: new \Filecage\GraphQL\Factory\Queries\Argument(
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

### Resolving Getter Methods
Public getter methods (methods starting with `get`) from your model will be included in the schema. For this purpose a custom
resolver function is added to the schema that then calls the method. This can be surpress using the `#[Filecage\GraphQL\Annotations\Ignore]` annotation.

Additionally, the `#[Filecage\GraphQL\Annotations\Promote]` attribute does the opposite and promotes a non-exported method to the schema
that would otherwise be missing.

```php
class Person {
    function getName() {} // This will be part of the schema automatically because the method's name starts with 'get'
    
    #[\Filecage\GraphQL\Annotations\Attributes\Ignore]
    function getNameSecret() {} // This won't be part of the schema automatically because it has the `Ignore` attribute
    
    function isSecret() {} // This won't be part of the schema automatically because the method's name does not start with `get`
    
    #[\Filecage\GraphQL\Annotations\Attributes\Promote]
    function isCelebrity() {} // This will be part of the schema because it has the `Promote` attribute
}
```

#### Resolving Union Types
GraphQL and PHP both support union types. However, GraphQL requires them to be [named and unique](https://graphql.org/learn/schema/#union-types)
throughout a schema. This is why all union types need to be named using the `TypeAlias` attribute annotation:

Additionally, only object types are allowed as union types in GraphQL.

#### Single Attribute Union Type
```php
class MyModel {
    #[\Filecage\GraphQL\Annotations\Attributes\TypeAlias('FooBarUnion')]
    public readonly Foo|Bar $baz;
}
```

#### List Attribute Union Type
```php
class MyModel {
    #[\Filecage\GraphQL\Annotations\Attributes\Contains(Foo::class)];
    #[\Filecage\GraphQL\Annotations\Attributes\Contains(Bar::class)];
    #[\Filecage\GraphQL\Annotations\Attributes\TypeAlias('FooBarUnion')]
    public readonly array $baz;
}
```

#### Managing Union Type Names Throughout The Schema
The `TypeAlias` attribute accepts enums and will use the enum's name or, if it's string backed, its value.

If this annotation is missing, the library will throw an `InvalidTypeException` with the message
> Missing union type `TypeAlias` attribute declaration
upon creating the type.

The library will also ensure that return signatures match when using identical union type names.

### Resolving Internal Types
Some internal types will be mapped, so they won't break your existing interface. A good example is `DateTimeInterface`, that
will be mapped to an object with different properties to access the DateTime values.

To see what type maps exists and how they resolve, see [Types](src/Types).

### Argument Fixtures
Some arguments might be used multiple times within your project, so it might make sense to share these arguments
within different queries. This can easily be done by defining a class with pre-defined argument values:

```php
class UserIdArgument {
    function __construct() {
        parent::__construct(
            'userId', 
            "The user's ID",
            \GraphQL\Type\Definition\Type::int()
        )
    }
}
```
I call this pattern 'argument fixture'. Use your Argument Fixture in any given query then:
```php
class GetUser extends \Filecage\GraphQL\Factory\Queries\Query {
    function __construct() {
        parent::__construct(
            description: 'Allows loading users from our UserLoader',
            returnTypeClassName: User::class,
            arguments: new UserIdArgument()
        )
    }
}
```

### Argument Explosion
Argument Fixtures can implement a resolve function very similar to a query's resolver.
This in-between resolver allows resolving an argument to one or many additional arguments that will be passed
to the consuming query.

```php
class UserIdArgument implements \Filecage\GraphQL\Factory\Interfaces\Argument\Resolvable {
    function __construct() {
        parent::__construct(
            'userId', 
            "The user's ID",
            \GraphQL\Type\Definition\Type::int()
        )
    }
    
    function resolve(mixed $rootValue = null, array $arguments = []) : callable {
        return function (UserLoader $userLoader) : \Generator {
            yield 'user' => $userLoader->loadUserById($arguments['id']);
        }
    }
}
```

This will add a `user` argument to the `arguments` array, accessible for all subsequent consumers (including argument resolvers).
