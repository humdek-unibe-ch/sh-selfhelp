<?php

namespace App\Command;

use App\Service\Core\GlobalCacheService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'cache:clear-api-routes',
    description: 'Clear API routes cache after adding new routes to database'
)]
class CacheClearApiRoutesCommand extends Command
{
    public function __construct(
        private GlobalCacheService $cacheService
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Clearing API Routes Cache');

        try {
            $success = $this->cacheService->clearApiRoutesCache();

            if ($success) {
                $io->success('API routes cache cleared successfully!');
                $io->text('New routes from the database will now be loaded on the next request.');
                return Command::SUCCESS;
            } else {
                $io->error('Failed to clear API routes cache.');
                return Command::FAILURE;
            }
        } catch (\Exception $e) {
            $io->error('Error clearing API routes cache: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
