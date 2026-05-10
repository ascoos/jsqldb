## **JSQLDB - JSON SQL Database for PHP**
## 💬 **Μια ελαφριά, SQL-like βάση δεδομένων βασισμένη σε JSON για PHP**  

Το **JSQLDB** είναι ένα **ευέλικτο σύστημα βάσης δεδομένων** που **αξιοποιεί JSON** για αποθήκευση και παρέχει **SQL-like queries** χωρίς την ανάγκη για SQLite ή MySQL. Είναι **ελαφρύ, γρήγορο** και **ιδανικό** για εφαρμογές που χρειάζονται **φορητότητα και ασφάλεια**.

---

![JSQL Database](https://cdn.ascoos.com/images/jsqldb/jsqldb.png)

---

## **🚀 Χαρακτηριστικά**
- ✅ **JSON-based αποθήκευση** χωρίς DLL/SO εξαρτήσεις  
- ✅ **SQL-like queries** με υποστήριξη `SELECT`, `INSERT`, `UPDATE`, `DELETE`, `JOIN`, `UNION`, `GROUP - BY`, `HAVING`, `LIMIT`, `ORDER BY`, `DISTINCT` κλπ.  
- ✅ **Υποστήριξη Indexing** για γρήγορη αναζήτηση  
- ✅ **Συμπιεσμένη αποθήκευση** για βελτιστοποιημένη χρήση πόρων  
- ✅ **Προσαρμογή διαδρομής αποθήκευσης** για ασφαλή διαχείριση δεδομένων  
- ✅ **Βελτιστοποιημένο για PHP 8.2+**  

---

## 🧩 Απαιτεί:
- PHP 8.2+
- Ascoos Framework (Για πρόσβαση υψηλού επιπέδου και διαχείριση)
- ionCube loaders.

---

## **💻 Εγκατάσταση**
```bash
git clone https://github.com/alexsoft-software/jsql.git
cd jsql
composer install
```
✏️ **Περισσότερες λεπτομέρειες προσεχώς, στην τεκμηρίωση!**

---

## **📌 Χρήση της Βάσης Δεδομένων**

Την βάση δεδομένων πρέπει να την αρχικοποιήσουμε πριν την χρησιμοποιήσουμε. Αυτό γίνεται μέσω του πίνακα ρυθμίσεων. 


### **📑 Παράδειγμα Ρυθμίσεων**
```php

return [
    'jsql' => [
        'config_path' => '/root/path/conf/config.json', 
        'users_path' => '/root/path/conf/users.json',
        'databases_root_path' => '/root/path/jsql_db',      
    ]
];
```

### **📑 Παράδειγμα Χρήσης**

```php
use ASCOOS\FRAMEWORK\Kernel\DB\JSQLDB;

// Διαβάζουμε από τον πίνακα ρυθμίσεων τις παραμέτρους λειτουργίας της βάσης δεδομένων. 
$conf = require "conf/config.php";

$properties['tables_prefix'] = 'ascoos'; // Θα δώσει π.χ. έναν πίνακα "ascoos_articles'

// Αρχικοποίηση του αντικειμένου της βάσης δεδομένων
$jsql = new TJSQLDB($conf, $properties);

// Δημιουργία βάσης δεδομένων
$jsql->createDatabase('test_db');

// Δημιουργία χρήστη και αντιστοίχιση σε βάση δεδομένων
$jsql->createUser('admin', 'root', 'test_db');

// Επιλογή τρέχουσας βάσης δεδομένων
$jsql->select_db('test_db');

// Δημιουργία πίνακα
$sql = "CREATE TABLE `#__articles` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `article_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `cat_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `user_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `lang_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `title` VARCHAR(200) NOT NULL,
  `content` TEXT NULL COMPRESSED,
  `created` DATETIME NULL DEFAULT CURRENT_TIMESTAMP(),
  `updated` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP()
);";

$jsql->setSQLQuery($sql);
$jsql->execute();

// Εισαγωγή δεδομένων
$query->setSQLQuery("INSERT INTO #__articles (article_id, cat_id, user_id, lang_id, title, content) VALUES 
(1, 1, 1, 1, 'Title 1', 'Test Content 1'),
(1, 1, 1, 2, 'Title 2', 'Test Content 2'),
(2, 2, 1, 1, 'Title 3', 'Test Content 3'),
(3, 3, 1, 1, 'Title 4', 'Test Content 4'),
(3, 3, 1, 2, 'Title 5', 'Test Content 5');
");
$query->execute();

$query = "SELECT article_id AS aid, title, content AS doc FROM #__articles WHERE user_id = ".$my->id." AND lang_id = 1 ORDER BY created DESC LIMIT 10";
$jsql->setSQLQuery($query);
$jsql->execute();
$data = $jsql->getResults();

// Κλείσιμο όλων των ανοιχτών πόρων της βάσης δεδομένων
$jsql->close();

print_r($data);
?>
```

### 📑 **Εναλλακτικός τρόπος δημιουργίας πίνακα**

```php

// Άμεσος τρόπος δημιουργίας πίνακα και της δομής του.
$schema = [
  'id' => 'INT NOT NULL AUTO_INCREMENT PRIMARY KEY',
  'article_id' => 'INT UNSIGNED NOT NULL DEFAULT 0',
  'cat_id' => 'INT UNSIGNED NOT NULL DEFAULT 0',
  'user_id' => 'INT UNSIGNED NOT NULL DEFAULT 0',
  'lang_id' => 'INT UNSIGNED NOT NULL DEFAULT 0',
  'title' => 'VARCHAR(200) NOT NULL',
  'content' => ' TEXT NULL COMPRESSED',
  'created' => ' DATETIME NULL DEFAULT CURRENT_TIMESTAMP()',
  'updated' => 'DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP()'
];
$jsql->createTable('#__articles', $schema);
```

📌 **Δείτε περισσότερα παραδείγματα στην επίσημη ιστοσελίδα!**  

