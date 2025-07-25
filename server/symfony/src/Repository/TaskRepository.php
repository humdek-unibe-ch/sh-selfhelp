<?php

namespace App\Repository;

use App\Entity\Task;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Task>
 */
class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    /**
     * Find task by ID
     */
    public function findTaskById(int $id): ?Task
    {
        return $this->find($id);
    }

    /**
     * Find tasks by name
     */
    public function findByName(string $name): array
    {
        return $this->findBy(['name' => $name]);
    }

    /**
     * Find active tasks
     */
    public function findActiveTasks(): array
    {
        return $this->findBy(['active' => true]);
    }
} 