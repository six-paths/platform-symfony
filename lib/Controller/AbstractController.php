<?php

namespace Sixpaths\Platform\Symfony\Controller;

use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as REST;
use FOS\RestBundle\View\View;
use Sixpaths\Platform\Symfony\Collection\AbstractCollection;
use Sixpaths\Platform\Symfony\Filter\AbstractFilter;
use Sixpaths\Platform\Symfony\Interfaces\ResponseInterface;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractController extends AbstractFOSRestController
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @REST\Options
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function optionsAction(): Response
    {
        return new Response();
    }

    /**
     * Apply a serialization group to the collection and return it as a view
     *
     * @param \Sixpaths\Platform\Symfony\Collection\AbstractCollection $collection
     * @param \Sixpaths\Platform\Symfony\Filter\AbstractFilter|null $filter
     *
     * @return \FOS\RestBundle\View\View
     */
    public function getCollectionView(
        AbstractCollection $collection,
        AbstractFilter $filter = null
    ): View {
        $view = View::create($collection, Response::HTTP_OK);

        if ($filter instanceof AbstractFilter) {
            /** @var string */
            $datagroup = $filter->get('datagroup') ?? '';

            $view->setContext((new Context())->setGroups(['default',  $datagroup]));
        }

        return $view;
    }

    /**
     * Apply a serialization group to the response and return it as a view
     *
     * @param \Sixpaths\Platform\Symfony\Interfaces\ResponseInterface $response
     * @param int $statusCode
     *
     * @return \FOS\RestBundle\View\View
     */
    public function getDetailView(
        ResponseInterface $response,
        int $statusCode
    ): View {
        $view = View::create($response, $statusCode);
        $view->setContext((new Context())->setGroups(['default', 'details']));

        return $view;
    }
}
