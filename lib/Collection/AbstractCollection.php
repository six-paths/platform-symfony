<?php

namespace Sixpaths\Platform\Symfony\Collection;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as Serializer;
use Sixpaths\Platform\Symfony\Filter\AbstractFilter;
use Sixpaths\Platform\Symfony\Interfaces\AbstractEntityInterface;
use Sixpaths\Platform\Symfony\Response\AbstractResponse;
use Sixpaths\Platform\Symfony\Security\Validator\AbstractValidator;

/**
 * @Hateoas\Relation(
 *     name = "self",
 *     href = @Hateoas\Route(
 *         "expr(object.getRoute())",
 *         parameters = "expr(object.getParameters())"
 *     ),
 *     exclusion = @Hateoas\Exclusion(
 *         excludeIf = "expr(object.getRoute() === null)",
 *         groups = {"list", "details"},
 *     ),
 *     attributes = {
 *         "method" = "GET",
 *     },
 * )
 * @Hateoas\Relation(
 *     name = "first",
 *     href = @Hateoas\Route(
 *         "expr(object.getRoute())",
 *         parameters = "expr(object.getParameters(1))",
 *     ),
 *     exclusion = @Hateoas\Exclusion(
 *         excludeIf = "expr(object.getRoute() === null or object.getPage() === 1)",
 *         groups = {"list", "details"},
 *     ),
 *     attributes = {
 *         "method" = "GET",
 *     },
 * )
 * @Hateoas\Relation(
 *     name = "previous",
 *     href = @Hateoas\Route(
 *         "expr(object.getRoute())",
 *         parameters = "expr(object.getParameters(object.getPage() - 1))",
 *     ),
 *     exclusion = @Hateoas\Exclusion(
 *         excludeIf = "expr(object.getRoute() === null or object.getPage() === 1)",
 *         groups = {"list", "details"},
 *     ),
 *     attributes = {
 *         "method" = "GET",
 *     },
 * )
 * @Hateoas\Relation(
 *     name = "next",
 *     href = @Hateoas\Route(
 *         "expr(object.getRoute())",
 *         parameters = "expr(object.getParameters(object.getPage() + 1))",
 *     ),
 *     exclusion = @Hateoas\Exclusion(
 *         excludeIf = "expr(object.getRoute() === null or object.getPages() <= object.getPage())",
 *         groups = {"list", "details"},
 *     ),
 *     attributes = {
 *         "method" = "GET",
 *     },
 * )
 * @Hateoas\Relation(
 *     name = "last",
 *     href = @Hateoas\Route(
 *         "expr(object.getRoute())",
 *         parameters = "expr(object.getParameters(object.getPages() ?: 1))",
 *     ),
 *     exclusion = @Hateoas\Exclusion(
 *         excludeIf = "expr(object.getRoute() === null or object.getPages() === 0 or object.getPage() === object.getPages())",
 *         groups = {"list", "details"},
 *     ),
 *     attributes = {
 *         "method" = "GET",
 *     },
 * )
 *
 *
 * @implements \ArrayAccess<int, \Sixpaths\Platform\Symfony\Interfaces\AbstractEntityInterface>
 */
abstract class AbstractCollection implements \ArrayAccess
{
    /**
     * @Serializer\Expose
     * @Serializer\Groups({"list", "details"})
     *
     * @var integer
     */
    protected $page = AbstractFilter::DEFAULT_PAGE;

    /**
     * @Serializer\Expose
     * @Serializer\Groups({"list", "details"})
     *
     * @var integer
     */
    protected $limit = AbstractFilter::DEFAULT_LIMIT;

    /**
     * @Serializer\Expose
     * @Serializer\Groups({"list", "details"})
     *
     * @var integer
     */
    protected $pages = 0;

    /**
     * @Serializer\Expose
     * @Serializer\Groups({"list", "details"})
     *
     * @var integer
     */
    protected $count = 0;

    /**
     * @var array<mixed>
     */
    private $arguments;

    /**
     * @var array<int, \Sixpaths\Platform\Symfony\Response\AbstractResponse>
     */
    private $cache = [];

    /**
     * @var array<mixed>
     */
    private $parameters = [];

    /**
     * @var array<string, \Sixpaths\Platform\Symfony\Interfaces\AbstractEntityInterface>
     */
    private $objects = [];

    /**
     * @var \Doctrine\ORM\Tools\Pagination\Paginator<\Sixpaths\Platform\Symfony\Interfaces\AbstractEntityInterface>
     */
    private $items;

    /**
     * @var string|null
     */
    private $route;

    /**
     * @var string|null
     */
    private $filterRoute;

    /**
     * @var \Sixpaths\Platform\Symfony\Filter\AbstractFilter|null
     */
    private $filterClass;

    /**
     * @var string|null
     */
    private $formRoute;

    /**
     * @var \Sixpaths\Platform\Symfony\Security\Validator\AbstractValidator|null
     */
    private $formClass;

    final public function __construct() {}

    abstract public function getWrapper(): string;

