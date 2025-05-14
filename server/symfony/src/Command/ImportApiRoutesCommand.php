<?php

namespace App\Command;

use App\Entity\ApiRoute;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:import-api-routes',
    description: 'Import API routes from the api_routes table',
)]
class ImportApiRoutesCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        string $name = null
    ) {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        // Query the database for routes
        $connection = $this->entityManager->getConnection();
        $sql = 'SELECT * FROM api_routes';
        
        try {
            $rows = $connection->executeQuery($sql)->fetchAllAssociative();
            
            if (empty($rows)) {
                $io->warning('No routes found in api_routes table.');
                return Command::FAILURE;
            }
            
            $routeRepository = $this->entityManager->getRepository(ApiRoute::class);
            
            // Clear existing routes if needed
            $existingRoutes = $routeRepository->findAll();
            foreach ($existingRoutes as $route) {
                $this->entityManager->remove($route);
            }
            $this->entityManager->flush();
            
            // Import routes from database
            $importedCount = 0;
            foreach ($rows as $row) {
                $route = new ApiRoute();
                $route->setName($row['route_name'] ?? ('route_' . $importedCount));
                $route->setPath($row['path']);
                $route->setController($row['controller']);
                $route->setMethods($row['methods'] ?? 'GET');
                
                // Convert requirements if they exist
                if (!empty($row['requirements'])) {
                    // Requirements are stored as a string like {"page_keyword": "[A-Za-z0-9_-]+"}
                    $route->setRequirements($row['requirements']);
                }
                
                $this->entityManager->persist($route);
                $importedCount++;
            }
            
            $this->entityManager->flush();
            
            $io->success(sprintf('Successfully imported %d routes.', $importedCount));
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Error importing routes: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
