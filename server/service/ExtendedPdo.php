<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
/**
 * This class extends native PDO one but allow nested transactions
 * by using the SQL statements `SAVEPOINT', 'RELEASE SAVEPOINT' AND 'ROLLBACK SAVEPOINT'
 */

class ExtendedPdo extends PDO
{

    /**
     * @var array Database drivers that support SAVEPOINT * statements.
     */
    protected static $_supportedDrivers = array("pgsql", "mysql");

    /**
     * @var int the current transaction depth
     */
    protected $_transactionDepth = 0;

    protected $clockwork;


    /**
     * Test if database driver support savepoints
     *
     * @return bool
     */
    protected function hasSavepoint()
    {
        return in_array(
            $this->getAttribute(PDO::ATTR_DRIVER_NAME),
            self::$_supportedDrivers
        );
    }

    /**
     * Start transaction
     *
     * @return bool;
     */
    #[\ReturnTypeWillChange]
    public function beginTransaction()
    {
        if ($this->_transactionDepth == 0 || !$this->hasSavepoint()) {
            $result = parent::beginTransaction();
        } else {
            $this->exec("SAVEPOINT LEVEL{$this->_transactionDepth}");
            $result = true;
        }

        $this->_transactionDepth++;
        return $result;
    }

    /**
     * Commit current transaction
     *
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function commit()
    {
        $this->_transactionDepth--;

        if ($this->_transactionDepth == 0 || !$this->hasSavepoint()) {
            $result = parent::commit();
        } else {
            $this->exec("RELEASE SAVEPOINT LEVEL{$this->_transactionDepth}");
            $result = true;
        }

        return $result;
    }

    /**
     * Rollback current transaction,
     *
     * @throws PDOException if there is no transaction started
     * @return bool Returns `true` on success or `false` on failure.
     */
    #[\ReturnTypeWillChange]
    public function rollBack()
    {
        if ($this->_transactionDepth == 0) {
            throw new PDOException('Rollback error: There is no transaction started');
        }

        $this->_transactionDepth--;

        if ($this->_transactionDepth == 0 || !$this->hasSavepoint()) {
            $result = parent::rollBack();
        } else {
            $this->exec("ROLLBACK TO SAVEPOINT LEVEL{$this->_transactionDepth}");
            $result = true;
        }

        return $result;
    }

    /**
     * Set Clockwork instance for query timing
     * 
     * @param mixed $clockwork Clockwork instance
     * @return void
     */
    public function set_clockwork($clockwork)
    {
        $this->clockwork = $clockwork;
    }

    /**
     * Prepare a statement for execution
     * 
     * @param string $query The SQL query to prepare
     * @param array $options Driver-specific options
     * @return PDOStatement|false
     */
    #[\ReturnTypeWillChange]
    public function prepare($query, $options = [])
    {
        $stmt = parent::prepare($query, $options);
        
        if ($stmt && $this->clockwork) {
            return new ClockworkPDOStatement($stmt, $query, $this->clockwork);
        }
        
        return $stmt;
    }
}
?>

<?php

class ClockworkPDOStatement
{
    private PDOStatement $stmt;
    private string $query;
    private $clockwork;
    private array $params = [];

    public function __construct(PDOStatement $stmt, string $query, $clockwork = null)
    {
        $this->stmt = $stmt;
        $this->query = $query;
        $this->clockwork = $clockwork;
    }

    public function execute(?array $params = null): bool
    {
        $this->params = $params ?? [];

        $start = microtime(true);
        $result = $this->stmt->execute($params);
        $duration = (microtime(true) - $start) * 1000;    
        if ($this->clockwork) {
            $this->clockwork->addDatabaseQuery(
                $this->query,
                $this->params,
                $duration,
                $result
            );
        }

        return $result;
    }

    #[\ReturnTypeWillChange]
    public function fetchAll($fetch_style = PDO::FETCH_ASSOC)
    {
        return $this->stmt->fetchAll($fetch_style);
    }

    public function __call($name, $arguments)
    {
        return $this->stmt->$name(...$arguments);
    }

    public function bindParam($key, &$value, $data_type = PDO::PARAM_STR, $length = null, $driver_options = null): bool
    {
        $this->params[$key] = $value;
        return $this->stmt->bindParam($key, $value, $data_type, $length, $driver_options);
    }

    public function bindValue($key, $value, $data_type = PDO::PARAM_STR): bool
    {
        $this->params[$key] = $value;
        return $this->stmt->bindValue($key, $value, $data_type);
    }
}
