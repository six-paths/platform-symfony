<?php

namespace Sixpaths\Platform\Symfony\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Sixpaths\Platform\Symfony\Filter\AbstractFilter;

abstract class AbstractRepository extends ServiceEntityRepository
{
    /**
     * @var integer
     */
    protected $page;

    /**
     * @var integer
     */
    protected $limit;

    /**
     * @var array<int, string>
     */
    protected $ordering = [];

    /**
     * @var array<int, string>
     */
    protected $orderByFields = [];

    /**
     * @var integer
     */
    protected $offset;

    /**
     * @var string
     */
    protected $entityAlias = 'i';

    /**
     * @var string
     */
    protected $defaultOrderColumn = 'id';

    /**
     * @var string
     */
    protected $defaultOrder = 'asc';

    /**
     * @var string
     */
    protected $identifier = 'id';

    /**
     * @var string
     */
    protected $className;

    /**
     * @phpstan-param class-string<\Sixpaths\Platform\Symfony\Entity\AbstractEntity> $className
     */
    public function __construct(
        ManagerRegistry $registry,
        string $className
    ) {
        parent::__construct($registry, $className);

        $this->className = $className;
    }

    /**
     * @param \Sixpaths\Platform\Symfony\Filter\AbstractFilter $filter
     * @param array<int, string> $ordering
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getBaseQueryBuilder(
        AbstractFilter $filter,
        array $ordering = []
    ): QueryBuilder {
        $this->parseCommonFilterParams($filter);

        $qb = $this->createQueryBuilder($this->entityAlias)
            ->select($this->entityAlias)
            ->setFirstResult($this->offset)
            ->setMaxResults($this->limit);

        if (count($ordering) >= 1) {
            $this->addOrdering($qb, $ordering);
        }

        $this->addOrdering($qb, $this->ordering);
        $this->addFilters($qb, $filter);

        return $qb;
    }

    abstract protected function addFilters(QueryBuilder $qb, AbstractFilter $filter): void;

    /**
     * Parse the given filter to set common object properties
     *
     * @param \Sixpaths\Platform\Symfony\Filter\AbstractFilter $filter
     *
     * @return void
     */
    final protected function parseCommonFilterParams(AbstractFilter $filter): void
    {
        /** @var int */
        $page = $filter->get('page', AbstractFilter::DEFAULT_PAGE, 'integer');
        $this->page = $page;

        /** @var int */
        $limit = $filter->get('limit', AbstractFilter::DEFAULT_LIMIT, 'integer');
        $this->limit = $limit;

        /** @var array<int, string> */
        $ordering = $filter->get('order', null, 'order');
        $this->ordering = $ordering;

        $this->offset = (($this->page - 1) * $this->limit);
    }

    /**
     * Add ordering to the query
     *
     * @param \Doctrine\ORM\QueryBuilder $qb
     * @param array<int, string> $ordering
     *
     * @return void
     */
    final protected function addOrdering(QueryBuilder $qb, array $ordering): void
    {
        foreach ($ordering as $key => $order) {
            $order = $order ? 'asc' : 'desc';

            if (array_key_exists($key, $this->orderByFields)) {
                $qb->addOrderBy($this->orderByFields[$key], $order);
            }
        }

        $qb->addOrderBy($this->entityAlias . '.' . ($this->defaultOrderColumn ?: $this->identifier), $this->defaultOrder);
    }

    /**
     * Gets the passed alias or the current repository's alias
     *
     * @param string|null $alias
     *
     * @return string
     */
    final protected function getAlias(string $alias = null): string
    {
        return $alias ?? $this->entityAlias;
    }

