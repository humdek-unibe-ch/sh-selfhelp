<?php

namespace App\Util;

/**
 * Utility class for entity-related operations
 */
class EntityUtil
{
    /**
     * Convert a Doctrine entity or any object to an array representation
     * 
     * @param object $entity The entity or object to convert
     * @return array The array representation of the entity
     */
    public static function convertEntityToArray(object $entity): array
    {
        // If the entity has a toArray method, use it
        if (method_exists($entity, 'toArray')) {
            return $entity->toArray();
        }
        
        // Start with public properties
        $result = get_object_vars($entity);
        
        // Try to get all properties using reflection
        $reflection = new \ReflectionClass($entity);
        $properties = $reflection->getProperties();
        
        foreach ($properties as $property) {
            $property->setAccessible(true);
            $name = $property->getName();
            
            // Skip already processed properties
            if (array_key_exists($name, $result)) {
                continue;
            }
            
            // Try to get the value
            try {
                $value = $property->getValue($entity);
                
                // Handle nested objects
                if (is_object($value)) {
                    // For DateTime objects, convert to string
                    if ($value instanceof \DateTimeInterface) {
                        $result[$name] = $value->format('Y-m-d H:i:s');
                    } 
                    // For Doctrine collections, convert to array of IDs
                    // WARNING: This can cause N+1 queries if collection is not initialized
                    elseif ($value instanceof \Doctrine\Common\Collections\Collection) {
                        try {
                            // Try to access collection safely - if it's a PersistentCollection, check if initialized
                            if (method_exists($value, 'isInitialized') && !$value->isInitialized()) {
                                // For uninitialized collections, just indicate it's a collection
                                $result[$name] = 'Collection[lazy]';
                            } else {
                                // Collection is initialized or not a PersistentCollection
                                $result[$name] = array_map(function($item) {
                                    return method_exists($item, 'getId') ? $item->getId() : null;
                                }, $value->toArray());
                            }
                        } catch (\Throwable $e) {
                            // If anything fails, just indicate it's a collection
                            $result[$name] = 'Collection[error]';
                        }
                    }
                    // For other objects, try to get ID or handle lazy loading
                    else {
                        if (method_exists($value, 'getId')) {
                            try {
                                // Check if it's a Doctrine proxy to avoid lazy loading
                                if (is_object($value) && str_contains(get_class($value), 'Proxy')) {
                                    // For proxies, try to get ID without initializing
                                    $result[$name] = $value->getId();
                                } else {
                                    $result[$name] = $value->getId();
                                }
                            } catch (\Throwable $e) {
                                // Skip if lazy loading fails
                                $result[$name] = null;
                            }
                        } else {
                            // Check if it's a lazy loading proxy
                            if ($value instanceof \Symfony\Component\VarExporter\Internal\LazyObjectState || 
                                (is_object($value) && str_contains(get_class($value), 'Proxy'))) {
                                // Skip lazy-loaded objects that can't be converted
                                $result[$name] = 'Proxy[' . get_class($value) . ']';
                            } else {
                                try {
                                    $result[$name] = (string)$value;
                                } catch (\Throwable $e) {
                                    // If string conversion fails, set to null
                                    $result[$name] = null;
                                }
                            }
                        }
                    }
                } 
                // For arrays, keep as is
                elseif (is_array($value)) {
                    $result[$name] = $value;
                }
                // For scalar values, keep as is
                elseif ($value === null || is_scalar($value)) {
                    $result[$name] = $value;
                }
            } catch (\Throwable $e) {
                // Skip properties that can't be accessed
                continue;
            }
        }
        
        return $result;
    }
}
