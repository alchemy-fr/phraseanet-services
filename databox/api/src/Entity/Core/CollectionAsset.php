<?php

declare(strict_types=1);

namespace App\Entity\Core;

use App\Entity\AbstractUuidEntity;
use App\Entity\Traits\CreatedAtTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class CollectionAsset extends AbstractUuidEntity
{
    use CreatedAtTrait;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Core\Collection", inversedBy="assets")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?Collection $collection = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Core\Asset", inversedBy="collections")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?Asset $asset = null;

    public function getCollection(): Collection
    {
        return $this->collection;
    }

    public function setCollection(Collection $collection): void
    {
        $this->collection = $collection;
    }

    public function getAsset(): Asset
    {
        return $this->asset;
    }

    public function setAsset(Asset $asset): void
    {
        $this->asset = $asset;
    }
}
