<?php

namespace App\Service\Core;

use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class Utils
{
/**
     * Normalizes data before sending it in a response using Symfony Serializer
     * This handles Doctrine entities and converts them to arrays
     * 
     * @param mixed $data The data to normalize
     * @return mixed The normalized data
     */
    public static function normalizeWithSymfonySerializer($data)
    {
        if ($data === null) {
            return null;
        }
        
        // For simple scalar types, return as is
        if (is_scalar($data)) {
            return $data;
        }
        
        // Create a serializer instance with appropriate normalizers and encoders
        $normalizer = new ObjectNormalizer(null, null, null, null, null, null, [
            ObjectNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object) {
                // For circular references, just return the ID if available
                return method_exists($object, 'getId') ? ['id' => $object->getId()] : null;
            },
            ObjectNormalizer::CIRCULAR_REFERENCE_LIMIT => 1,
            // Ignore Doctrine proxy properties
            ObjectNormalizer::IGNORED_ATTRIBUTES => ['__initializer__', '__cloner__', '__isInitialized__']
        ]);
        
        $serializer = new Serializer([$normalizer], [new JsonEncoder()]);
        
        // First serialize to JSON, then decode back to array
        $json = $serializer->serialize($data, 'json');
        return json_decode($json, true);
    }
}