    /**
     * @param \Doctrine\ORM\Tools\Pagination\Paginator<\Sixpaths\Platform\Symfony\Interfaces\AbstractEntityInterface> $items
     * @param array<mixed> $parameters
     * @param array<mixed>|null $arguments
     *
     * @return \Sixpaths\Platform\Symfony\Collection\AbstractCollection
     */
    public static function create(
        /** @todo undo (member notes) */
        /*Paginator*/ $items,
        array $parameters,
        array $arguments = null
    ): AbstractCollection {
        return (new static())->setItems($items)
            ->setArguments($arguments)
            ->addParameters($parameters);
    }

    /**
     * Sets the value of items.
     *
     * @param \Doctrine\ORM\Tools\Pagination\Paginator<\Sixpaths\Platform\Symfony\Interfaces\AbstractEntityInterface> $items
     *
     * @return \Sixpaths\Platform\Symfony\Collection\AbstractCollection
     */
    final public function setItems(Paginator $items): AbstractCollection
    {
        $this->count = count($items);
        $this->items = $items;

        return $this;
    }

    /**
     * Gets the value of a parameter
     *
     * @param string $parameter
     *
     * @return mixed
     */
    final public function getParameter(string $parameter)
    {
        return $this->parameters[$parameter] ?? null;
    }

    /**
     * Gets the value of parameters.
     *
     * @param integer $page
     * @param integer $limit
     *
     * @return array<string, mixed>
     */
    final public function getParameters(
        int $page = null,
        int $limit = null
    ): array {
        $parameters = $this->parameters;
        $parameters['page'] = $page ?? $this->page;
        $parameters['limit'] = $limit ?? $this->limit;

        return $parameters;
    }

    /**
     * Sets the value of parameters.
     *
     * @param array<string, mixed> $parameters
     *
     * @return \Sixpaths\Platform\Symfony\Collection\AbstractCollection
     */
    final public function addParameters(array $parameters): AbstractCollection
    {
        foreach ($parameters as $parameter => $value) {
            if (!isset($this->parameters[$parameter])) {
                $this->parameters[$parameter] = $value;
            }
        }

        if (isset($parameters['page']) &&
            is_numeric($parameters['page'])) {
            $this->setPage((int) $parameters['page']);
        }

        if (isset($parameters['limit']) &&
            is_numeric($parameters['limit'])) {
            $this->setLimit((int) $parameters['limit']);
        }

        return $this;
    }

    /**
     * Gets all objects
     *
     * @return array<string, \Sixpaths\Platform\Symfony\Interfaces\AbstractEntityInterface>
     */
    final public function getObjects(): array
    {
        return $this->objects;
    }

    /**
     * Gets an object by name.
     *
     * @param string $name
     *
     * @return \Sixpaths\Platform\Symfony\Interfaces\AbstractEntityInterface|null
     */
    final public function getObject(string $name): ?AbstractEntityInterface
    {
        return $this->objects[$name] ?? null;
    }

    /**
     * Sets an object by name
     *
     * @param string $name
     * @param \Sixpaths\Platform\Symfony\Interfaces\AbstractEntityInterface $entity
     *
     * @return \Sixpaths\Platform\Symfony\Collection\AbstractCollection
     */
    final public function setObject(
        string $name,
        AbstractEntityInterface $entity
    ): AbstractCollection {
        $this->objects[$name] = $entity;

        return $this;
    }

    /**
     * Gets the value of route.
     *
     * @return string|null
     */
    final public function getRoute(): ?string
    {
        return $this->route;
    }

    /**
     * Sets the value of route.
     *
     * @param string $route
     * @param bool $setFilterRoute
     * @param array<mixed> $parameters
     *
     * @return \Sixpaths\Platform\Symfony\Collection\AbstractCollection
     */
    final public function setRoute(
        string $route,
        bool $setFilterRoute = false,
        array $parameters = []
    ): AbstractCollection {
        $this->route = $route;
        $this->addParameters($parameters);

        if ($setFilterRoute === true) {
            $this->setFilterRoute($route . '_filter');
        }

        return $this;
    }

    /**
     * Gets the value of filterRoute.
     *
     * @return string|null
     */
    final public function getFilterRoute(): ?string
    {
        return $this->filterRoute;
    }

    /**
     * Sets the value of filterRoute.
     *
     * @param string $route
     *
     * @return \Sixpaths\Platform\Symfony\Collection\AbstractCollection
     */
    final public function setFilterRoute(string $route): AbstractCollection
    {
        $this->filterRoute = $route;

        return $this;
    }

    /**
     * Gets the value of filterClass.
     *
     * @return \Sixpaths\Platform\Symfony\Filter\AbstractFilter|null
     */
    final public function getFilterClass(): ?AbstractFilter
    {
        return $this->filterClass;
    }

    /**
     * Gets the class name of the filterClass.
     *
     * @return string|null
     */
    final public function getFilterClassName(): ?string
    {
        if ($this->filterClass instanceof AbstractFilter) {
            return get_class($this->filterClass);
        }

        return null;
    }

