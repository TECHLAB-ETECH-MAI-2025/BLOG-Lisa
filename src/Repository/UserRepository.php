<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function countAdmins(): int
{
    $conn = $this->getEntityManager()->getConnection();

    $sql = <<<SQL
        SELECT COUNT(*) FROM "user"
        WHERE roles::jsonb @> :role_admin
           OR roles::jsonb @> :role_super_admin
    SQL;

    return (int) $conn->fetchOne($sql, [
        'role_admin' => json_encode(['ROLE_ADMIN']),
        'role_super_admin' => json_encode(['ROLE_SUPER_ADMIN']),
    ]);
}

}
