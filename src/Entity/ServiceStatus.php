<?php

namespace App\Entity;

use App\Enum\ServiceStatusType;
use App\Repository\ServiceStatusRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ServiceStatusRepository::class)]
class ServiceStatus
{
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column]
	private ?int $id = null;

	#[ORM\Column(length: 255)]
	private ?string $service = null;

	#[ORM\Column]
	private ?ServiceStatusType $status = ServiceStatusType::UNKNOWN;

	#[ORM\Column]
	private ?int $high_tasks = 0;

	#[ORM\Column]
	private ?int $medium_tasks = 0;

	#[ORM\Column]
	private ?int $low_tasks = 0;

	#[ORM\Column]
	private ?string $url = "";

	#[ORM\Column]
	private ?string $access_key = "";

	public function getId(): ?int
	{
		return $this->id;
	}

	public function getService(): ?string
	{
		return $this->service;
	}


	public function setService(string $service): static
	{
		$this->service = $service;

		return $this;
	}

	public function isStatus(): ?ServiceStatusType
	{
		return $this->status;
	}

	public function setStatus(ServiceStatusType $status): static
	{
		$this->status = $status;

		return $this;
	}

	public function getHighTasks(): ?int
	{
		return $this->high_tasks;
	}

	public function getMediumTasks(): ?int
	{
		return $this->medium_tasks;
	}

	public function getLowTasks(): ?int
	{
		return $this->low_tasks;
	}

	public function getStatus(): ?ServiceStatusType
	{
		return $this->status;
	}


	public function setHighTasks(int $high_tasks): static
	{
		$this->high_tasks = $high_tasks;

		return $this;
	}
	public function setMediumTasks(int $medium_tasks): static
	{
		$this->medium_tasks = $medium_tasks;

		return $this;
	}
	public function setLowTasks(int $low_tasks): static
	{
		$this->low_tasks = $low_tasks;

		return $this;
	}

	public function getUrl(): ?string
	{
		return $this->url;
	}
	public function setUrl(string $url): static
	{
		$this->url = $url;

		return $this;
	}
	public function getKey(): ?string
	{
		return $this->access_key;
	}
	public function setKey(string $key): static
	{
		$this->access_key = $key;

		return $this;
	}
}