    final protected function applyBaseFilters(
        QueryBuilder $qb,
        AbstractFilter $filter
    ): void {
        $this->addSingleFilter($qb, 'id', $filter->get('id'), $filter->getModifier('id'));
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $qb
     * @param string $field
     * @param mixed|null $value
     * @param array<string, mixed> $modifiers
     * @param string|null $alias
     */
    final protected function addSingleFilter(
        QueryBuilder $qb,
        string $field,
        $value,
        array $modifiers,
        string $alias = null
    ): void {
        ['type' => $type, 'isInverse' => $isInverse] = array_merge(['type' => 'contains', 'isInverse' => false], $modifiers);
        $isInverse = filter_var($isInverse, FILTER_VALIDATE_BOOLEAN);

        switch ($type) {
            case 'isEquals':
                $this->addEqualityFilter($qb, $field, $value, $isInverse, $alias);
                break;

            case 'contains':
                /** @var string */
                $stringValue = $value;

                $this->addStringContainsFilter($qb, $field, $stringValue, $isInverse, $alias);
                break;

            case 'greaterThanEquals':
                $this->addGreaterThanEqualsFilter($qb, $field, $value, $isInverse, $alias);
                break;

            case 'isNull':
                $this->addNullFilter($qb, $field, $isInverse, $alias);
                break;

            case 'anyOf':
                $this->addAnyOfFilter($qb, $field, $value, $isInverse, $alias);
                break;

            case 'instanceof':
                /** @var string */
                $className = $value;

                $this->addInstanceOfFilter($qb, $className, $isInverse, $alias);
                break;
        }
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $qb
     * @param string $field
     * @param mixed $rawValue
     * @param mixed $value
     * @param array<int, array<string, string>> $operators
     * @param bool $isInverse
     * @param string|null $alias
     */
    final private function addGroupComparisonFilter(
        QueryBuilder $qb,
        string $field,
        $rawValue,
        $value,
        array $operators,
        bool $isInverse = false,
        string $alias = null
    ): void {
        if ($rawValue !== null) {
            $logicalOperator = $operators[$isInverse]['logical'];
            $comparisonOperator = $operators[$isInverse]['comparison'];

            $fields = explode(',', $field);
            if (count($fields) > 1) {
                $queryParts = ['CONCAT(' . implode(", ' ', ", $fields) . ')' . $comparisonOperator . ':concatField'];
                $qb->setParameter(':concatField', $value);
            }

            foreach ($fields as $field) {
                $queryParts[] = $this->getAliasedField($field, $alias) . $comparisonOperator . ':' . $this->getParametisedField($field);
                $qb->setParameter(':' . $this->getParametisedField($field), $value);
            }

            $qb->andWhere('(' . implode($logicalOperator, $queryParts) . ')');
        }
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $qb
     * @param string $field
     * @param mixed|null $value
     * @param bool $isInverse
     * @param string|null $alias
     */
    final protected function addEqualityFilter(
        QueryBuilder $qb,
        string $field,
        $value,
        bool $isInverse = false,
        string $alias = null
    ): void {

        $this->addGroupComparisonFilter(
            $qb,
            $field,
            $value,
            $value,
            [false => ['logical' => ' OR ', 'comparison' => ' = '], true => ['logical' => ' AND ', 'comparison' => ' != ']],
            $isInverse,
            $alias
        );
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $qb
     * @param string $field
     * @param string $value
     * @param bool $isInverse
     * @param string|null $alias
     */
    final protected function addStringContainsFilter(
        QueryBuilder $qb,
        string $field,
        string $value,
        bool $isInverse = false,
        string $alias = null
    ): void {
        $this->addGroupComparisonFilter(
            $qb,
            $field,
            $value,
            '%' . addcslashes($value, '%_') . '%',
            [false => ['logical' => ' OR ', 'comparison' => ' LIKE '], true => ['logical' => ' AND ', 'comparison' => ' NOT LIKE ']],
            $isInverse,
            $alias
        );
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $qb
     * @param string $field
     * @param mixed|null $value
     * @param bool $isInverse
     * @param string|null $alias
     */
    final protected function addGreaterThanEqualsFilter(
        QueryBuilder $qb,
        string $field,
        $value,
        bool $isInverse = false,
        string $alias = null
    ): void {
        $this->addGroupComparisonFilter(
            $qb,
            $field,
            $value,
            $value,
            [false => ['logical' => ' OR ', 'comparison' => ' >= '], true => ['logical' => ' AND ', 'comparison' => '< ']],
            $isInverse,
            $alias
        );
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $qb
     * @param string $field
     * @param bool $isInverse
     * @param string|null $alias
     */
    final protected function addNullFilter(
        QueryBuilder $qb,
        string $field,
        bool $isInverse = false,
        string $alias = null
    ): void {
        if ($isInverse) {
            $qb->andWhere($this->getAliasedField($field, $alias) . ' IS NOT NULL');

            return;
        }

        $qb->andWhere($this->getAliasedField($field, $alias) . ' IS NULL');
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $qb
     * @param string $field
     * @param mixed|null $values
     * @param bool $isInverse
     * @param string|null $alias
     */
    final protected function addAnyOfFilter(
        QueryBuilder $qb,
        string $field,
        $values,
        bool $isInverse = false,
        string $alias = null
    ): void {
        if (!is_array($values)) {
            $values = [$values];
        }

        $queryParts = [];

        if (in_array('~', $values)) {
            $queryParts[] = $this->getAliasedField($field, $alias) . ' IS ' . ($isInverse ? 'NOT' : '') . ' NULL';
            $values = array_diff($values, ['~']);
        }

        if (count($values) > 0) {
            $queryParts[] = $this->getAliasedField($field, $alias) . ' ' . ($isInverse ? 'NOT' : '') . ' IN (:' . $this->getParametisedField($field) . ')';
            $qb->setParameter($this->getParametisedField($field), $values);
        }

        if (count($queryParts) > 0) {
            $qb->andWhere(implode($isInverse ? ' AND ' : ' OR ', $queryParts));
        }
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $qb
     * @param string|null $value
     * @param bool $isInverse
     * @param string|null $alias
     */
    final protected function addInstanceOfFilter(
        QueryBuilder $qb,
        $value,
        bool $isInverse = false,
        string $alias = null
    ): void {
        if ($isInverse) {
            $qb->andWhere($this->getAlias($alias) . ' NOT INSTANCE OF ' . $value);

            return;
        }

        $qb->andWhere($this->getAlias($alias) . ' INSTANCE OF ' . $value);
    }

    /**
     * @param array<string> $roles
     * @param bool $isInverse
     * @param string|null  $alias
     */
    final protected function getRoleFilter(
        array $roles = [],
        bool $isInverse = false,
        string $alias = null
    ): string {
        $roleChecks = [];

        foreach ($roles as $role) {
            $roleChecks[] = $this->getAlias($alias) . ".roles " . ($isInverse ? 'NOT' : '') . " LIKE '%\"" . $role . "\"%'";
        }

        return implode(($isInverse ? ' AND ' : ' OR '), $roleChecks);
    }

    /**
     * @param array<string> $types
     * @param bool $isInverse
     * @param string|null  $alias
     */
    final protected function getTypeFilter(
        array $types = [],
        bool $isInverse = false,
        string $alias = null
    ): string {
        $typeChecks = [];

        foreach ($types as $type) {
            $typeChecks[] = $this->getAlias($alias) . ".types " . ($isInverse ? 'NOT' : '') . " LIKE '%\"" . $type . "\"%'";
        }

        return implode(($isInverse ? ' AND ' : ' OR '), $typeChecks);
    }

    /**
     * @param \Sixpaths\Platform\Symfony\Filter\AbstractFilter $filter
     * @param string $field
     * @param array<string, mixed> $defaults
     *
     * @return array<string, mixed>
     */
    final protected function getModifier(
        AbstractFilter $filter,
        string $field,
        array $defaults = ['type' => 'contains', 'isInverse' => false]
    ): array {
        return array_merge($defaults, $filter->getModifier($field));
    }

    private function getAliasedField(
        string $field,
        string $alias = null
    ): string {
        if (strpos($field, '.') === false) {
            return $this->getAlias($alias) . '.' . $field;
        }

        return $field;
    }

    private function getParametisedField(string $field): string
    {
        $fieldParts = explode('.', $field);

        return $fieldParts[count($fieldParts) - 1];
    }
}
