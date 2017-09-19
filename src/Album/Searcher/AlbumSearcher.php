<?php

namespace Shopware\Album\Searcher;

use Doctrine\DBAL\Connection;
use Shopware\Album\Factory\AlbumDetailFactory;
use Shopware\Album\Loader\AlbumBasicLoader;
use Shopware\Context\Struct\TranslationContext;
use Shopware\Search\Criteria;
use Shopware\Search\Parser\SqlParser;
use Shopware\Search\QueryBuilder;
use Shopware\Search\Searcher;
use Shopware\Search\SearchResultInterface;
use Shopware\Search\UuidSearchResult;

class AlbumSearcher extends Searcher
{
    /**
     * @var AlbumDetailFactory
     */
    private $factory;

    /**
     * @var AlbumBasicLoader
     */
    private $loader;

    public function __construct(Connection $connection, SqlParser $parser, AlbumDetailFactory $factory, AlbumBasicLoader $loader)
    {
        parent::__construct($connection, $parser);
        $this->factory = $factory;
        $this->loader = $loader;
    }

    protected function createQuery(Criteria $criteria, TranslationContext $context): QueryBuilder
    {
        return $this->factory->createSearchQuery($criteria, $context);
    }

    protected function load(UuidSearchResult $uuidResult, TranslationContext $context): SearchResultInterface
    {
        $collection = $this->loader->load($uuidResult->getUuids(), $context);

        $result = new AlbumSearchResult($collection->getElements());

        $result->setTotal($uuidResult->getTotal());

        return $result;
    }
}
