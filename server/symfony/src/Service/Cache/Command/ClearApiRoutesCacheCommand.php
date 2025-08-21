<?php

namespace App\Service\Cache\Command;

use App\Service\Cache\Core\CacheService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'cache:clear-api-routes',
    description: 'Clear API routes cache after adding new routes to database'
)]
class ClearApiRoutesCacheCommand extends Command
{
    public function __construct(
        private CacheService $cacheService
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Clearing API Routes Cache');

        try {
            $this->cacheService
                ->withCategory(CacheService::CATEGORY_API_ROUTES)
                ->invalidateAllListsInCategory();

            $io->success('API routes cache cleared successfully!');
            $io->text('New routes from the database will now be loaded on the next request.');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Error clearing API routes cache: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
