<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php

/**
 * A class that handles transaction records
 */
class Cache
{

    /* Constants ************************************************/

    /* CACHE TYPES */
    const CACHE_TYPE_PAGES = 'CACHE_PAGES';
    const CACHE_TYPE_SECTIONS = 'CACHE_SECTIONS';
    const CACHE_TYPE_FIELDS = 'CACHE_FIELDS';
    const CACHE_TYPE_STYLES = 'CACHE_STYLES';
    const CACHE_TYPE_HOOKS = 'HOOKS';
    const CACHE_TYPE_USER_INPUT = 'USER_INPUT';
    const CACHE_TYPE_CONDITION = 'CONDITION';

    const CACHE_ALL = 'ALL'; // it is used when we want all IDs and not a single record

    /**
     * Creating a Cache Instance.
     *
     */
    public function __construct()
    {
    }

    /* Public Methods *********************************************************/

    /**
     * Clear the cache, if no parameter is given it will clear all the cache. If parameters are given it will clear the cache based on their values
     * @param string $type = null
     * The type od the stored data - the types are defined as constants in the Cache class
     * @param int $id = null
     * the id of the object
     */
    public function clear_cache($type = null, $id = null)
    {
        if (!$type) {
            apcu_clear_cache();
        } else {
            $filter = PROJECT_NAME . '-' . $type;
            if ($id) {
                $filter = $filter . '-' . $id;
            }

            $iterator = new APCUIterator('#^' . $filter . '#', APC_ITER_KEY);
            foreach ($iterator as $entry_name) {
                apcu_delete($entry_name);
            }
        }
    }

    /**
     * Generate key for storing the results in the cache
     * The key has a fixed structure --> type-id-hash
     * @param string $type
     * The type od the stored data - the types are defined as constants in the Cache class
     * @param string $id
     * the id of the object
     * @param array $extra_params
     * If there are some extra params they are converted to a hash
     * @return string
     * the generated key
     */
    public function generate_key($type, $id, $extra_params =  array())
    {
        $res = PROJECT_NAME . '-' . $type . '-' . $id;
        if (count($extra_params) > 0) {
            // $hashed_params = md5(implode('-', $extra_params));
            $hashed_params = implode('-', $extra_params);
            $res = $res . '-' . $hashed_params;
        }
        return  $res;
    }

    /**
     * @param string|string[] $key
     * The key used to store the value (with apcu_store()). If an array is passed then each element is fetched and returned.
     * @return mixed|false
     * The stored variable or array of variables on success; FALSE on failure.
     */
    public function get($key)
    {
        return apcu_fetch($key);
    }

    /**
     * @param string|string[] $key
     * String: Store the variable using this name. Keys are cache-unique, so storing a second value with the same key will overwrite the original value. Array: Names in key, variables in value.
     * @param mixed $var
     * The variable to store
     * @param int $ttl
     * [optional] Time To Live; store var in the cache for ttl seconds. After the ttl has passed, the stored variable will be expunged from the cache (on the next request). If no ttl is supplied (or if the ttl is 0), the value will persist until it is removed from the cache manually, or otherwise fails to exist in the cache (clear, restart, etc.).
     * @return bool|array
     * Returns TRUE on success or FALSE on failure | array with error keys.
     */
    public function set($key, $value, $ttl = 0)
    {
        return apcu_store($key, $value, $ttl);
    }
}
?>
