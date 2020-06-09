<?php

declare(strict_types=1);

namespace App\Model;

use Symfony\Component\Serializer\Annotation\Groups;

class LayoutOptions extends AbstractOptions
{
    /**
     * Compatible with [gallery].
     *
     * @Groups({"profile:read", "publication:admin:read"})
     */
    private ?bool $displayMap = null;

    /**
     * Display pins on map instead of thumbnails
     * Compatible with [mapbox].
     *
     * @Groups({"profile:read", "publication:admin:read"})
     */
    private ?bool $displayMapPins = null;

    public function jsonSerialize()
    {
        return array_filter([
            'displayMap' => $this->displayMap,
            'displayMapPins' => $this->displayMapPins,
        ]);
    }

    public function fromJson(array $options = []): void
    {
        $this->displayMap = $options['displayMap'] ?? null;
        $this->displayMapPins = $options['displayMapPins'] ?? null;
    }

    public function isDisplayMap(): ?bool
    {
        return $this->displayMap;
    }

    public function setDisplayMap(?bool $displayMap): void
    {
        $this->displayMap = $displayMap;
    }

    public function getDisplayMapPins(): ?bool
    {
        return $this->displayMapPins;
    }

    public function setDisplayMapPins(?bool $displayMapPins): void
    {
        $this->displayMapPins = $displayMapPins;
    }
}
