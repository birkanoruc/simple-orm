# Simple ORM

**Simple ORM** is a lightweight Object-Relational Mapping library for PHP, designed to simplify database interactions and provide a clean and intuitive API for working with database records.

## Table of Contents

- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
  - [Creating a Database Connection](#creating-a-database-connection)
  - [Creating a Model](#creating-a-model)
  - [Querying Data](#querying-data)
  - [Inserting Data](#inserting-data)
  - [Updating Data](#updating-data)
  - [Deleting Data](#deleting-data)
  - [Relationships](#relationships)
  - [Aggregates](#aggregates)
- [License](#license)

## Installation

To get started with Simple ORM, you can clone the repository or require it via Composer:

```bash
composer require birkanoruc/simple-orm
```

### Configuration

You need to configure your database connection settings. The following parameters are required:

- **host**: Database server address (e.g., `localhost`)
- **port**: Your DB port (e.g., `3306`)
- **dbname**: Database name
- **charset**: Character set (default is `utf8mb4`)

```php
$config = [
'host' => '127.0.0.1',
'port' => 'your_db_port',
'dbname' => 'your_database',
'charset' => 'utf8mb4',
];
```

### Usage

Creating a Database Connection

To create a new database connection, instantiate the Database class with your configuration:

```php
use Birkanoruc\SimpleOrm\Database;

$config = [
    'host' => '127.0.0.1',
    'port' => 'your_db_port',
    'dbname' => 'your_database',
    'charset' => 'utf8mb4',
];

$db = new Database($config);
```

### Creating a Model

Create a model by extending the Model class. Define the table name in the model:

```php
namespace App\Models;

use Birkanoruc\SimpleOrm\Model;

class User extends Model
{
    protected $table = 'users';
}
```

### Querying Data

To retrieve all records:

```php
$userModel = new User($db);
$users = $userModel->all();
```

To retrieve the first record:

```php
$user = $userModel->first();
```

To find a specific record by ID:

```php
$user = $userModel->find(1);
```

To apply a WHERE clause:

```php
$activeUsers = $userModel->where('active', '=', 1)->all();
```

### Inserting Data

To insert a new record:

```php
$newUser = [
    'name' => 'John Doe',
    'email' => 'john@example.com',
];

$userModel->create($newUser);
```

### Updating Data

To update an existing record:

```php
$userModel->update(1, [
    'name' => 'John Smith',
]);
```

### Deleting Data

To delete a record:

```php
$userModel->delete(1);
```

To delete multiple records:

```php
$userModel->destroy([1, 2, 3]);
```

### Relationships

Define relationships within your model:

**Has One**

```php
public function profile()
{
    return $this->hasOne(Profile::class);
}
```

**Has Many**

```php
public function posts()
{
    return $this->hasMany(Post::class);
}
```

**Belongs To**

```php
public function user()
{
    return $this->belongsTo(User::class);
}
```

**Belongs To Many**

```php
public function roles()
{
    return $this->belongsToMany(Role::class, 'user_roles');
}
```

### Aggregates

To perform aggregate queries:

```php
$count = $userModel->count();
$sum = $userModel->sum('age');
$avg = $userModel->avg('salary');
$min = $userModel->min('age');
$max = $userModel->max('age');
```

### License

This project is licensed under the MIT License - see the LICENSE file for details.

### Explanations:

- **Project Introduction**: Clearly states the purpose of the library and what it does.
- **Installation**: Provides information on how to install and configure the library.
- **Usage**: Contains examples of how to use the library.
- **Relationships**: Offers information on how to define model relationships.
- **Aggregates**: Explains how to perform aggregate queries.
