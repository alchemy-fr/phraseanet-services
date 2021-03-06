<?php

declare(strict_types=1);

namespace App\Filter;

use Alchemy\RemoteAuthBundle\Model\RemoteUser;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractContextAwareFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Core\Exception\InvalidArgumentException;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;

class PublicationFilter extends AbstractContextAwareFilter
{
    private Security $security;

    /**
     * @required
     */
    public function setSecurity(Security $security): void
    {
        $this->security = $security;
    }

    protected function filterProperty(
        string $property,
        $value,
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        string $operationName = null
    ) {
    }

    public function apply(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        string $operationName = null,
        array $context = []
    ) {
        parent::apply($queryBuilder, $queryNameGenerator, $resourceClass, $operationName, $context);

        $filters = $context['filters'];

        if (isset($filters['parentId'])) {
            $queryBuilder
                ->andWhere(sprintf('o.parent = :parentId'))
                ->setParameter('parentId', $filters['parentId'])
            ;
        } elseif (!(isset($filters['flatten']) && true === $this->normalizeBoolValue($filters['flatten'], 'flatten'))) {
            $queryBuilder->andWhere(sprintf('o.parent IS NULL'));
        }

        if (isset($filters['profileId'])) {
            $queryBuilder
                ->andWhere(sprintf('o.profile = :profileId'))
                ->setParameter('profileId', $filters['profileId'])
            ;
        }

        if (isset($filters['expired']) && true === $this->normalizeBoolValue($filters['expired'], 'expired')) {
            if (!in_array('p', $queryBuilder->getAllAliases())) {
                $queryBuilder->leftJoin('o.profile', 'p');
            }
            $queryBuilder
                ->andWhere('(o.config.expiresAt < :expiresNow OR (o.config.expiresAt IS NULL AND p.config.expiresAt < :expiresNow))')
                ->setParameter('expiresNow', date('Y-m-d H:i:s'))
            ;
        }

        if (isset($filters['empty']) && true === $this->normalizeBoolValue($filters['empty'], 'empty')) {
            $queryBuilder
                ->leftJoin('o.assets', 'pa')
                ->andWhere('pa.id IS NULL')
            ;
        }

        if (isset($filters['disabled']) && true === $this->normalizeBoolValue($filters['disabled'], 'disabled')) {
            if (!in_array('p', $queryBuilder->getAllAliases())) {
                $queryBuilder->leftJoin('o.profile', 'p');
            }

            $queryBuilder
                ->andWhere('(o.config.enabled = false OR (p.id IS NOT NULL AND p.config.enabled = false))')
            ;
        }

        if (isset($filters['mine'])) {
            $user = $this->security->getUser();
            if (!$user instanceof RemoteUser) {
                throw new AuthenticationException('Should be an authenticated user');
            }
            $queryBuilder
                ->andWhere(sprintf('o.ownerId = :me'))
                ->setParameter('me', $user->getId())
            ;
        }
    }

    public function getDescription(string $resourceClass): array
    {
        return [
            'flatten' => [
                'property' => 'flatten',
                'type' => 'bool',
                'required' => false,
                'description' => 'Get all the publications, regardless the hierarchy',
            ],
            'parentId' => [
                'property' => 'parentId',
                'type' => 'string',
                'required' => false,
                'description' => 'Get children of this publication',
            ],
            'profileId' => [
                'property' => 'profileId',
                'type' => 'string',
                'required' => false,
                'description' => 'Filter by profile',
            ],
            'mine' => [
                'property' => 'mine',
                'type' => 'bool',
                'required' => false,
                'description' => 'Get publications which the current authenticated user owns',
            ],
            'expired' => [
                'property' => 'expired',
                'type' => 'bool',
                'required' => false,
                'description' => 'Get publications which expiration date has passed',
            ],
            'empty' => [
                'property' => 'empty',
                'type' => 'bool',
                'required' => false,
                'description' => 'Get publications with no asset',
            ],
            'disabled' => [
                'property' => 'disabled',
                'type' => 'bool',
                'required' => false,
                'description' => 'Get publications which are not enabled',
            ],
        ];
    }

    private function normalizeBoolValue($value, string $property): ?bool
    {
        if (in_array($value, [true, 'true', '1'], true)) {
            return true;
        }

        if (in_array($value, [false, 'false', '0'], true)) {
            return false;
        }

        $this->getLogger()->notice('Invalid filter ignored', [
            'exception' => new InvalidArgumentException(sprintf('Invalid boolean value for "%s" property, expected one of ( "%s" )', $property, implode('" | "', [
                'true',
                'false',
                '1',
                '0',
            ]))),
        ]);

        return null;
    }
}
