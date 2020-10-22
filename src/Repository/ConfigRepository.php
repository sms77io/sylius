<?php declare(strict_types=1);

namespace Sms77\SyliusPlugin\Repository;

use Sms77\SyliusPlugin\Entity\Config;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

class ConfigRepository extends EntityRepository implements ConfigRepositoryInterface {
    public function findEnabled(): ?Config {
        return $this->findOneBy(['enabled' => 1]);
    }

    public function findByNot(string $field, $value): array {
        $qb = $this->createQueryBuilder('c');
        $qb->where($qb->expr()->not($qb->expr()->eq("c.$field", '?1')));
        $qb->setParameter(1, $value);

        return $qb->getQuery()->getResult();
    }
}
