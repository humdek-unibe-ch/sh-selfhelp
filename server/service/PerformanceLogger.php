<?php

class PerformanceLogger {
    private static array $currentTimers = [];
    private static array $styles = [];
    private static array $groupedStyles = [];
    
    private static function getKey(string $category, string $name, array $context = []): string {
        $key = $category . '_' . $name;
        if (isset($context['section_id'])) {
            $key .= '_' . $context['section_id'];
        }
        return $key;
    }

    /**
     * Start timing an operation
     */
    public static function startTimer(string $category, string $name, array $context = []): void {
        $key = self::getKey($category, $name, $context);
        self::$currentTimers[$key] = [
            'start_time' => microtime(true),
            'name' => $name,
            'section_id' => $context['section_id'] ?? null
        ];
    }

    /**
     * End timing an operation and record metrics
     */
    public static function endTimer(string $category, string $name, array $context = []): void {
        $key = self::getKey($category, $name, $context);
        if (isset(self::$currentTimers[$key])) {
            $timer = self::$currentTimers[$key];
            $executionTime = round((microtime(true) - $timer['start_time']) * 1000, 2);
            
            if ($category === 'style-model' || $category === 'style-view') {
                $styleKey = $timer['name'] . '_' . $timer['section_id'];
                
                if (!isset(self::$groupedStyles[$styleKey])) {
                    self::$groupedStyles[$styleKey] = [
                        'name' => $timer['name'],
                        'id' => $timer['section_id'],
                        'executionTime-view' => 0,
                        'executionTime-model' => 0,
                        'hasCondition' => false,
                        'hasChildren' => false
                    ];
                }
                
                if ($category === 'style-view') {
                    self::$groupedStyles[$styleKey]['executionTime-view'] = $executionTime;
                } else {
                    self::$groupedStyles[$styleKey]['executionTime-model'] = $executionTime;
                    self::$groupedStyles[$styleKey]['hasCondition'] = $context['has_condition'] ?? false;
                    self::$groupedStyles[$styleKey]['hasChildren'] = $context['has_children'] ?? false;
                }
            }
            
            unset(self::$currentTimers[$key]);
        }
    }

    /**
     * Get all collected style metrics
     */
    public static function getAllStyleMetrics(): array {
        return ['styles' => array_values(self::$groupedStyles)];
    }

    /**
     * Clear all metrics (useful for testing or reset)
     */
    public static function reset(): void {
        self::$currentTimers = [];
        self::$styles = [];
        self::$groupedStyles = [];
    }
}