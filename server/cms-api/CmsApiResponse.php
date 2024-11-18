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
    public function toArray(): array;

    /**
     * Sends the response as a JSON HTTP response
     */
    public function send(): void;
}

/**
 * Handles API response formatting and sending
 * 
 * This class provides functionality to create and send standardized API responses
 * with status codes, messages, optional error information, and response data.
 */
class CmsApiResponse implements CmsApiResponseInterface {
    /**
     * Maps HTTP status codes to their corresponding messages
     * 
     * @param int $code The HTTP status code
     * @return string The corresponding status message
     */
    private static function getStatusMessage(int $code): string {
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
        $this->message = self::getStatusMessage($status);
        $this->data = $data;
        $this->error = $error;
    }

    /**
     * Converts the response to an array format
     * 
     * @return array Associative array containing status, message, error, and data
     */
    public function toArray(): array {
        return [
            'status' => $this->status,
            'message' => $this->message,
            'error' => $this->error,
            'data' => $this->data
        ];
    }

    /**
     * Sends the response as JSON
     * 
     * Sets appropriate headers, HTTP status code, and outputs JSON-encoded response data.
     * Terminates script execution after sending.
     */
    public function send(): void {
        header(header: 'Content-Type: application/json');
        http_response_code(response_code: $this->status);
        echo json_encode(value: $this->toArray());
        exit;
    }
} 