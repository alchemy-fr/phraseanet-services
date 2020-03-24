<?php

declare(strict_types=1);

namespace App\Serializer\Normalizer;

use App\Entity\Asset;
use App\Entity\Publication;
use App\Security\Voter\PublicationVoter;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Security;

class PublicationNormalizer extends AbstractRouterNormalizer
{
    /**
     * @var Security
     */
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * @param Publication $object
     */
    public function normalize($object, array &$context = [])
    {
        if (!$object->isEnabled() && !$this->security->isGranted(PublicationVoter::EDIT, $object)) {
            throw new NotFoundHttpException();
        }

        if (in_array(Publication::GROUP_PUB_READ, $context['groups'])) {
            $isAuthorized = $this->security->isGranted(PublicationVoter::READ, $object);
            $object->setAuthorized($isAuthorized);
            if (!$isAuthorized) {
                $context['groups'] = [Publication::GROUP_PUB_INDEX];
            }

            if ($this->security->isGranted(PublicationVoter::EDIT, $object)) {
                $context['groups'][] = Publication::GROUP_PUB_ADMIN_READ;
            }
        }

        $object->setChildren($object->getChildren()->filter(function (Publication $child): bool {
            return $child->isEnabled() || $this->security->isGranted(PublicationVoter::EDIT, $child);
        }));

        if ($object->getPackage() instanceof Asset) {
            $object->setPackageUrl($this->generateAssetUrl('asset_download', $object->getPackage()));
        }
        if ($object->getCover() instanceof Asset) {
            $object->setCoverUrl($this->generateAssetUrl('asset_thumbnail', $object->getCover()));
        }
    }

    public function support($object, $format): bool
    {
        return $object instanceof Publication;
    }
}
