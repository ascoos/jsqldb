![JSQL Database](https://cdn.ascoos.com/images/jsqldb/jsqldb.png)

# JSQLDB - Μηχανή Βάσης Δεδομένων JSON με SQL Σύνταξη για το Ascoos OS

Η **JSQLDB** αποτελεί την ενσωματωμένη μηχανή βάσης δεδομένων του Ascoos OS και βασίζεται αποκλειστικά σε αρχεία **JSON**, με υποστήριξη SQL-like ερωτημάτων.  
Σχεδιάστηκε για εφαρμογές που χρειάζονται φορητότητα, ασφάλεια, ταχύτητα και πλήρη ανεξαρτησία από MySQL, MariaDB ή SQLite.

Η λειτουργία της είναι πλήρως ενοποιημένη με τον πυρήνα του Ascoos OS, ενώ η πρόσβαση στη βάση γίνεται μέσω του `TJSQLDBHandler`.  
Όλες οι διαδρομές, τα αρχεία και οι ρυθμίσεις διαχειρίζονται από το ίδιο το λειτουργικό σύστημα, εξασφαλίζοντας σταθερότητα και ασφάλεια.

---

## Με πρώτη ματιά

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

## Τι προσφέρει η JSQLDB

Η JSQLDB αποθηκεύει τα δεδομένα σε JSON αρχεία, υποστηρίζει ευρετήρια, συμπίεση στα TEXT πεδία και SQL σύνταξη για CRUD λειτουργίες.  
Η μηχανή είναι βελτιστοποιημένη για PHP 8.4+ και αξιοποιεί τον **TUTF8 Grapheme Engine** του Ascoos OS, ώστε η επεξεργασία κειμένου να γίνεται με βάση τα πραγματικά οπτικά σύμβολα και όχι τα bytes.

Αυτό σημαίνει ότι η βάση δεν «κόβει» ποτέ χαρακτήρες στη μέση, υπολογίζει σωστά το μήκος σύνθετων χαρακτήρων (emoji, σημαίες, συνδυαστικά σύμβολα) και δημιουργεί ευρετήρια που λειτουργούν σωστά σε όλες τις γλώσσες.

---

## Ρύθμιση και Αρχικοποίηση

Η JSQLDB δεν απαιτεί εγκατάσταση.  
Οι βασικές διαδρομές (config, users, root path) ορίζονται στο configuration του Ascoos OS και είναι κρυπτογραφημένες.

Παράδειγμα ρυθμίσεων συστήματος:

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

Κάθε domain/subdomain μπορεί να έχει δική του βάση, με δικά του credentials:

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

## Παράδειγμα 1 - Δημιουργία βάσης, πίνακα και μαζική εισαγωγή

Το παρακάτω παράδειγμα (`examples/example3.php`) δείχνει ένα πλήρες workflow:  
δημιουργία βάσης, χρήστη, πίνακα, batch insert και ανάκτηση δεδομένων.

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

## Παράδειγμα 2 - Σύστημα ειδήσεων με JOIN και δημοσιευμένα άρθρα

Το δεύτερο παράδειγμα (`examples/example4.php`) παρουσιάζει ένα πιο σύνθετο σενάριο:  
πίνακας ειδήσεων με slug, excerpt, content, timestamps, ENUM status και JOIN με χρήστες και κατηγορίες.

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

## Ο TUTF8 Grapheme Engine

Η JSQLDB χρησιμοποιεί τον TUTF8 Engine του Ascoos OS, ο οποίος υπολογίζει το μήκος και την επεξεργασία κειμένου με βάση τα **Grapheme Clusters**.

Παράδειγμα:

| Χαρακτήρας | strlen() | mb_strlen() | utf8->strlen() |
|-----------|----------|-------------|----------------|
| 🚀        | 4        | 1           | 1              |
| 👍🏽       | 8        | 2           | 1              |
| 🇨🇳       | 8        | 2           | 1              |

Αυτό εξασφαλίζει:

- σωστή αποθήκευση  
- σωστό indexing  
- αποφυγή κομμένων χαρακτήρων  
- πλήρη συμβατότητα με όλες τις γλώσσες  

---

## Άδεια

Η JSQLDB αποτελεί μέρος του **Ascoos OS** και διανέμεται υπό την επίσημη άδεια του έργου.
