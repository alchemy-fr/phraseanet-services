<?php

declare(strict_types=1);

namespace App\Controller;

use Alchemy\ReportBundle\ReportUserService;
use App\Entity\Publication;
use App\Entity\PublicationAsset;
use App\Report\ExposeLogActionInterface;
use App\Security\AssetUrlGenerator;
use App\Security\Voter\PublicationVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/publications/{publicationId}/assets/{assetId}/download", name="download_asset")
 */
final class DownloadAssetAction extends AbstractController
{
    private EntityManagerInterface $em;
    private ReportUserService $reportClient;
    private AssetUrlGenerator $assetUrlGenerator;

    public function __construct(EntityManagerInterface $em, ReportUserService $reportClient, AssetUrlGenerator $assetUrlGenerator)
    {
        $this->em = $em;
        $this->reportClient = $reportClient;
        $this->assetUrlGenerator = $assetUrlGenerator;
    }

    public function __invoke(string $publicationId, string $assetId, Request $request): RedirectResponse
    {
        /** @var Publication|null $publication */
        $publication = $this->em
            ->getRepository(Publication::class)
            ->find($publicationId);

        if (!$publication instanceof Publication) {
            throw new NotFoundHttpException();
        }

        $this->denyAccessUnlessGranted(PublicationVoter::READ_DETAILS, $publication);

        $publicationAsset = $this->em->getRepository(PublicationAsset::class)
            ->findOneBy([
                'publication' => $publication->getId(),
                'asset' => $assetId,
            ]);

        if (!$publicationAsset instanceof PublicationAsset) {
            throw new NotFoundHttpException('PublicationAsset not found');
        }

        $asset = $publicationAsset->getAsset();

        $this->reportClient->pushHttpRequestLog(
            $request,
            ExposeLogActionInterface::ASSET_DOWNLOAD,
            $asset->getId(),
            [
                'publicationId' => $publication->getId(),
                'publicationTitle' => $publication->getTitle(),
                'assetTitle' => $asset->getTitle(),
            ]
        );

        return new RedirectResponse($this->assetUrlGenerator->generateAssetUrl($asset, true));
    }
}
