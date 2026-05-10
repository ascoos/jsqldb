![JSQL Database](https://cdn.ascoos.com/images/jsqldb/jsqldb.png)

# JSQLDB - JSON Database Engine with SQL Syntax for Ascoos OS

**JSQLDB** is the built‑in database engine of Ascoos OS.  
It is based entirely on **JSON files**, while supporting SQL‑like queries for everyday data operations.  
It is designed for applications that require portability, security, speed, and complete independence from MySQL, MariaDB or SQLite.

The engine is fully integrated into the Ascoos OS kernel, and all interaction with the database is done through the `TJSQLDBHandler`.  
All paths, files and configuration values are managed by the operating system itself, ensuring stability and security.

---

## At a glance

```php
$db = new TJSQLDBHandler($conf);

$db->setSQLQuery("
    SELECT title, created_at
    FROM #__articles
    ORDER BY created_at DESC
    LIMIT 5
");

$db->execute();

print_r($db->getResults());

$db->close();
```

---

## What JSQLDB provides

JSQLDB stores all data in JSON files, supports indexing, compression for TEXT fields, and SQL syntax for CRUD operations.  
The engine is optimized for PHP 8.4+ and uses the **TUTF8 Grapheme Engine** of Ascoos OS, ensuring that text is processed based on visible characters rather than raw bytes.

This means the database never breaks characters in the middle, calculates the correct length of complex symbols (emoji, flags, combined characters), and produces indexes that behave correctly across all languages.

---

## Configuration and Initialization

JSQLDB requires no installation.  
The main paths (config, users, root path) are defined in the Ascoos OS configuration and are encrypted.

Example system configuration:

```php
'db' => [
    'db_driver' => 'jsqldb',
    'jsqldb' => [
        'config_path'  => '/path/to/jsqldb/conf/config.json',
        'users_path'   => '/path/to/jsqldb/conf/users.json',
        'db_root_path' => '/path/to/jsqldb/db',
        'dbname'       => 'jsqldb',
    ],
],
```

Each domain/subdomain may have its own database with its own credentials:

```php
'db' => [
    'db_driver' => 'jsqldb',
    'jsqldb' => [
        'host' => 'localhost',
        'port' => 28031,
        'user' => 'user',
        'pass' => 'pass',
        'dbname' => 'test_db',
    ],
],
```

---

## Example 1 - Creating a database, a table and inserting multiple records

The following example (`examples/example3.php`) demonstrates a complete workflow:  
creating a database, assigning a user, creating a table, performing a batch insert and retrieving data.

```php
$jsqldb = new TJSQLDBHandler($conf, [
    'tables_prefix' => 'ascoos_',
    'default_compression' => true
]);

$jsqldb->createDatabase('test_db');
$jsqldb->createUser('user', 'pass', 'test_db');
$jsqldb->selectDatabase('test_db');

$sql = "CREATE TABLE IF NOT EXISTS `#__articles` (...);";
$jsqldb->setSQLQuery($sql);
$jsqldb->execute();

$insertQuery = "INSERT INTO #__articles (...) VALUES (?, ?, ?, ?, ?, ?)";
$jsqldb->bind('iiiiss', [$data], $insertQuery);
$jsqldb->execute();

$jsqldb->setSQLQuery("SELECT * FROM #__articles LIMIT 10");
$jsqldb->execute();
$data = $jsqldb->getResults();

$jsqldb->close();
```

---

## Example 2 - News system with JOIN and published articles

The second example (`examples/example4.php`) shows a more complete scenario:  
a news table with slug, excerpt, content, timestamps, ENUM status and JOIN with users and categories.

```php
$jsqldb->createDatabase('myapp_2026');
$jsqldb->createUser('webuser', 'strongPass123!', 'myapp_2026');
$jsqldb->selectDatabase('myapp_2026');

$createTable = "CREATE TABLE IF NOT EXISTS `#__news` (...);";
$jsqldb->setSQLQuery($createTable);
$jsqldb->execute();

$insertQuery = "INSERT INTO #__news (...) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
$jsqldb->bind('ssssiiis', $data, $insertQuery);
$jsqldb->execute();

$selectQuery = "
    SELECT n.id, n.title, n.excerpt, n.published_at,
           u.username AS author_name,
           c.name     AS category_name
    FROM #__news n
    LEFT JOIN #__users u ON u.id = n.author_id
    LEFT JOIN #__categories c ON c.id = n.category_id
    WHERE n.status = 'published'
      AND n.published_at <= NOW()
    ORDER BY n.published_at DESC
    LIMIT 10
";

$jsqldb->setSQLQuery($selectQuery);
$jsqldb->execute();
$articles = $jsqldb->getResults();
```

---

## The TUTF8 Grapheme Engine

JSQLDB uses the TUTF8 Engine of Ascoos OS, which measures and processes text based on **Grapheme Clusters**.

Example:

| Character | strlen() | mb_strlen() | utf8->strlen() |
|-----------|----------|-------------|----------------|
| 🚀        | 4        | 1           | 1              |
| 👍🏽       | 8        | 2           | 1              |
| 🇨🇳       | 8        | 2           | 1              |

This ensures:

- correct storage  
- correct indexing  
- no broken characters  
- full multilingual compatibility  

---

## License

JSQLDB is part of **Ascoos OS** and is distributed under the official project license.