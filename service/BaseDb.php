<?php
/**
 * Class to handle the global communication with the DB
 *
 * @author moiri
 */
class BaseDb {
    private $dbh = null;

    /**
     * Open connection to mysql database
     *
     * @param string $server:   address of server
     * @param string $dbname:   name of database
     * @param string $username: username
     * @param string $password: password
     * @param string $names:    charset (optional, default: utf8)
     */
    public function __construct($server, $dbname, $username, $password, $names="utf8") {
        try {
            $this->dbh = new PDO(
                "mysql:host=$server;dbname=$dbname;charset=$names",
                $username, $password, array(PDO::ATTR_PERSISTENT => true)
            );
            $this->dbh->setAttribute(PDO::ATTR_ERRMODE,
                PDO::ERRMODE_EXCEPTION);
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
     * Exectute an arbitrary query on the db.
     *
     * @param string $sql
     *  The query to be executed.
     * @param array $arguments
     *  An associative array with value key pairs where the keys are variable
     *  identifiers used in the query (e.g ':id') which will be replaced with
     *  the associated value on query execution.
     *
     * @retval array
     *  An array with all row entries or false if no entry was selected.
     */
    public function query_db($sql, $arguments)
    {
        try {
            $stmt = $this->dbh->prepare($sql);
            $stmt->execute($arguments);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        catch(PDOException $e)
        {
            if(DEBUG == 1) echo "BaseDb::query_db: ".$e->getMessage();
            return false;
        }
    }

    /**
     * Exectute an arbitrary query on the db and return the first matching row.
     *
     * @param string $sql
     *  The query to be executed.
     * @param array $arguments
     *  An associative array with value key pairs where the keys are variable
     *  identifiers used in the query (e.g ':id') which will be replaced with
     *  the associated value on query execution.
     *
     * @retval array
     *  All row entries or false if no entry was selected.
     */
    public function query_db_first($sql, $arguments)
    {
        try {
            $stmt = $this->dbh->prepare($sql);
            $stmt->execute($arguments);
            return $stmt->fetch(PDO::FETCH_ASSOC);
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
     */
    public function remove_by_fk($table, $fk, $id) {
        try {
            $sql = "DELETE FROM $table WHERE $fk = :fk";
            $stmt = $this->dbh->prepare($sql);
            $stmt->execute(array(':fk' => $id));
        }
        catch(PDOException $e) {
            if(DEBUG == 1)
                echo "BaseDb::remove_by_fk: ".$e->getMessage();
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
     *  An associative array of db entries e.g. $["colname1"] = "foo"
     * @retval int
     *  The inserted id if succeded, false otherwise.
     */
    public function insert($table, $entries) {
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
            $sql = "INSERT INTO ".$table
                ." (".$columnStr.") VALUES(".$valueStr.")";
            $stmt = $this->dbh->prepare($sql);
            $stmt->execute($data);
            $new_id = $this->dbh->lastInsertId();
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
     * @param array $entries
     *  A matrix of values.
     * @retval int
     *  The last inserted id if succeded, false otherwise.
     */
    public function insert_mult($table, $cols, $entries) {
        try {
            $data = array();
            $columnStr = "(" . implode(',', $cols) . ")";
            $valueStr = implode(',', array_map(function($entry) {
                      return "(" . implode(',', $entry) . ")";
                }, $entries));
            $sql = "INSERT INTO ".$table
                .$columnStr." VALUES ".$valueStr;
            $stmt = $this->dbh->prepare($sql);
            $stmt->execute($data);
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
            $stmt->execute($data);
            return true;
        }
        catch(PDOException $e) {
            if(DEBUG == 1) echo "BaseDb::update_by_ids: ".$e->getMessage();
            return false;
        }
    }
}
?>
