<?php

/**
 * Interface for CMS API responses
 * 
 * Defines the contract for API response objects that can be converted to arrays
 * and sent as HTTP responses.
 */
interface CmsApiResponseInterface {
    /**
     * Converts the response object to an array
     * 
     * @return array The response data as an associative array
     */
    public function to_array(): array;

    /**
     * Sends the response as a JSON HTTP response
     */
    public function send(): void;

    public function set_logged_in(bool $logged_in) : void; 

    
    /**
     * Sets the HTTP status code for the response
     * 
     * @param int $status_code The HTTP status code (e.g. 200, 404, 500)
     * @return static The response object for fluent API
     */
    public function set_status(int $status_code): static;
    
    /**
     * Sets the message for the response
     * 
     * @param string $message A short description of the response
     * @return static The response object for fluent API
     */
    public function set_message(string $message): static;

}

/**
 * Handles API response formatting and sending
 * 
 * This class provides functionality to create and send standardized API responses
 * with status codes, messages, optional error information, and response data.
 */
class CmsApiResponse implements CmsApiResponseInterface {
    private array $after_send_callbacks = [];
    
    /**
     * Registers a callback to be executed after sending the response
     * 
     * @param callable $callback Function to execute after sending response
     */
    public function add_after_send_callback(callable $callback): void {
        $this->after_send_callbacks[] = $callback;
    }

    /**
     * Maps HTTP status codes to their corresponding messages
     * 
     * @param int $code The HTTP status code
     * @return string The corresponding status message
     */
    private static function get_status_message(int $code): string {
        $messages = [
            200 => 'OK',
            201 => 'Created',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            500 => 'Internal Server Error'
        ];
        return $messages[$code] ?? 'Unknown status code';
    }

    /** @var int HTTP status code */
    private int $status;

    /** @var string Status message corresponding to the status code */
    private string $message;

    /** @var string|null Optional error message */
    private ?string $error;

    /** @var mixed Response data */
    private $data;

    /** @var bool Indicates if the user is logged in */
    private bool $logged_in = false;

    /** @var string API version */
    private string $api_version = 'v1';

    /** @var array Meta information */
    private array $meta;

    /**
     * Creates a new API response
     * 
     * @param int $status HTTP status code (defaults to 200)
     * @param mixed $data Response data (optional)
     * @param string|null $error Error message (optional)
     */
    public function __construct(
        int $status = 200,
        $data = null,
        ?string $error = null
    ) {
        $this->status = $status;
        $this->message = self::get_status_message($status);
        $this->data = $data;
        $this->error = $error;
        $this->meta = [
            'version' => $this->api_version,
            'timestamp' => date('c')  // ISO 8601 date format
        ];
    }

    /**
     * Converts the response to an array format
     * 
     * @return array Associative array containing status, message, error, data, and meta information
     */
    public function to_array(): array {
        return [
            'status' => $this->status,
            'message' => $this->message,
            'error' => $this->error,
            'logged_in' => $this->logged_in,            
            'meta' => $this->meta,
            'data' => $this->data
        ];
    }

    /**
     * Sends the response as JSON
     * 
     * Sets appropriate headers, HTTP status code, outputs JSON-encoded response data,
     * executes registered callbacks, and terminates script execution.
     */
    public function send(): void {
        header('Content-Type: application/json');
        http_response_code($this->status);
        echo json_encode($this->to_array());
        
        // Execute all registered callbacks before exit
        foreach ($this->after_send_callbacks as $callback) {
            $callback();
        }
        
        exit;
    }

    /**
     * Sets the logged_in status for the response
     * 
     * @param bool $logged_in The logged_in status to set
     */
    public function set_logged_in(bool $logged_in): void
    {
        $this->logged_in = $logged_in;
    }

    
    /**
     * Gets the logged_in status
     * 
     * @return bool The logged_in status
     */
    public function is_logged_in(): bool
    {
        return $this->logged_in;
    }


    /**
     * Sets the HTTP status code
     * 
     * @param int $status_code The HTTP status code (e.g. 200, 404, 500)
     * @return static The response object for fluent API
     */
    public function set_status(int $status_code): static {
        $this->status = $status_code;
        $this->message = self::get_status_message($status_code);
        return $this;
    }

    /**
     * Sets the message for the response
     * 
     * @param string $message A short description of the response
     * @return static The response object for fluent API
     */
    public function set_message(string $message): static {
        $this->message = $message;
        return $this;
    }

    /**
     * Sets the response data
     * 
     * @param mixed $data The response data
     * @return static The response object for fluent API
     */
    public function set_data($data): static {
        $this->data = $data;
        return $this;
    }

    /**
     * Sets the error message
     * 
     * @param string|null $error The error message (optional)
     * @return static The response object for fluent API
     */
    public function set_error(?string $error): static {
        $this->error = $error;
        return $this;
    }

}       