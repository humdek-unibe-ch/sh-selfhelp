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
            $result = true; // Assuming SAVEPOINT execution is successful
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
            $result = true; // Assuming RELEASE SAVEPOINT execution is successful
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
            $result = true; // Assuming ROLLBACK TO SAVEPOINT execution is successful
        }

        return $result;
    }
}
?>
