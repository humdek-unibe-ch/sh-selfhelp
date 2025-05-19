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
 * particularly for debugging database operations.
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
                'api' => BASE_PATH . '/admin/clockwork?request=',
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
        // Load only the necessary classes instead of all PHP files
        ob_start();
        $this->loadRequiredClasses();

        $tracked_classes_with_functions = [
            "BaseView" => ["output_content", "output_content_mobile"],
        ];

        $clockworkService = $this;
        // Avoid printing all declared classes which is expensive
        // print_r(get_declared_classes());

        // Loop over each tracked base class and its methods.
        foreach ($tracked_classes_with_functions as $baseClass => $functions) {
            // Iterate over all declared classes.
            foreach (get_declared_classes() as $class) {
                // Check if the class is the base class itself or a subclass of it.
                if ($class === $baseClass || is_subclass_of($class, $baseClass)) {
                    // Loop over each function that should be wrapped.                    
                    foreach ($functions as $function) {
                        try {
                            // if (method_exists($class, $function)) {
                            $method = new ReflectionMethod($class, $function);
                            // Apply hook only if the method is declared in the class (not inherited)
                            if ($method->getDeclaringClass()->getName() === $class) {
                                uopz_set_return(
                                    $class,
                                    $function,
                                    function (...$args) use ($clockworkService, $function) {
                                        // Log before execution.
                                        $class = get_class($this);
                                        $clockworkService->startEvent("[$class][$function]");

                                        $originalReturn = call_user_func_array([$this, $function], $args);

                                        // Log after execution.
                                        $clockworkService->endEvent("[$class][$function]");

                                        // Return the original value.
                                        return $originalReturn;
                                    },
                                    true // Execute original method before calling this callback.
                                );
                            }
                            // }
                        } catch (ReflectionException $e) {
                            // The method does not exist on this class.
                            // You can optionally log or handle this case.
                        }
                    }
                }
            }
        }
        ob_end_clean();
    }

    /**
     * Load only the required classes for Clockwork tracking
     * This is much faster than scanning and requiring all PHP files
     */
    private function loadRequiredClasses()
    {
        // Define the base classes we need to track
        $baseClasses = [
            'BaseView' => __DIR__ . '/../component/BaseView.php',
            'StyleModel' => __DIR__ . '/../component/style/StyleModel.php',
            // Add other base classes as needed
        ];

        // First load the base classes
        foreach ($baseClasses as $className => $filePath) {
            if (file_exists($filePath) && !class_exists($className, false)) {
                require_once $filePath;
            }
        }

        // Define paths to scan for subclasses
        $scanPaths = [
            __DIR__ . '/../component',
            __DIR__ . '/../component/style',
            __DIR__ . '/../plugins/*/server/component',
            __DIR__ . '/../plugins/*/server/component/style',
            // Add other important directories as needed
        ];

        // Use a more efficient approach to find and load relevant class files
        foreach ($scanPaths as $path) {
            $this->loadClassesFromPath($path);
        }
    }

    /**
     * Efficiently load classes from a specific path
     * 
     * @param string $path Directory path to scan
     */
    private function loadClassesFromPath($path)
    {
        // Use glob() which is faster than scandir() + manual filtering
        $files = glob($path . '/*');

        foreach ($files as $file) {
            // Skip directories named "ext"
            if (basename($file) === 'ext') {
                continue;
            }

            if (is_dir($file)) {
                // Only recurse into specific directories to avoid unnecessary scanning
                $dirName = basename($file);
                if (!in_array($dirName, ['ext', 'vendor', 'node_modules'])) {
                    $this->loadClassesFromPath($file);
                }
            } elseif (is_file($file) && pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                $fileName = basename($file);
                $className = pathinfo($fileName, PATHINFO_FILENAME);

                // Only load files that match our pattern and haven't been loaded yet
                if (
                    preg_match('/(View|Model|Component|Controller)$/', $className) &&
                    !class_exists($className, false)
                ) {
                    require_once $file;
                }
            }
        }
    }

    /**
     * Original slow implementation - kept for reference
     * @deprecated Use loadRequiredClasses() instead
     */
    private function requireAllPhpFiles($dir)
    {
        foreach (scandir($dir) as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            $fullPath = $dir . DIRECTORY_SEPARATOR . $file;
            // Normalize path for consistent directory separator.
            $normalizedPath = str_replace('\\', '/', $fullPath);
            // Skip any paths that contain the folder "/ext/"
            if (strpos($normalizedPath, '/ext/') !== false) {
                continue;
            }
            if (is_dir($fullPath)) {
                $this->requireAllPhpFiles($fullPath);
            } elseif (pathinfo($fullPath, PATHINFO_EXTENSION) === 'php') {
                $filenameWithoutExtension = pathinfo($file, PATHINFO_FILENAME);
                // Only require files whose names end with one of these suffixes.
                if (preg_match('/(View|Model|Component|Controller)$/', $filenameWithoutExtension)) {
                    require_once $fullPath;
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
