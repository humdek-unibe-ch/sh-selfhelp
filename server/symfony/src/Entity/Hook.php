<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'hooks')]
class Hook
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'id_hookTypes', type: 'integer')]
    private int $idHookTypes;

    #[ORM\Column(name: 'name', type: 'string', length: 100, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(name: 'description', type: 'string', length: 1000, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(name: 'class', type: 'string', length: 100)]
    private string $class;

    #[ORM\Column(name: 'function', type: 'string', length: 100)]
    private string $function;

    #[ORM\Column(name: 'exec_class', type: 'string', length: 100)]
    private string $execClass;

    #[ORM\Column(name: 'exec_function', type: 'string', length: 100)]
    private string $execFunction;

    #[ORM\Column(name: 'priority', type: 'integer', options: ['default' => 10])]
    private int $priority = 10;

    public function getId(): ?int { return $this->id; }
    public function getIdHookTypes(): int { return $this->idHookTypes; }
    public function setIdHookTypes(int $idHookTypes): self { $this->idHookTypes = $idHookTypes; return $this; }
    public function getName(): ?string { return $this->name; }
    public function setName(?string $name): self { $this->name = $name; return $this; }
    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): self { $this->description = $description; return $this; }
    public function getClass(): string { return $this->class; }
    public function setClass(string $class): self { $this->class = $class; return $this; }
    public function getFunction(): string { return $this->function; }
    public function setFunction(string $function): self { $this->function = $function; return $this; }
    public function getExecClass(): string { return $this->execClass; }
    public function setExecClass(string $execClass): self { $this->execClass = $execClass; return $this; }
    public function getExecFunction(): string { return $this->execFunction; }
    public function setExecFunction(string $execFunction): self { $this->execFunction = $execFunction; return $this; }
    public function getPriority(): int { return $this->priority; }
    public function setPriority(int $priority): self { $this->priority = $priority; return $this; }
}
// ENTITY RULE
