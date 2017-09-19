<?php declare(strict_types=1);

namespace Shopware\Album\Struct;

use Shopware\Media\Struct\MediaBasicCollection;

class AlbumDetailCollection extends AlbumBasicCollection
{
    /**
     * @var AlbumDetailStruct[]
     */
    protected $elements = [];

    public function getMediaUuids(): array
    {
        $uuids = [];
        foreach ($this->elements as $element) {
            foreach ($element->getMediaUuids() as $uuid) {
                $uuids[] = $uuid;
            }
        }

        return $uuids;
    }

    public function getMedias(): MediaBasicCollection
    {
        $collection = new MediaBasicCollection();
        foreach ($this->elements as $element) {
            $collection->fill($element->getMedias()->getIterator()->getArrayCopy());
        }

        return $collection;
    }
}
