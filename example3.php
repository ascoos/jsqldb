<?php
/*
dobu {
    file:id(`example-17380`) {
        ascoos {
            logo {`
                  __ _  ___  ___ ___   ___   ___     ___   ___
                 / _` |/  / / __/ _ \ / _ \ /  /    / _ \ /  /
                | (_| |\  \| (_| (_) | (_) |\  \   | (_) |\  \
                 \__,_|/__/ \___\___/ \___/ /__/    \___/ /__/
            `},
            name {`ASCOOS OS`},
            version {`1.0.0`},
        },
        example {
            class {`TJSQLDBHandler`},
            methods {`createDatabase(), createUser(), selectDatabase(), setSQLQuery(), bind(), execute(), getResults(), getLastError(), close()`},
            source {`example3.php`},
            category:langs {
                en {`Databases`},
                el {`Βάσεις Δεδομένων`}
            },
            subcategory:langs {
                en {`JSQLDB`},
                el {`JSQLDB`}
            },
            summary:langs {
                en {`Example demonstrating database creation, user assignment, table creation, batch inserts with prepared statements, and data retrieval using the JSQLDB engine.`},
                el {`Παράδειγμα που δείχνει τη δημιουργία βάσης, αντιστοίχιση χρήστη, δημιουργία πίνακα, μαζικές εισαγωγές με prepared statements και ανάκτηση δεδομένων μέσω της JSQLDB.`}
            },
            desc:langs {
                en {`This example showcases a complete workflow using the TJSQLDBHandler:
                    - initializing the database handler,
                    - creating a database and user,
                    - creating a table using SQL DDL,
                    - inserting multiple records using batch prepared statements,
                    - executing a SELECT query with ordering and limits,
                    - retrieving results,
                    - and properly closing all database resources.

                    It demonstrates real‑world usage of JSQLDB inside Ascoos OS, including error handling and logging integration.`},
                el {`Αυτό το παράδειγμα παρουσιάζει ένα πλήρες workflow με το TJSQLDBHandler:
                 - αρχικοποίηση του database handler,
                 - δημιουργία βάσης και χρήστη,
                 - δημιουργία πίνακα μέσω SQL DDL,
                 - εισαγωγή πολλαπλών εγγραφών με batch prepared statements,
                 - εκτέλεση SELECT με ταξινόμηση και όριο,
                 - ανάκτηση αποτελεσμάτων,
                 - και σωστό κλείσιμο όλων των πόρων της βάσης.

                 Αναδεικνύει πραγματική χρήση της JSQLDB μέσα στο Ascoos OS, συμπεριλαμβανομένου του error handling και του logging.`}
            },
            keywords {`jsqldb, database, json-sql, sql-engine, ascoos-os,prepared-statements, batch-insert, select-query,table-creation, utf8, grapheme, example`},
            tags {`example, jsqldb, database, insert, select, handler,prepared, batch, migration, demo`},
            author {`Drogidis Christos`},
            license {`AGL (ASCOOS General License)`},
            since {`1.0.0`},
            sincePHP {`8.4.0`}
        }
    }
}
*/
declare(strict_types=1);

use ASCOOS\OS\Kernel\DB\JSQLDB\TJSQLDBHandler;
use ASCOOS\OS\Kernel\Core\TError;

global $conf, $my;

$properties = [
    'tables_prefix' => 'ascoos_',   // τελικό prefix π.χ. ascoos_articles
    'default_compression' => true   // compression στα TEXT
];

// Αρχικοποίηση του αντικειμένου της βάσης δεδομένων
$jsqldb = new TJSQLDBHandler($conf, $properties);


// 1. Δημιουργία ή επιλογή βάσης (συνήθως το κάνεις 1 φορά ή στο install)
try {
    // Δημιουργία βάσης δεδομένων
    $jsqldb->createDatabase('test_db');

    // Δημιουργία χρήστη και αντιστοίχιση σε βάση δεδομένων
    $jsqldb->createUser('user', 'pass', 'test_db');

    // Επιλογή τρέχουσας βάσης δεδομένων
    $jsqldb->selectDatabase('test_db');
} catch (Exception $e) {
    $jsqldb->logger->log("Database init error: " . $e->getMessage(), $jsqldb::DEBUG_LEVEL_ERROR);
    $jsqldb->close();
    new TError("Πρόβλημα αρχικοποίησης βάσης δεδομένων.", E_ASCOOS_DB_JSQLDB_NOT_INIT);
}

// 2. Δημιουργία πίνακα (συνήθως στο migration/install script)
$sql = "CREATE TABLE IF NOT EXISTS `#__articles` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `article_id`  INT UNSIGNED NOT NULL DEFAULT 0,
  `cat_id`      INT UNSIGNED NOT NULL DEFAULT 0,
  `user_id`     INT UNSIGNED NOT NULL DEFAULT 0,
  `lang_id`     INT UNSIGNED NOT NULL DEFAULT 0,
  `title`       VARCHAR(200) NOT NULL,
  `content`     TEXT         NULL     COMPRESSED,
  `created`     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  `updated`     DATETIME     NULL     ON UPDATE CURRENT_TIMESTAMP()
);";

$jsqldb->setSQLQuery($sql);
if (!$jsqldb->execute()) {
    $jsqldb->close();
    new TError("Αποτυχία δημιουργίας πίνακα: " . $jsqldb->logger->get_last_log(), E_ASCOOS_DB_JSQLDB_CREATE_TABLE);
}


// 3. Εισαγωγή εγγραφής (prepared statement)
$insertQuery = "INSERT INTO #__articles (article_id, cat_id, user_id, lang_id, title, content) 
    VALUES (?, ?, ?, ?, ?, ?)
";

$data = [
    [1, 1, 1, 1, 'Title 1', 'Test Content 1'],
    [1, 1, 1, 2, 'Title 2', 'Test Content 2'],
    [2, 2, 1, 1, 'Title 3', 'Test Content 3'],
    [3, 3, 1, 1, 'Title 4', 'Test Content 4'],
    [3, 3, 1, 2, 'Title 5', 'Test Content 5']
];

$types = 'iiiiss';   // int, int, int, int, string, string

$jsqldb->bind($types, $data, $insertQuery);   // notice: array μέσα σε array για batch
if (!$jsqldb->execute()) {
    $jsqldb->close();
    new TError("Insert failed: " . $jsqldb->getLastError(), E_ASCOOS_DB_JSQLDB_INSERT_DATA);
}


// 4. Αναζήτηση - απλή 
$selectQuery = "SELECT article_id AS aid, title, content AS doc
    FROM #__articles
    WHERE user_id = ? AND lang_id = ?
    ORDER BY created DESC
    LIMIT 10
";

$jsqldb->bind('ii', [$my->id, 1], $selectQuery);
if (!$jsqldb->execute()) {
    $jsqldb->close();
    new TError("Insert failed: " . $jsqldb->getLastError(), E_ASCOOS_DB_JSQLDB_SELECT_QUERY);
}
$data = $jsqldb->getResults();


// 5. Κλείσιμο όλων των ανοιχτών πόρων της βάσης δεδομένων
$jsqldb->close();

print_r($data);
?>
