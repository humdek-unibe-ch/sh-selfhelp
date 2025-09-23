<?php

namespace App\Controller\Api\V1\Auth;

use App\Controller\Trait\RequestValidatorTrait;
use App\Entity\User;
use App\Exception\RequestValidationException;
use App\Service\Auth\UserValidationService;
use App\Service\Core\ApiResponseFormatter;
use App\Service\Core\LookupService;
use App\Service\Core\TransactionService;
use App\Service\JSON\JsonSchemaValidationService;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * API V1 User Validation Controller
 *
 * Handles user account validation endpoints for API v1
 */
class UserValidationController extends AbstractController
{
    use RequestValidatorTrait;

    public function __construct(
        private readonly UserValidationService $userValidationService,
        private readonly ApiResponseFormatter $responseFormatter,
        private readonly JsonSchemaValidationService $jsonSchemaValidationService,
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly TransactionService $transactionService,
        private readonly LookupService $lookupService,
        private readonly Connection $connection,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * Check if validation token is valid before showing the form
     *
     * @route /validate/{user_id}/{token}
     * @method GET
     */
    public function validateToken(Request $request): JsonResponse
    {
        try {
            // Get route parameters from request attributes
            $userId = (int) $request->attributes->get('user_id');
            $token = $request->attributes->get('token');

            // Validate parameters
            if (!$userId || !$token) {
                return $this->responseFormatter->formatError(
                    'Invalid route parameters',
                    Response::HTTP_BAD_REQUEST
                );
            }

            // Find the user
            $user = $this->entityManager->getRepository(User::class)->find($userId);
            if (!$user) {
                return $this->responseFormatter->formatError(
                    'User not found',
                    Response::HTTP_NOT_FOUND
                );
            }

            // Check if token matches
            if ($user->getToken() !== $token) {
                return $this->responseFormatter->formatError(
                    'Invalid validation token',
                    Response::HTTP_BAD_REQUEST
                );
            }

            // Token is valid, return user information for form display
            return $this->responseFormatter->formatSuccess([
                'user_id' => $userId,
                'email' => $user->getEmail(),
                'name' => $user->getName(),
                'token_valid' => true,
                'message' => 'Token is valid. You can proceed with account setup.'
            ], 'responses/auth/validate_token', Response::HTTP_OK, false);

        } catch (\Exception $e) {
            $this->logger->error('Token validation check error', [
                'userId' => $userId,
                'token' => substr($token, 0, 8) . '...', // Log only first 8 chars for security
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->responseFormatter->formatError(
                'An error occurred while validating the token.',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Complete user validation with password, name, gender and additional form inputs
     *
     * @route /validate/{user_id}/{token}/complete
     * @method POST
     */
    public function completeValidation(Request $request): JsonResponse
    {
        try {
            // Get route parameters from request attributes
            $userId = (int) $request->attributes->get('user_id');
            $token = $request->attributes->get('token');

            // Validate parameters
            if (!$userId || !$token) {
                return $this->responseFormatter->formatError(
                    'Invalid route parameters',
                    Response::HTTP_BAD_REQUEST
                );
            }

            // Validate request against JSON schema
            $data = $this->validateRequest($request, 'requests/auth/complete_validation', $this->jsonSchemaValidationService);

            // Extract user data
            $password = $data['password'] ?? null;
            $name = $data['name'] ?? null;
            $gender = $data['gender'] ?? null;
            $formInputs = $data['form_inputs'] ?? [];
            $sectionId = $data['section_id'] ?? null;

            // Validate the token first
            $validationResult = $this->userValidationService->validateToken($userId, $token);
            if (!$validationResult['success']) {
                return $this->responseFormatter->formatError(
                    $validationResult['error'],
                    Response::HTTP_BAD_REQUEST
                );
            }

            // Get the user
            $user = $this->entityManager->getRepository(User::class)->find($userId);
            if (!$user) {
                return $this->responseFormatter->formatError(
                    'User not found',
                    Response::HTTP_NOT_FOUND
                );
            }

            // Update user information
            $this->entityManager->beginTransaction();

            try {
                // Update password if provided
                if ($password) {
                    $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
                    $user->setPassword($hashedPassword);
                }

                // Update name if provided
                if ($name) {
                    $user->setName($name);
                }

                // Update gender if provided
                if ($gender) {
                    // Find gender by name (case-insensitive)
                    $genderEntity = $this->entityManager->getRepository(\App\Entity\Gender::class)
                        ->findOneBy(['name' => ucfirst(strtolower($gender))]);

                    if ($genderEntity) {
                        $user->setGender($genderEntity);
                    }
                }

                $this->entityManager->flush();

                // Save user input data if provided
                if (!empty($formInputs) && $sectionId) {
                    $this->saveUserFormInputs($userId, $formInputs, $sectionId, 'user_validation');
                }

                // Log the transaction
                $this->transactionService->logTransaction(
                    'update',
                    LookupService::TRANSACTION_BY_BY_USER,
                    'users',
                    $userId,
                    false,
                    json_encode([
                        'action' => 'account_validation_completed',
                        'email' => $user->getEmail(),
                        'name' => $user->getName(),
                        'has_password' => !empty($password),
                        'has_form_inputs' => !empty($formInputs),
                        'section_id' => $sectionId
                    ])
                );

                $this->entityManager->commit();

                return $this->responseFormatter->formatSuccess([
                    'message' => 'Account validation completed successfully',
                    'user_id' => $userId,
                    'email' => $user->getEmail(),
                    'name' => $user->getName()
                ], 'responses/auth/complete_validation', Response::HTTP_OK, true);

            } catch (\Exception $e) {
                $this->entityManager->rollback();
                throw $e;
            }

        } catch (RequestValidationException $e) {
            // Let the ApiExceptionListener handle this
            throw $e;
        } catch (\InvalidArgumentException $e) {
            return $this->responseFormatter->formatError(
                $e->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        } catch (\Exception $e) {
            $this->logger->error('Complete validation error', [
                'userId' => $userId,
                'token' => substr($token, 0, 8) . '...',
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->responseFormatter->formatError(
                'An error occurred while completing validation.',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Save user form inputs to dataTables
     *
     * @param int $userId User ID
     * @param array $formInputs Form input data
     * @param int $sectionId Section ID
     * @param string $formName Form name
     */
    private function saveUserFormInputs(int $userId, array $formInputs, int $sectionId, string $formName): void
    {
        // Prepare data for saving
        $dataToSave = [
            'user_id' => $userId,
            'section_id' => $sectionId,
            'form_name' => $formName,
            'created_at' => date('Y-m-d H:i:s')
        ];

        // Add form inputs
        foreach ($formInputs as $key => $value) {
            $dataToSave[$key] = $value;
        }

        // Use the legacy UserInput service to save data
        // Create a simple transaction wrapper for the legacy UserInput class
        $legacyTransactionService = new class($this->transactionService) {
            private $transactionService;

            public function __construct($transactionService) {
                $this->transactionService = $transactionService;
            }

            public function add_transaction($type, $by, $userId, $table, $recordId, $isInsert, $data) {
                $this->transactionService->logTransaction($type, $by, $table, $recordId, $isInsert, json_encode($data));
            }
        };

        // Include the legacy UserInput class
        require_once __DIR__ . '/../../../../legacy/UserInput.php';

        $userInput = new \UserInput($this->connection, $legacyTransactionService);
        $userInput->save_data('by_system', 'user_validation_inputs', $dataToSave);
    }
}
