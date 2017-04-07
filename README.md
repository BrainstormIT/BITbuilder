![BITBUILDER](http://i.imgur.com/GuUrLw1.png)

# About BITbuilder
**BITbuilder** is a MySQL querybuilder created by software development interns at [**Brainstorm IT**](https://www.brainstormit.nl/) and uses the PHP Data Objects extension (PDO) for accessing databases. 
This querybuilder is designed to be as simple as possible with almost no learning curve attached. 
[**Laravels**](https://laravel.com/docs/5.4/queries) querybuilder has been an inspiration to how we wanted the BITbuilder syntax to look like, so syntax wise a few comparisons can be made.

# Requirements
- PHP 5.6+
- composer

# Installation
You can install BITbuilder through **composer**:
```php
composer require 'brainstormit/bitbuilder'
```

# Get started!
First things first! We need a **PDO** **Database** object for BITbuilder to work.<br>
The Database helper class can easily create one (You don't necessarily need to use this).<br>
Navigate to `src/helpers/Database.php` and edit your database configurations:

```php
$this->db_type = 'mysql';
$this->db_host = 'localhost';
$this->db_name = 'qbtest';
$this->db_username = 'root';
$this->db_password = '';
```


With the database configurations all set up we can create our database object, 
and even more important: our first **BITbuilder** object!

```php
$db = new Database();

// BITbuilder object
$b = new Builder($db);
```

# Selecting your table
Before we start with the fun stuff we need to select<br>
the table we want to work with.<br>
Let's assume we want to select the `users` table:

```php
$tbl = $b->table('users');
```

# SELECT statements
If you want to select all users in the `users` table we can perform an<br>
`SELECT *` with the following:

```php
$record = $tbl->select('*')->fetchAll();
```

It's also possible to provide an array with all the fields you want to select:
```php
$fields = array('first_name', 'last_name', 'email');
$record = $tbl->select($fields)->fetchAll();
```

# WHERE clauses
You can add a `WHERE` clause to your statement with the following:

```php
$record = $tbl->select(['first_name', 'email'])
              ->where('id', 89)
              ->fetchAll();
```
A different operator can be provided as second parameter. <br>
The third parameter then becomes the value:

```php
$record = $tbl->select(['first_name', 'email'])
              ->where('id', '<=', 51)
              ->fetch();
```

Valid operators: `= != < > >= <=`

# AND & OR operators
`OR` & `AND` operators can be added to your clauses with the following:

```php
// AND operator
$record = $tbl->select(['first_name', 'email'])
              ->where('id', 23)
              ->and_('email', '!=', 'johndoe@example.com')
              ->fetchAll();
              
// OR operator
$record = $tbl->select(['first_name', 'email'])
              ->where('id', 69)
              ->or_('last_name', '=', 'Smith')
              ->fetchAll();
```
`and_` and `or_` have an underscore after their method name because PHP doesn't allow `PHP reserved names`
to be used as method names.

# ORDER BY keyword
Ordering a selected record can be done with the following: 

```php
$record = $tbl->select('*')
              ->orderBy('id')
              ->fetchAll();
```
The default order is **ascending**. This can easily be changed by adding `DESC` as second parameter:
```php
->orderBy('id', 'DESC')
```

# GROUP BY keyword
Grouping a selected record can be done with the following: 

```php
$record = $tbl->select('*')
              ->GroupBy('email')
              ->fetchAll();
```

# LIMIT keyword
Limiting the amount of items in a record can be done by adding a `LIMIT` to your query:
```php
$record = $tbl->select('*')
              ->limit(5)
              ->fetchAl();
```


# INSERT statements
Inserting a new record into the `users` table would look similar to this:

```php
$insert = [
    'first_name' => 'John',
    'last_name' => 'Doe',
    'email' => 'johndoe@example.com'
];

$tbl->insert($insert);
```
The array which contains your insert info has to be **associative**. <br>
The array key represents the table field, and the array value represents<br>
the value you want to insert into your table.

# DELETE statements
Deleting a record from the `users` table would look similar to this:

```php
$tbl->delete('id', 44);
```
The second and third parameter represents the `WHERE` clause.<br>
A where clause can also be added manually:

```php
$tbl->delete()
    ->where('id', 44)
    ->exec()
```
The `exec()` method is needed to manually execute the query.

# UPDATE statements
Updating a record in the `users` table would look similar to this:

```php
$update = array('first_name' => 'Crazy', 'last_name' => 'Frog');
$tbl->update($update, 'id', 59);
```
The array which contains your update info has to be **associative**, just like the `insert()` method. <br>
The array key represents the table field you want to update, and the array value represents<br>
the value.<br>

Just like the `delete()` method it's possible to manually add a `WHERE` clause if you'd like:
```php
$update = array('first_name' => 'Crazy', 'last_name' => 'Frog');

$tbl->update($update)
    ->where('id', 59)
    ->exec();
```

# Joins
Let's assume we want to develop a platform where users can post pictures.<br>
If you want to select all pictures that belong to a certain user,
your join would look similar to this:
```php
// pictures table
$tbl = $b->table('pictures AS p');

$join = $tbl->select('*')
            ->join('users AS u', 'p.userid', 'u.id')
            ->fetchAll();
```
The table yould want to join with should be passed as the first parameter.<br>
The second and third parameter represent the `ON` of the join

Available joins: `INNER JOIN (join()), LEFT JOIN (leftJoin()), RIGHT JOIN (rightJoin()), OUTER JOIN (outerJoin())`

# Executing raw queries
Raw queries can be executed as well:
```php
$tbl->raw('SELECT COUNT(*) FROM users')->fetchAll();
```
or:
```php
$tbl->raw('DELETE FROM users WHERE id = 77')->exec();
```

# License
BITbuilder is open-sourced software licensed under the MIT license.
