<?php

namespace Ecommerce\Hal\Plugin;

use ArrayObject;
use Countable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\PersistentCollection;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\Hydrator\ExtractionInterface;
use Zend\Hydrator\HydratorPluginManager;
use Zend\Mvc\Controller\Plugin\PluginInterface as ControllerPluginInterface;
use Zend\Paginator\Paginator;
use Zend\Stdlib\DispatchableInterface;
use Zend\View\Helper\AbstractHelper;
use Zend\View\Helper\ServerUrl;
use Zend\View\Helper\Url;
use ZF\ApiProblem\ApiProblem;
use ZF\Hal\Collection;
use ZF\Hal\Entity;
use ZF\Hal\EntityHydratorManager;
use ZF\Hal\Extractor\EntityExtractor;
use ZF\Hal\Exception;
use ZF\Hal\Extractor\LinkCollectionExtractorInterface;
use ZF\Hal\Link\Link;
use ZF\Hal\Link\LinkCollection;
use ZF\Hal\Link\LinkCollectionAwareInterface;
use ZF\Hal\Link\PaginationInjector;
use ZF\Hal\Link\PaginationInjectorInterface;
use ZF\Hal\Metadata\Metadata;
use ZF\Hal\Metadata\MetadataMap;
use ZF\Hal\Resource;
use ZF\Hal\ResourceFactory;

class Hal extends \ZF\Hal\Plugin\Hal
{
    /**
     * Render an individual entity
     *
     * Creates a hash representation of the Entity. The entity is first
     * converted to an array, and its associated links are injected as the
     * "_links" member. If any members of the entity are themselves
     * Entity objects, they are extracted into an "_embedded" hash.
     *
     * @param  Entity $halEntity
     * @param  bool $renderEntity
     * @param  int $depth           depth of the current rendering recursion
     * @param  int $maxDepth        maximum rendering depth for the current metadata
     * @throws Exception\CircularReferenceException
     * @return array
     */
    public function renderEntity(Entity $halEntity, $renderEntity = true, $depth = 0, $maxDepth = null)
    {
        $this->getEventManager()->trigger(__FUNCTION__, $this, ['entity' => $halEntity]);
        $entity      = $halEntity->entity;
        $entityLinks = clone $halEntity->getLinks(); // Clone to prevent link duplication

        $metadataMap = $this->getMetadataMap();

        if (is_object($entity)) {
            if ($maxDepth === null && $metadataMap->has($entity)) {
                $maxDepth = $metadataMap->get($entity)->getMaxDepth();
            }

            if ($maxDepth === null) {
                $entityHash = spl_object_hash($entity);

                if (isset($this->entityHashStack[$entityHash])) {
                    // we need to clear the stack, as the exception may be caught and the plugin may be invoked again
                    $this->entityHashStack = [];
                    throw new Exception\CircularReferenceException(sprintf(
                        "Circular reference detected in '%s'. %s",
                        get_class($entity),
                        "Either set a 'max_depth' metadata attribute or remove the reference"
                    ));
                }

                $this->entityHashStack[$entityHash] = get_class($entity);
            }
        }

        if (! $renderEntity || ($maxDepth !== null && $depth > $maxDepth)) {
            $entity = [];
        }

        if (!is_array($entity)) {
            $entity = $this->getEntityExtractor()->extract($entity);
        }

        foreach ($entity as $key => $value) {
            if (is_object($value) && $metadataMap->has($value)) {
                $value = $this->getResourceFactory()->createEntityFromMetadata(
                    $value,
                    $metadataMap->get($value),
                    $this->getRenderEmbeddedEntities()
                );
            }

            if (($value instanceof PersistentCollection || $value instanceof ArrayCollection) && $value->count() > 0) {
                $resources = [];
                $route = null;

                foreach ($value as $member) {
                    $metadata = $metadataMap->get($member);

                    if (!$route) {
                        $route = $metadata->getRoute();
                        $resourceRoute = $metadata->getEntityRoute();
                        $routeOptions = $metadata->getRouteOptions();
                        $routeParams = $metadata->getRouteParams();
                    }

                    $resources[] = $member;
                }

                $collection = new Collection($resources);
                $collection->setEntityRoute($resourceRoute);
                $collection->setEntityRouteOptions($routeOptions);
                $collection->setEntityRouteParams($routeParams);

                $entity[$key] = $collection;
                $this->extractEmbeddedCollection($entity, $key, $collection, $depth + 1, $maxDepth);
            }

            if ($value instanceof Entity) {
                $this->extractEmbeddedEntity($entity, $key, $value, $depth + 1, $maxDepth);
            }
            if ($value instanceof Collection) {
                $this->extractEmbeddedCollection($entity, $key, $value, $depth + 1, $maxDepth);
            }
            if ($value instanceof Link) {
                // We have a link; add it to the entity if it's not already present.
                $entityLinks = $this->injectPropertyAsLink($value, $entityLinks);
                unset($entity[$key]);
            }
            if ($value instanceof LinkCollection) {
                foreach ($value as $link) {
                    $entityLinks = $this->injectPropertyAsLink($link, $entityLinks);
                }
                unset($entity[$key]);
            }
        }

        $halEntity->setLinks($entityLinks);
        $entity['_links'] = $this->fromResource($halEntity);

        $payload = new ArrayObject($entity);
        $this->getEventManager()->trigger(
            __FUNCTION__ . '.post',
            $this,
            ['payload' => $payload, 'entity' => $halEntity]
        );

        if (isset($entityHash)) {
            unset($this->entityHashStack[$entityHash]);
        }

        return $payload->getArrayCopy();
    }

