<?php

/**
 * Clockwork PHP Debugging Integration
 * 
 * This file provides a wrapper for the Clockwork debugging tool in vanilla PHP.
 * It allows for performance monitoring, logging, and debugging of PHP applications.
 */

// Load Clockwork via Composer autoloader
require_once __DIR__ . '/ext/clockwork/vendor/autoload.php';

use Clockwork\Support\Vanilla\Clockwork;

// Import the correct Clockwork classes from the vanilla support package
// use Clockwork\Support\Vanilla\Clockwork;

/**
 * ClockworkService - A service class for Clockwork debugging integration
 * 
 * This class provides a simplified interface for using Clockwork in the SelfHelp application,
 * particularly for debugging the Mobisense module's SSH and database operations.
 */
class ClockworkService
{
    /** @var \Clockwork\Support\Vanilla\Clockwork The Clockwork instance */
    private $clockwork;


    /**
     * Private constructor to enforce singleton pattern
     */
    public function __construct()
    {
        try {
            // Initialize Clockwork using the Vanilla support class
            if (!$this->isEnabled()) {
                return;
            }
            ob_start();
            $this->clockwork = Clockwork::init([
                'storage_files_path' => __DIR__ . '/../../data/clockwork',
                'api' => BASE_PATH . '/ClockworkApi.php?request=',
                'register_helpers' => true,
                'enable' => $this->isEnabled(),
                'collect_data_always' => true,
                'database' => true,
                'features' => [
                    'database' => [
                        'detect_duplicate_queries' => true,
                        'log_duplicate_queries' => true,
                    ],
                    'performance' => [
                        "client_metrics" => true
                    ]
                ],
            ]);
            $this->wrap_common_functions();
        } catch (\Exception $e) {
            error_log('Clockwork initialization failed: ' . $e->getMessage());
        }
    }

    private function wrap_common_functions()
    {
        $clockworkService = $this;
        foreach (get_declared_classes() as $class) {
            if (is_subclass_of($class, 'BaseView')) {
                try {
                    $method = new ReflectionMethod($class, 'output_content');
                    // Only apply hook if the method is declared in the class itself.
                    if ($method->getDeclaringClass()->getName() === $class) {
                        uopz_set_return($class, 'output_content', function (...$args) use ($clockworkService) {
                            // Log before. (Note: the original function has already been executed.)
                            $clockworkService->startEvent('[BaseView][output_content]');

                            // The callback receives all arguments passed to output_content, 
                            // with the original return value appended as the last parameter.
                            $originalReturn = array_pop($args);

                            // Log after.
                            $clockworkService->endEvent('[BaseView][output_content]');

                            // Return the original value.
                            return $originalReturn;
                        }, true);
                    }
                } catch (ReflectionException $e) {
                    // The method 'output_content' is not declared in this class.
                }
            }
        }
    }

    public function isEnabled(): bool
    {
        return defined('CLOCKWORK_PROFILE') && CLOCKWORK_PROFILE == 1;
    }


    /**
     * Get the Clockwork instance
     * 
     * @return \Clockwork\Support\Vanilla\Clockwork|null
     */
    public function getClockwork()
    {
        return $this->clockwork;
    }

    /**
     * Log an informational message
     * 
     * @param string $message Message to log
     * @param array $context Additional context data
     * @return void
     */
    public function info($message, array $context = [])
    {
        if (!$this->isEnabled()) {
            return;
        }

        clock()->info($message, $context);
    }

    /**
     * Log a warning message
     * 
     * @param string $message Warning message
     * @param array $context Additional context data
     * @return void
     */
    public function warning($message, array $context = [])
    {
        if (!$this->isEnabled()) {
            return;
        }

        clock()->warning($message, $context);
    }

    /**
     * Log an error message
     * 
     * @param string $message Error message
     * @param array $context Additional context data
     * @return void
     */
    public function error($message, array $context = [])
    {
        if (!$this->isEnabled()) {
            return;
        }

        clock()->error($message, $context);
    }

    /**
     * Start a timing event
     * 
     * @param string $name Event name
     * @return void
     */
    public function startEvent($name)
    {
        if (!$this->isEnabled()) {
            return;
        }

        clock()->event($name)->start(microtime(true));
    }

    /**
     * End a timing event
     * 
     * @param string $name Event name
     * @return void
     */
    public function endEvent($name)
    {
        if (!$this->isEnabled()) {
            return;
        }

        clock()->event($name)->end(microtime(true));
    }

    /**
     * Add custom data to the request
     * 
     * @param string $name Data name
     * @param mixed $data Data value
     * @return void
     */
    public function addData($name, $data)
    {
        if (!$this->isEnabled()) {
            return;
        }

        clock()->addData($name, $data);
    }

    /**
     * Track a database query
     * 
     * @param string $query SQL query
     * @param array $bindings Query bindings
     * @param float $duration Query duration in milliseconds
     * @param bool $success Whether the query was successful
     * @return void
     */
    public function addDatabaseQuery($query, array $bindings = [], $duration = 0, $success = true)
    {
        if (!$this->isEnabled()) {
            return;
        }

        clock()->addDatabaseQuery($query, $bindings, $duration, $success);
    }

    /**
     * Process and store the current request data
     * 
     * This method finalizes the current request, resolves all data sources,
     * and stores the request data for later retrieval by the Clockwork app.
     * 
     * @return void
     */
    public function requestProcessed()
    {
        if (!$this->isEnabled()) {
            return;
        }

        try {
            $this->clockwork->requestProcessed();
            ob_end_flush();
        } catch (\Exception $e) {
            error_log('Clockwork request processing failed: ' . $e->getMessage());
        }
    }
}
