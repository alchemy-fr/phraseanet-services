<?php

declare(strict_types=1);

namespace App\Controller;

use App\Mail\MailerRabbitProxy;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/", methods={"POST"})
 */
class ApiController extends AbstractController
{
    /**
     * @Route("/send-email")
     */
    public function sendEmail(Request $request, MailerRabbitProxy $mailerRabbitProxy)
    {
        $mailerRabbitProxy->sendEmail($request);

        return new JsonResponse(true);
    }

    /**
     * @Route("/notify-user")
     */
    public function notifyUser(Request $request, MailerRabbitProxy $mailerRabbitProxy)
    {
        $mailerRabbitProxy->notifyUser($request);

        return new JsonResponse(true);
    }

    /**
     * @Route("/notify-topic/{topic}")
     */
    public function notifyTopic(string $topic, Request $request, MailerRabbitProxy $mailerRabbitProxy)
    {
        $mailerRabbitProxy->notifyTopic($topic, $request);

        return new JsonResponse(true);
    }

    /**
     * @Route("/register-user")
     */
    public function registerUser(Request $request, MailerRabbitProxy $mailerRabbitProxy)
    {
        $mailerRabbitProxy->registerUser($request);

        return new JsonResponse(true);
    }

    /**
     * @Route("/delete-user")
     */
    public function deleteUser(Request $request, MailerRabbitProxy $mailerRabbitProxy)
    {
        $mailerRabbitProxy->deleteUser($request);

        return new JsonResponse(true);
    }
}
