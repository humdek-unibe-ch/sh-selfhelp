<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
/**
 * Class to handle the global communication with the DB
 *
 * @author moiri
 */

require_once __DIR__ . "/Cache.php";
require_once __DIR__ . "/ExtendedPdo.php";

class BaseDb {

    /**
     * The DB handler.
     */
    private $dbh = null;

    /**
     * @var int the current transaction depth
     */
    protected $_transactionDepth = 0;

    /**
     * The cache instance
     */
    protected $cache;

    /**
     * Open connection to mysql database
     *
     * @param string $server:   address of server
     * @param string $dbname:   name of database
     * @param string $username: username
     * @param string $password: password
     * @param string $names:    charset (optional, default: utf8mb4)
     */
    public function __construct($server, $dbname, $username, $password, $clockwork, $names="utf8mb4") {
        $this->cache = new Cache();
        try {
            $this->dbh = new ExtendedPdo(
                "mysql:host=$server;dbname=$dbname;charset=$names",
                $username, $password, array(PDO::ATTR_PERSISTENT => true)
            );
            $this->dbh->setAttribute(PDO::ATTR_ERRMODE,
                PDO::ERRMODE_EXCEPTION);
            $this->dbh->set_clockwork($clockwork);
        }
        catch(PDOException $e)
        {
            if(DEBUG == 1) echo "Connection failed: ".$e->getMessage();
        }
    }

    public function __destruct() {
        $this->dbh = null;
    }

    /**
     * Return the PDO handler.
     *
     * @retval object
     *  The database handler.
     */
    public function get_dbh()
    {
        return $this->dbh;
    }

    /**
     * Exectute an arbitrary query on the db.
     *
     * @param string $sql
     *  The query to be executed.
     *
     * @retval int
     *  The number of affected rows.
     */
    public function execute_db($sql)
    {
        try {
            return $this->dbh->exec($sql);
        }
        catch(PDOException $e) {
            if(DEBUG == 1) echo "BaseDb::execute_db: ".$e->getMessage();
            return false;
        }
    }

    /**
     * Exectute an arbitrary update query on the db.
     *
     * @param string $sql
     *  The query to be executed.
     * @param array $arguments
     *  An associative array with value key pairs where the keys are variable
     *  identifiers used in the query (e.g ':id') which will be replaced with
     *  the associated value on query execution.
     *
     * @retval array
     *  The number of affected rows or false if the query failed.
     */
    public function execute_update_db($sql, $arguments=array())
    {
        try {
            $stmt = $this->dbh->prepare($sql);
            $stmt->execute($arguments);
            return $stmt->rowCount();
        }
        catch(PDOException $e) {
            if(DEBUG == 1) echo "BaseDb::execute_db: ".$e->getMessage();
            return false;
        }
    }

    /**
     * Exectute an arbitrary select query on the db.
     *
     * @param string $sql
     *  The query to be executed.
     * @param array $arguments
     *  An associative array with value key pairs where the keys are variable
     *  identifiers used in the query (e.g ':id') which will be replaced with
     *  the associated value on query execution.
     * @param enum $fetch_style
     *  Controls how the next row will be returned to the caller. Refer to the
     *  [official documentation](https://www.php.net/manual/en/pdostatement.fetch.php)
     *  for more information.
     * @retval array
     *  An array with all row entries or false if no entry was selected.
     */
    public function query_db($sql, $arguments=array(),
        $fetch_style=PDO::FETCH_ASSOC)
    {
        try {
            $stmt = $this->dbh->prepare($sql);
            $stmt->execute($arguments);
            return $stmt->fetchAll($fetch_style);
        }
        catch(PDOException $e)
        {
            if(DEBUG == 1) echo "BaseDb::query_db: ".$e->getMessage();
            return false;
        }
    }

    /**
     * Exectute an arbitrary select query on the db and return the first
     * matching row.
     *
     * @param string $sql
     *  The query to be executed.
     * @param array $arguments
     *  An associative array with value key pairs where the keys are variable
     *  identifiers used in the query (e.g ':id') which will be replaced with
     *  the associated value on query execution.
     * @param enum $fetch_style
     *  Controls how the next row will be returned to the caller. Refer to the
     *  [official documentation](https://www.php.net/manual/en/pdostatement.fetch.php)
     *  for more information.
     * @retval array
     *  All row entries or false if no entry was selected.
     */
    public function query_db_first($sql, $arguments=array(),
        $fetch_style=PDO::FETCH_ASSOC)
    {
        try {
            $stmt = $this->dbh->prepare($sql);
            $stmt->execute($arguments);
            return $stmt->fetch($fetch_style);
        }
        catch(PDOException $e)
        {
            if(DEBUG == 1) echo "BaseDb::query_db_first: ".$e->getMessage();
            return false;
        }
    }

