<?php

class PerformanceLogger {
    private static array $currentTimers = [];
    private static array $styles = [];
    
    private static function getKey(string $category, string $name): string {
        return $category . '_' . $name;
    }

    /**
     * Start timing an operation
     */
    public static function startTimer(string $category, string $name, array $context = []): void {
        $key = self::getKey($category, $name);
        self::$currentTimers[$key] = [
            'start_time' => microtime(true),
            'name' => $name,
            'section_id' => $context['section_id'] ?? null,
            'has_condition' => $context['has_condition'] ?? false,
            'has_children' => $context['has_children'] ?? false
        ];
    }

    /**
     * End timing an operation and record metrics
     */
    public static function endTimer(string $category, string $name, array $context = []): void {
        $key = self::getKey($category, $name);
        if (isset(self::$currentTimers[$key])) {
            $timer = self::$currentTimers[$key];
            $executionTime = microtime(true) - $timer['start_time'];
            
            if ($category === 'style') {
                self::$styles[] = [
                    'styleName' => $timer['name'],
                    'id' => $timer['section_id'],
                    'executionTime' => round($executionTime * 1000, 2), // Convert to milliseconds
                    'hasCondition' => $context['has_condition'] ?? false,
                    'hasChildren' => $context['has_children'] ?? false
                ];
            }
            
            unset(self::$currentTimers[$key]);
        }
    }

    /**
     * Get all collected style metrics
     */
    public static function getAllStyleMetrics(): array {
        return ['styles' => self::$styles];
    }

    /**
     * Clear all metrics (useful for testing or reset)
     */
    public static function reset(): void {
        self::$currentTimers = [];
        self::$styles = [];
    }
}