    protected function extractCollection(Collection $halCollection, $depth = 0, $maxDepth = null)
    {
        $collection           = [];
        $events               = $this->getEventManager();
        $routeIdentifierName  = $halCollection->getRouteIdentifierName();
        $entityRoute          = $halCollection->getEntityRoute();
        $entityRouteParams    = $halCollection->getEntityRouteParams();
        $entityRouteOptions   = $halCollection->getEntityRouteOptions();
        $metadataMap          = $this->getMetadataMap();
        $entityMetadata       = null;

        foreach ($halCollection->getCollection() as $entity) {
            $eventParams = new ArrayObject([
                'collection'   => $halCollection,
                'entity'       => $entity,
                'resource'     => $entity,
                'route'        => $entityRoute,
                'routeParams'  => $entityRouteParams,
                'routeOptions' => $entityRouteOptions,
            ]);
            $events->trigger('renderCollection.resource', $this, $eventParams);
            $events->trigger('renderCollection.entity', $this, $eventParams);

            $entity = $eventParams['entity'];

            if (is_object($entity) && $metadataMap->has($entity)) {
                $entity = $this->getResourceFactory()->createEntityFromMetadata($entity, $metadataMap->get($entity));
            }

            if ($entity instanceof Entity) {
                // Depth does not increment at this level
                $collection[] = $this->renderEntity($entity, $this->getRenderCollections(), $depth, $maxDepth);
                continue;
            }

            if (!is_array($entity)) {
                $entity = $this->getEntityExtractor()->extract($entity);
            }

            foreach ($entity as $key => $value) {
                if (is_object($value) && $metadataMap->has($value)) {
                    $value = $this->getResourceFactory()->createEntityFromMetadata($value, $metadataMap->get($value));
                }

                if ($value instanceof Entity) {
                    $this->extractEmbeddedEntity($entity, $key, $value, $depth + 1, $maxDepth);
                }

                if ($value instanceof Collection) {
                    $this->extractEmbeddedCollection($entity, $key, $value, $depth + 1, $maxDepth);
                }

                if (($value instanceof PersistentCollection || $value instanceof ArrayCollection) && $value->count() > 0) {
                    $resources = [];
                    $route = null;

                    foreach ($value as $member) {
                        $metadata = $metadataMap->get($member);

                        if (!$route) {
                            $route = $metadata->getRoute();
                            $resourceRoute = $metadata->getEntityRoute();
                            $routeOptions = $metadata->getRouteOptions();
                            $routeParams = $metadata->getRouteParams();
                        }

                        $resources[] = $member;
                    }

                    $tmpCollection = new Collection($resources);
                    $tmpCollection->setEntityRoute($resourceRoute);
                    $tmpCollection->setEntityRouteOptions($routeOptions);
                    $tmpCollection->setEntityRouteParams($routeParams);

                    $entity[$key] = $tmpCollection;
                    $this->extractEmbeddedCollection($entity, $key, $tmpCollection, $depth + 1, $maxDepth);
                }
            }

            $id = $this->getIdFromEntity($entity);

            if ($id === false) {
                // Cannot handle entities without an identifier
                // Return as-is
                $collection[] = $entity;
                continue;
            }

            if ($eventParams['entity'] instanceof LinkCollectionAwareInterface) {
                $links = $eventParams['entity']->getLinks();
            } else {
                $links = new LinkCollection();
            }

            if (isset($entity['links']) && $entity['links'] instanceof LinkCollection) {
                $links = $entity['links'];
            }

            /* $entity is always an array here. We don't have metadata config for arrays so the self link is forced
               by default (at the moment) and should be removed manually if not required. But at some point it should
               be discussed if it makes sense to force self links in this particular use-case.  */
            $selfLink = new Link('self');
            $selfLink->setRoute(
                $eventParams['route'],
                array_merge($eventParams['routeParams'], [$routeIdentifierName => $id]),
                $eventParams['routeOptions']
            );
            $links->add($selfLink);

            $entity['_links'] = $this->fromLinkCollection($links);

            $collection[] = $entity;
        }

        return $collection;
    }
}