    /**
     * Remove all rows where the foreign key matches.
     *
     * @param string $table
     *  The name of the db table.
     * @param string $fk
     *  The name of the foreign key.
     * @param int $id
     *  The foreign key of the row to be selected
     * @retval bool
     *  True on success, false otherwise.
     */
    public function remove_by_fk($table, $fk, $id) {
        try {
            $sql = "DELETE FROM $table WHERE $fk = :fk";
            $stmt = $this->dbh->prepare($sql);
            return $stmt->execute(array(':fk' => $id));
        }
        catch(PDOException $e) {
            if(DEBUG == 1)
                echo "BaseDb::remove_by_fk: ".$e->getMessage();
            return false;
        }
    }

    /**
     * Remove all rows where the foreign key matches.
     *
     * @param string $table
     *  The name of the db table.
     * @param array $ids
     *  An associative array of where conditions e.g WHERE $key = $value. The
     *  conditions are concatenated with AND.
     * @retval bool
     *  True on success, false otherwise.
     */
    public function remove_by_ids($table, $ids) {
        try {
            $data = array();
            $where_cond = "";
            $first = true;
            foreach($ids as $key => $value) {
                $data[':' . $key] = $value;
                if($first) $where_cond = "WHERE $key = :$key";
                else $where_cond .= " AND $key = :$key";
                $first = false;
            }
            $sql = "DELETE FROM $table $where_cond";
            $stmt = $this->dbh->prepare($sql);
            return $stmt->execute($data);
        }
        catch(PDOException $e) {
            if(DEBUG == 1)
                echo "BaseDb::remove_by_ids: ".$e->getMessage();
            return false;
        }
    }

    /**
     * Set locale time name variable.
     *
     * @param string $locale
     *  The locale indentifier, e.g. de_CH.
     */
    public function set_db_locale($locale) {
        try {
            $stmt = $this->dbh->prepare("SET lc_time_names = :locale");
            $stmt->execute(array(':locale' => $locale));
        }
        catch(Exception $e) {
            if(DEBUG == 1) echo "BaseDb::set_db_locale: ".$e->getMessage();
        }
    }

