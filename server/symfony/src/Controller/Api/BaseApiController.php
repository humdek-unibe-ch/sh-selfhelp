<?php

namespace App\Controller\Api;

use App\Exception\ServiceException;
use App\Service\Core\ApiResponseFormatter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Base API Controller
 * 
 * Provides common functionality for all API controllers
 */
abstract class BaseApiController extends AbstractController
{
    protected ApiResponseFormatter $responseFormatter;

    /**
     * Execute a service method and handle exceptions
     * 
     * @param callable $serviceMethod The service method to execute
     * @param array $additionalData Additional data to include in the response
     * @return JsonResponse The formatted response
     */
    protected function executeServiceMethod(callable $serviceMethod, array $additionalData = []): JsonResponse
    {
        try {
            $result = $serviceMethod();
            $data = $additionalData;
            if ($result !== null) {
                $data = array_merge($data, ['result' => $result]);
            }
            return $this->responseFormatter->formatSuccess($data);
        } catch (ServiceException $e) {
            return $this->responseFormatter->formatException($e, $this->getUser() !== null);
        } catch (\Exception $e) {
            return $this->responseFormatter->formatError(
                $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR,
                $this->getUser() !== null
            );
        }
    }
}
