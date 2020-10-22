<?php declare(strict_types=1);

namespace Sms77\SyliusPlugin\Repository;

use Sms77\SyliusPlugin\Entity\Config;
use Sylius\Component\Resource\Repository\RepositoryInterface;

interface ConfigRepositoryInterface extends RepositoryInterface {
    public function findEnabled(): ?Config;

    public function findByNot(string $field, $value): array;
}
