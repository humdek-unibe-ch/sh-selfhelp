<?php

namespace App\Service\Auth;

use App\Entity\Lookup;
use App\Entity\ScheduledJob;
use App\Entity\User;
use App\Service\Core\BaseService;
use App\Service\Core\JobSchedulerService;
use App\Service\Core\LookupService;
use App\Service\Core\TransactionService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Service responsible for user account validation
 * 
 * This service handles:
 * - Token validation and account activation
 * - Scheduling welcome emails after successful validation
 * - Validation email scheduling and resending
 */
class UserValidationService extends BaseService
{
    private EntityManagerInterface $entityManager;
    private JobSchedulerService $jobSchedulerService;
    private TransactionService $transactionService;
    private LoggerInterface $logger;

    public function __construct(
        EntityManagerInterface $entityManager,
        JobSchedulerService $jobSchedulerService,
        TransactionService $transactionService,
        LoggerInterface $logger,
        LookupService $lookupService
    ) {
        $this->entityManager = $entityManager;
        $this->jobSchedulerService = $jobSchedulerService;
        $this->transactionService = $transactionService;
        $this->logger = $logger;
        $this->lookupService = $lookupService;
    }

    /**
     * Setup validation for an existing user (generates token and schedules email)
     * This is called after user creation to add validation functionality
     * 
     * @param User $user The user entity
     * @param array $emailConfig Optional email configuration overrides
     * @return array Result with token and job ID
     */
    public function setupUserValidation(User $user, array $emailConfig = []): array
    {
        try {
            // Generate validation token and store in user entity
            $token = $this->generateValidationToken();
            $user->setToken($token);
            
            // Set user status to invited
            $status = $this->entityManager->getRepository(Lookup::class)->findOneBy(['typeCode' => LookupService::USER_STATUS, 'lookupValue' => LookupService::USER_STATUS_INVITED]);
            $user->setStatus($status);
            
            // Don't flush here - let the calling service handle transaction management
            // $this->entityManager->flush();

            // Schedule validation email
            $job = $this->scheduleValidationEmail($user->getId(), $token, $emailConfig);

            if (!$job) {
                throw new \Exception('Failed to schedule validation email');
            }

            // Don't log transaction here - let the calling service handle it
            // $this->transactionService->logTransaction(
            //     'update',
            //     JobSchedulerService::TRANSACTION_BY_SYSTEM,
            //     null,
            //     'users',
            //     $user->getId(),
            //     [
            //         'action' => 'validation_setup',
            //         'email' => $user->getEmail(),
            //         'validation_token' => $token,
            //         'job_id' => $jobId
            //     ]
            // );

            return [
                'success' => true,
                'token' => $token,
                'job_id' => $job->getId(),
                'validation_url' => "validate/{$user->getId()}/{$token}",
                'message' => 'Validation email has been sent.'
            ];

        } catch (\Exception $e) {
            $this->logger->error('Failed to setup user validation', [
                'error' => $e->getMessage(),
                'userId' => $user->getId()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Validate a token and activate user account
     * 
     * @param int $userId User ID
     * @param string $token Validation token
     * @return array Result of validation
     */
    public function validateToken(int $userId, string $token): array
    {
        try {
            $this->entityManager->beginTransaction();

            // Find the user
            $user = $this->entityManager->getRepository(User::class)->find($userId);
            if (!$user) {
                return [
                    'success' => false,
                    'error' => 'User not found'
                ];
            }

            // Check if token matches
            if ($user->getToken() !== $token) {
                return [
                    'success' => false,
                    'error' => 'Invalid validation token'
                ];
            }

            // Check if user is already validated
            if (!$user->isBlocked()) {
                return [
                    'success' => false,
                    'error' => 'User account is already validated'
                ];
            }

            // Activate the user account
            $user->setBlocked(false);
            
            // Clear the token after successful validation
            $user->setToken(null);

            $this->entityManager->flush();

            // Schedule immediate welcome email
            $welcomeJobId = $this->scheduleWelcomeEmail($userId);

            // Log the validation
            $this->transactionService->logTransaction(
                'update',
                JobSchedulerService::TRANSACTION_BY_USER,
                $userId,
                'users',
                $userId,
                [
                    'action' => 'account_validated',
                    'token' => $token,
                    'email' => $user->getEmail(),
                    'welcome_job_id' => $welcomeJobId
                ]
            );

            $this->entityManager->commit();

            return [
                'success' => true,
                'message' => 'Account successfully validated',
                'user_id' => $userId,
                'welcome_job_id' => $welcomeJobId
            ];

        } catch (\Exception $e) {
            $this->entityManager->rollback();
            $this->logger->error('Failed to validate token', [
                'userId' => $userId,
                'token' => $token,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => 'Validation failed due to system error'
            ];
        }
    }

    /**
     * Resend validation email for a user
     * 
     * @param int $userId User ID
     * @param array $emailConfig Email configuration overrides
     * @return array Result of resend operation
     */
    public function resendValidationEmail(int $userId, array $emailConfig = []): array
    {
        try {
            // Check if user exists and is not validated
            $user = $this->entityManager->getRepository(User::class)->find($userId);
            if (!$user) {
                return [
                    'success' => false,
                    'error' => 'User not found'
                ];
            }

            if (!$user->isBlocked()) {
                return [
                    'success' => false,
                    'error' => 'User account is already validated'
                ];
            }

            // Generate new token
            $token = $this->generateValidationToken();
            $user->setToken($token);
            $this->entityManager->flush();

            // Schedule new validation email
            $jobId = $this->scheduleValidationEmail($userId, $token, $emailConfig);

            if (!$jobId) {
                return [
                    'success' => false,
                    'error' => 'Failed to schedule validation email'
                ];
            }

            return [
                'success' => true,
                'message' => 'Validation email resent successfully',
                'token' => $token,
                'job_id' => $jobId,
                'validation_url' => "validate/{$userId}/{$token}"
            ];

        } catch (\Exception $e) {
            $this->logger->error('Failed to resend validation email', [
                'userId' => $userId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => 'Failed to resend validation email'
            ];
        }
    }

    /**
     * Generate a validation token
     * 
     * @return string The generated token (32 character hex string)
     */
    private function generateValidationToken(): string
    {
        // Generate a secure random token
        return bin2hex(random_bytes(16)); // 32 character hex string
    }

    /**
     * Schedule a validation email for a user
     * 
     * @param int $userId User ID
     * @param string $token Validation token
     * @param array $emailConfig Email configuration overrides
     * @return int|false Job ID if successful, false on failure
     */
    private function scheduleValidationEmail(int $userId, string $token, array $emailConfig = []): ScheduledJob|false
    {
        // Get user information for email personalization
        $user = $this->entityManager->getRepository(User::class)->find($userId);
        if (!$user) {
            $this->logger->error('User not found for validation email', ['userId' => $userId]);
            return false;
        }

        // Prepare email configuration
        $defaultConfig = [
            'from_email' => 'noreply@selfhelp.com',
            'from_name' => 'SelfHelp Platform',
            'reply_to' => 'noreply@selfhelp.com',
            'subject' => 'Please validate your account',
            'recipient_emails' => $user->getEmail(),
            'body' => $this->generateValidationEmailBody($user, $token),
            'is_html' => true
        ];

        // Merge with provided configuration
        $emailConfig = array_merge($defaultConfig, $emailConfig);

        // Schedule the email job
        return $this->jobSchedulerService->scheduleUserValidationEmail($userId, $token, $emailConfig);
    }

    /**
     * Schedule immediate welcome email after successful validation
     * 
     * @param int $userId User ID
     * @param array $emailConfig Email configuration overrides
     * @return int|false Job ID if successful, false on failure
     */
    private function scheduleWelcomeEmail(int $userId, array $emailConfig = []): int|false
    {
        // Get user information for email personalization
        $user = $this->entityManager->getRepository(User::class)->find($userId);
        if (!$user) {
            $this->logger->error('User not found for welcome email', ['userId' => $userId]);
            return false;
        }

        // Prepare welcome email configuration
        $defaultConfig = [
            'from_email' => 'noreply@selfhelp.com',
            'from_name' => 'SelfHelp Platform',
            'reply_to' => 'support@selfhelp.com',
            'subject' => 'Welcome to SelfHelp Platform - Your account is now active!',
            'recipient_emails' => $user->getEmail(),
            'body' => $this->generateWelcomeEmailBody($user),
            'is_html' => true
        ];

        // Merge with provided configuration
        $emailConfig = array_merge($defaultConfig, $emailConfig);

        // Schedule the email job to be sent immediately
        try {
            $jobId = $this->jobSchedulerService->scheduleDirectEmailJob(
                $emailConfig,
                new \DateTime(), // Send immediately
                $userId
            );

            if ($jobId) {
                $this->logger->info('Welcome email scheduled successfully', [
                    'userId' => $userId,
                    'jobId' => $jobId,
                    'email' => $user->getEmail()
                ]);
            }

            return $jobId;
        } catch (\Exception $e) {
            $this->logger->error('Failed to schedule welcome email', [
                'userId' => $userId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Generate the HTML body for validation email
     */
    private function generateValidationEmailBody(User $user, string $token): string
    {
        $validationUrl = "validate/{$user->getId()}/{$token}";
        $userName = $user->getName() ?: $user->getEmail();
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Account Validation</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                <h2 style='color: #2c3e50;'>Welcome to SelfHelp Platform!</h2>
                
                <p>Hello {$userName},</p>
                
                <p>Thank you for registering with our platform. To complete your registration and activate your account, please click the validation link below:</p>
                
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='{$validationUrl}' 
                       style='background-color: #3498db; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                        Validate Your Account
                    </a>
                </div>
                
                <p>Alternatively, you can copy and paste this URL into your browser:</p>
                <p style='word-break: break-all; background-color: #f8f9fa; padding: 10px; border-radius: 3px;'>
                    {$validationUrl}
                </p>
                
                <p><strong>Important:</strong> This validation link will expire in 24 hours for security reasons.</p>
                
                <p>If you did not create this account, please ignore this email and the account will remain inactive.</p>
                
                <hr style='border: none; border-top: 1px solid #eee; margin: 30px 0;'>
                
                <p style='font-size: 12px; color: #666;'>
                    This is an automated message from the SelfHelp Platform. Please do not reply to this email.
                </p>
            </div>
        </body>
        </html>
        ";
    }

    /**
     * Generate the HTML body for welcome email
     */
    private function generateWelcomeEmailBody(User $user): string
    {
        $userName = $user->getName() ?: $user->getEmail();
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Welcome to SelfHelp Platform</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                <h2 style='color: #2c3e50;'>Welcome to SelfHelp Platform!</h2>
                
                <p>Hello {$userName},</p>
                
                <p><strong>Congratulations!</strong> Your account has been successfully validated and is now active.</p>
                
                <p>You can now access all the features of our platform:</p>
                
                <ul style='margin: 20px 0; padding-left: 30px;'>
                    <li>Access your personalized dashboard</li>
                    <li>Connect with our community</li>
                    <li>Explore available resources and tools</li>
                    <li>Participate in discussions and activities</li>
                </ul>
                
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='/' 
                       style='background-color: #27ae60; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                        Get Started
                    </a>
                </div>
                
                <p>If you have any questions or need assistance, please don't hesitate to contact our support team.</p>
                
                <p>Thank you for joining us, and welcome aboard!</p>
                
                <hr style='border: none; border-top: 1px solid #eee; margin: 30px 0;'>
                
                <p style='font-size: 12px; color: #666;'>
                    This is an automated message from the SelfHelp Platform. 
                    If you need help, please contact us at support@selfhelp.com
                </p>
            </div>
        </body>
        </html>
        ";
    }
} 