    /**
     * Get all rows from a table.
     *
     * @param string $table
     *  The name of the db table.
     * @retval array
     *  An array with all row entries or false if no entry was selected.
     */
    public function select_table($table) {
        try {
            $stmt = $this->dbh->prepare("SELECT * FROM $table");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        catch(PDOException $e) {
            if(DEBUG == 1) echo "BaseDb::select_table: ".$e->getMessage();
        }
    }

    /**
     * Get a single row of a db table by foreign key.
     *
     * @param string $table
     *  The name of the db table.
     * @param string $fk
     *  The name of the foreign key.
     * @param int $id
     *  The foreign key of the row to be selected
     * @retval array
     *  An array with all row entries or false if no entry was selected
     */
    public function select_by_fk($table, $fk, $id) {
        try {
            $stmt = $this->dbh->prepare("SELECT * FROM $table WHERE $fk = :fk");
            $stmt->execute(array(':fk' => $id));
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        catch(PDOException $e) {
            if(DEBUG == 1) echo "BaseDb::select_by_fk: ".$e->getMessage();
        }
    }

    /**
     * Get a single row of a db table by foreign key constarints.
     *
     * @param string $table
     *  The name of the db table.
     * @param array $fks
     *  An associative array of where conditions e.g WHERE $key = $value. The
     *  conditions are concatenated with AND.
     * @retval array
     *  An array with all row entries or false if no entry was selected
     */
    public function select_by_fks($table, $fks) {
        try {
            $data = array();
            $where_cond = "";
            $first = true;
            foreach($fks as $key => $value) {
                $data[':' . $key] = $value;
                if($first) $where_cond = " WHERE $key = :$key";
                else $where_cond .= " AND $key = :$key";
                $first = false;
            }
            $stmt = $this->dbh->prepare("SELECT * FROM ".$table.$where_cond);
            $stmt->execute($data);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        catch(PDOException $e) {
            if(DEBUG == 1) echo "BaseDb::select_by_fks: ".$e->getMessage();
        }
    }

    /**
     * Get a single row of a db table by unique id.
     *
     * @param string $table
     *  The name of the db table.
     * @param int $id
     *  The unique id of the row to be selected.
     * @retval array
     *  An array with all row entries or false if no entry was selected.
     */
    public function select_by_uid($table, $id) {
        try {
            $stmt = $this->dbh->prepare("SELECT * FROM $table WHERE id = :id");
            $stmt->execute(array(':id' => $id));
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        catch(PDOException $e) {
            if(DEBUG == 1) echo "BaseDb::select_by_uid: ".$e->getMessage();
        }
    }

    /**
     * Get a single row of a data table by unique id and get all names of
     * foreign keys by joining the linked tables. The naming convention to make
     * this work is as follows:
     *
     *  - primary id keys:  'id'
     *  - foreign keys:     'id_<table_name>'
     *
     * @param string $table
     *  The name of the db table.
     * @param int $id
     *  The unique id of the row to be selected.
     * @retval array
     *  An array with all entries of the row or false if no entry was selected.
     */
    public function select_by_uid_join($table, $id) {
        try {
            $mainTable = $this->select_by_uid($table, $id);
            $tableNb = 0;
            $join = "";
            $sql = "SELECT ";
            if($mainTable) {
                foreach($mainTable as $i => $value) {
                    $sql .= "t0.".$i.", ";
                    if(substr($i, 0, 3) == "id_") {
                        $tableNb++;
                        $arr = explode('_', $i);
                        $tableName = ltrim($i, "id_");
                        $join .= " LEFT JOIN ".rtrim($tableName, "0..9")." t"
                            .$tableNb." ON t0.".$i." = t".$tableNb.".id";
                        $sql .= "t".$tableNb.".name AS name_".$tableName.", ";
                        $sql .= "t".$tableNb.".id AS id_".$tableName.", ";
                    }
                }
                $sql = rtrim($sql, ", ");
                $sql .= " FROM $table t0$join WHERE t0.id = :id";
                $stmt = $this->dbh->prepare($sql);
                $stmt->execute(array(':id' => $id));
                return $stmt->fetch(PDO::FETCH_ASSOC);
            }
        }
        catch(PDOException $e) {
            if( DEBUG == 1 )
                echo "BaseDb::select_by_uid_join: ".$e->getMessage();
        }
    }

    /**
     * Insert values into db table.
     *
     * @param string $table
     *  The name of the db table.
     * @param array $entries
     *  An associative array of db entries e.g. colname => foo
     * @param array $update_entries
     *  An associative array of db entries e.g. colname => foo.
     *  This array indicates which fields to update should the entry already
     *  exist.
     * @retval int
     *  The inserted id if succeded, false otherwise.
     */
    public function insert($table, $entries, $update_entries = array()) {
        try {
            $data = array();
            $columnStr = "";
            $valueStr = "";
            foreach ($entries as $i => $value) {
                $columnStr .= $i.", ";
                $id = ":".$i;
                $valueStr .= $id.", ";
                $data[$id] = $value;
            }
            $columnStr = rtrim($columnStr, ", ");
            $valueStr = rtrim($valueStr, ", ");
            $onDuplicate = "";
            if(count($update_entries) > 0)
            {
                $onDuplicate = "ON DUPLICATE KEY UPDATE ";
                foreach($update_entries as $key => $value)
                    $onDuplicate .= $key . "=:" . $key .",";
                $onDuplicate = rtrim($onDuplicate, ", ");
            }
            $sql = "INSERT INTO ".$table
                ." (".$columnStr.") VALUES(".$valueStr.") ". $onDuplicate;
            $stmt = $this->dbh->prepare($sql);
            $stmt->execute($data);
            $new_id = intval($this->dbh->lastInsertId());
            if($new_id > 0) return $new_id; // might be zero if no id is available
            else return true;

        }
        catch(PDOException $e) {
            if(DEBUG == 1) echo "BaseDb::insert: ".$e->getMessage();
            return false;
        }
    }

    /**
     * Insert multiple rows o values into db table.
     *
     * @param string $table
     *  The name of the db table.
     * @param array $cols
     *  An array of db collumn names.
     * @param array $data
     *  A matrix of values.
     * @retval int
     *  The last inserted id if succeded, false otherwise.
     */
    public function insert_mult($table, $cols, $data) {
        try {
            $db_data = array();
            $columnStr = "(" . implode(',', $cols) . ")";
            $valueStr = implode(',', array_map(
                function($row, $row_idx) use ($cols, &$db_data)
                {
                    return "(" . implode(',', array_map(
                        function($value, $col_idx) use ($cols, &$db_data, $row_idx)
                        {
                            $id = ":".($row_idx * count($cols) + $col_idx);
                            $db_data[$id] = $value;
                            return $id;
                        }, $row, array_keys($row))) . ")";
                }, $data, array_keys($data)));
            $sql = "INSERT INTO ".$table
                .$columnStr." VALUES ".$valueStr;
            $stmt = $this->dbh->prepare($sql);
            $stmt->execute($db_data);
            $new_id = $this->dbh->lastInsertId();
            if($new_id > 0) return $new_id; // might be zero if no id is available
            else return true;
        }
        catch(PDOException $e) {
            if(DEBUG == 1) echo "BaseDb::insert_mult: ".$e->getMessage();
            return false;
        }
    }

    /**
     * Update values in db defined by one or several ids.
     *
     * @param string $table
     *  The name of the db table.
     * @param array $entries
     *  An associative array of db entries e.g. $["colname1"] = "foo".
     * @param array $ids
     *  An associative array of where conditions e.g WHERE $key = $value. The
     *  conditions are concatenated with AND.
     * @retval bool
     *  true if succeded, false otherwise.
     */
    public function update_by_ids($table, $entries, $ids) {
        try {
            $data = array();
            $where_cond = "";
            $first = true;
            foreach($ids as $key => $value) {
                $data[':' . $key] = $value;
                if($first) $where_cond = " WHERE $key = :$key";
                else $where_cond .= " AND $key = :$key";
                $first = false;
            }
            $insertStr = "";
            foreach($entries as $i => $value) {
                $id = ":".$i;
                $insertStr .= $i."=".$id.", ";
                $data[$id] = $value;
            }
            $insertStr = rtrim($insertStr, ", ");
            $sql = "UPDATE ".$table." SET ".$insertStr.$where_cond;
            $stmt = $this->dbh->prepare($sql);
            $success = $stmt->execute($data);
            if ($success !== false) {
                return $stmt->rowCount();
            } else {
                return false;
            }
        }
        catch(PDOException $e) {
            if(DEBUG == 1) echo "BaseDb::update_by_ids: ".$e->getMessage();
            return false;
        }
    }

    /**
     * Get lookups for given type.
     *
     * @param string $lookupType
     *  The type of the lookup
     * @retval array
     *  An array with all row entries for the given lookuptype
     */
    public function get_lookups($lookupType) {
        try {
            $stmt = $this->dbh->prepare("SELECT * FROM lookups WHERE type_code = :code");
            $stmt->execute(array(':code' => $lookupType));
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        catch(PDOException $e) {
            if(DEBUG == 1) echo "BaseDb::select_table: ".$e->getMessage();
        }
    }

    /**
     * Get the id of a lookup value
     *
     * @param string $type
     *  The lookup type
     * @param string $value
     *  The lookup value
     * @retval int
     *  the id of the value
     */
    public function get_lookup_id_by_value($type, $value)
    {
        $key = $this->cache->generate_key($this->cache::CACHE_TYPE_LOOKUPS, $value, [__FUNCTION__, $type]);
        $get_result = $this->cache->get($key);
        if ($get_result !== false) {
            return $get_result;
        } else {
            $val = $this->query_db_first(
                'SELECT id FROM lookups WHERE lookup_value = :value AND type_code = :type_code;',
                array(
                    ':value' => $value,
                    ":type_code" => $type
                )
            );
            $res = $val ? $val["id"] : null;
            $this->cache->set($key, $res);
            return $res;
        }
    }

    /**
     * Get the id of a lookup value
     *
     * @param string $type
     *  The lookup type
     * @param string $code
     *  The lookup code
     * @retval int
     *  the id of the lookup code
     */
    public function get_lookup_id_by_code($type, $code)
    {
        $key = $this->cache->generate_key($this->cache::CACHE_TYPE_LOOKUPS, $code, [__FUNCTION__, $type]);
        $get_result = $this->cache->get($key);
        if ($get_result !== false) {
            return $get_result;
        } else {
            $val = $this->query_db_first(
                'SELECT id FROM lookups WHERE lookup_code = :code AND type_code = :type_code;',
                array(
                    ':code' => $code,
                    ":type_code" => $type
                )
            );
            $res = $val ? $val["id"] : null;
            $this->cache->set($key, $res);
            return $res;
        }
    }

    /**
     * Get the lookup value by id
     *
     * @param int $id
     *  The lookup id
     * @retval string
     *  the lookup value
     */
    public function get_lookup_value_by_id($id)
    {
        $val = $this->query_db_first(
            'SELECT lookup_value FROM lookups WHERE id = :id;',
            array(
                ':id' => $id
            )
        );
        return $val['lookup_value'];
    }

    /**
     * Get the lookup code by id
     *
     * @param int $id
     *  The lookup id
     * @retval string
     *  the lookup code
     */
    public function get_lookup_code_by_id($id)
    {
        $val = $this->query_db_first(
            'SELECT lookup_code FROM lookups WHERE id = :id;',
            array(
                ':id' => $id
            )
        );
        return $val['lookup_code'];
    }

    /**
     * Begin PDO DB transanction
     */
    public function begin_transaction(){
        $this->dbh->beginTransaction();
    }

    /**
     * commit PDO DB transanction
     */
    public function commit(){
        $this->dbh->commit();
    }

    /**
     * rollback PDO DB transanction
     */
    public function rollback()
    {
        $this->dbh->rollback();
        $this->cache->clear_cache(); // on rollback clear cache
    }

    /**
     * Get the callback key from the preferences table
     * @retval string 
     */
    public function get_callback_key(){
        $sql = "SELECT callback_api_key FROM cmsPreferences;";
        return $this->query_db_first($sql)['callback_api_key'];
    }

    /**
     * Fetch the user data from the db.
     *
     * @param int $lid
     *  The id of the language to fetch.
     * @retval array
     *  An array with the following keys:
     *   'id':      The id of the language.
     *   'locale':  
     *   'language':
     *   'csv_separator'
     */
    public function fetch_language($lid)
    {
        $sql = "SELECT * FROM languages WHERE id = :lid";
        $res = $this->query_db_first($sql, array(":lid" => $lid));
        if(!$res) return null;
        return array(
            "lid" => $lid,
            "locale" => $res['locale'],
            "language" => $res['language'],
            "csv_separator" => $res['csv_separator']
        );
    }

    /**
     * Fetch language by locale.
     *
     * @param string $locale
     *  The locale of the language.
     * @retval array
     *  An array with the following keys:
     *   'id':      The id of the language.
     *   'locale':  
     *   'language':
     *   'csv_separator'
     */
    public function fetch_language_by_locale($locale)
    {
        $sql = "SELECT * FROM languages WHERE locale = :locale";
        $res = $this->query_db_first($sql, array(":locale" => $locale));
        if (!$res) {
            // cannot find the locale, lets try to find something from the same language, the first one with the lowest id
            $local_language = explode('-', $locale);
            if (!isset($local_language[0])) {
                return null;
            }
            $locale = '%' . $local_language[0] . '-%';
            $sql = "SELECT * FROM languages WHERE locale LIKE (:locale) ORDER BY id ASC;";
            $res = $this->query_db_first($sql, array(":locale" => $locale));
            if(!$res) return null;
        };
        return array(
            "lid" => $res['id'],
            "locale" => $res['locale'],
            "language" => $res['language'],
            "csv_separator" => $res['csv_separator']
        );
    }


    /**
     * Get cache
     * @return object
     * Return the cache instance
     */
    public function get_cache(){
        return $this->cache;
    }

    /**
     * Check if a view exists in the current database.
     *
     * This function checks the `information_schema.VIEWS` table to determine if a
     * view with the given name exists in the current database.
     *
     * @param string $view_name The name of the view to check for existence.
     * @return bool Returns `true` if the view exists, `false` otherwise.
     */
    public function check_if_view_exists($view_name)
    {
        $sql = "SELECT COUNT(*) AS view_exists
                FROM information_schema.VIEWS
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :view_name;";
        $res = $this->query_db_first(
            $sql,
            array(
                ":view_name" => $view_name
            )
        );
        return $res['view_exists'] > 0;
    }
}
?>