    /**
     * Sets the value of filterClass.
     *
     * @param \Sixpaths\Platform\Symfony\Filter\AbstractFilter $filter
     *
     * @return \Sixpaths\Platform\Symfony\Collection\AbstractCollection
     */
    final public function setFilterClass(AbstractFilter $filter): AbstractCollection
    {
        $this->filterClass = $filter;

        return $this;
    }

    /**
     * Gets the value of formRoute.
     *
     * @return string|null
     */
    final public function getFormRoute(): ?string
    {
        return $this->formRoute;
    }

    /**
     * Sets the value of formRoute.
     *
     * @param string $route
     *
     * @return \Sixpaths\Platform\Symfony\Collection\AbstractCollection
     */
    final public function setFormRoute(string $route): AbstractCollection
    {
        $this->formRoute = $route;

        return $this;
    }

    /**
     * Gets the value of formClass.
     *
     * @return \Sixpaths\Platform\Symfony\Security\Validator\AbstractValidator|null
     */
    final public function getFormClass(): ?AbstractValidator
    {
        return $this->formClass;
    }

    /**
     * Gets the class name of the formClass.
     *
     * @return string|null
     */
    final public function getFormClassName(): ?string
    {
        if ($this->formClass instanceof AbstractValidator) {
            return get_class($this->formClass);
        }

        return null;
    }

    /**
     * Sets the value of formClass.
     *
     * @param \Sixpaths\Platform\Symfony\Security\Validator\AbstractValidator $validator
     *
     * @return \Sixpaths\Platform\Symfony\Collection\AbstractCollection
     */
    final public function setFormClass(AbstractValidator $validator): AbstractCollection
    {
        $this->formClass = $validator;

        return $this;
    }

    /**
     * Gets the value of page.
     *
     * @return integer|null
     */
    public function getPage(): ?int
    {
        return $this->page;
    }

    /**
     * Sets the value of page.
     *
     * @param integer $page
     *
     * @return \Sixpaths\Platform\Symfony\Collection\AbstractCollection
     */
    public function setPage(int $page): AbstractCollection
    {
        $this->page = $page;

        return $this;
    }

    /**
     * Gets the value of pages.
     *
     * @return integer|null
     */
    public function getPages(): ?int
    {
        return $this->pages;
    }

    /**
     * Gets the value of count.
     *
     * @return integer
     */
    public function getCount(): ?int
    {
        return $this->count;
    }

    /**
     * Sets the value of limit.
     *
     * @param integer $limit
     *
     * @return \Sixpaths\Platform\Symfony\Collection\AbstractCollection
     */
    public function setLimit(int $limit): AbstractCollection
    {
        $this->limit = $limit;

        if ($this->limit === 0) {
            $this->pages = 0;

            return $this;
        }

        $this->pages = (int) ceil($this->count / $this->limit);

        return $this;
    }

    /**
     * Sets the value of arguments.
     *
     * @param array<string, mixed>|null $arguments
     *
     * @return \Sixpaths\Platform\Symfony\Collection\AbstractCollection
     */
    final public function setArguments(array $arguments = null): AbstractCollection
    {
        $this->arguments = $arguments ?? [];

        return $this;
    }

    /**
     * Gets the value of items, after processing
     *
     * @return array<int, \Sixpaths\Platform\Symfony\Response\AbstractResponse>
     */
    final public function getItems(): array
    {
        $items = [];
        $iterator = $this->items->getIterator();

        foreach ($iterator as $offset => $item) {
            $items[] = $this[$offset];
        }

        return $items;
    }

    /**
     * {@inheritdoc}
     */
    final public function offsetExists($offset): bool
    {
        return isset($this[$offset]);
    }

    /**
     * @param int $offset
     *
     * @return \Sixpaths\Platform\Symfony\Response\AbstractResponse
     */
    final public function offsetGet($offset): AbstractResponse
    {
        if (!isset($this->cache[$offset])) {
            /** @var \Sixpaths\Platform\Symfony\Response\AbstractResponse */
            $wrapper = $this->getWrapper();

            $this->cache[$offset] = new $wrapper(
                $this->items->getIterator()[$offset],
                ...array_filter($this->arguments)
            );
        }

        return $this->cache[$offset];
    }

    /**
     * {@inheritdoc}
     */
    final public function offsetSet($offset, $value): void
    {
        throw new \Exception();
    }

    /**
     * {@inheritdoc}
     */
    final public function offsetUnset($offset): void
    {
        if (isset($this->cache[$offset])) {
            unset($this->cache[$offset]);
        }

        if (isset($this->items->getIterator()[$offset])) {
            unset($this->items->getIterator()[$offset]);
        }
    }

    /**
     * @param string $name
     * @param array<mixed> $arguments
     *
     * @return mixed
     */
    public function __call(
        string $name,
        array $arguments = []
    ) {
        foreach ($this->getItems() as $item) {
            $item->$name(...$arguments);
        }

        return $this;
    }
}
