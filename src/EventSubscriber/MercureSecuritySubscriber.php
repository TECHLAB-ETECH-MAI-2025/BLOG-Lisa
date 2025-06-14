<?php

namespace App\EventListener;

use App\Service\MercureTokenService;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class MercureSecuritySubscriber implements EventSubscriberInterface
{
    private MercureTokenService $jwtService;

    public function __construct(MercureTokenService $jwtService)
    {
        $this->jwtService = $jwtService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if (!str_contains($request->getPathInfo(), '/api/chat/')) {
            return;
        }

        $authHeader = $request->headers->get('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            throw new UnauthorizedHttpException('Bearer', 'Token JWT requis');
        }

        $token = substr($authHeader, 7);
        $payload = $this->jwtService->validateToken($token);

        if (!$payload) {
            throw new UnauthorizedHttpException('Bearer', 'Token JWT invalide');
        }

        $request->attributes->set('mercure_payload', $payload);
        $request->attributes->set('user_id', $payload['sub'] ?? null);
    }
}
