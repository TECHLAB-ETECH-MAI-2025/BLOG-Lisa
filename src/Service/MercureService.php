<?php

namespace App\Service;

use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

class MercureService 
{
    private HubInterface $hub;

    public function __construct(HubInterface $hub)
    {
        $this->hub = $hub;
    }

    /**
     * Publier une mise à jour vers un ou plusieurs topics
     *
     * @param string $topic   Le topic ou l'URI du topic
     * @param array  $data    Les données à publier
     * @param array  $targets Les topics privés (optionnel)
     * @param bool   $private Si la publication doit être privée
     */
    public function publish(string $topic, array $data, array $targets = [], bool $private = false): void
    {
        $update = new Update(
            $topic,
            json_encode($data, JSON_THROW_ON_ERROR),
            $targets,
            null,
            $private ? Update::PRIVATE : Update::PUBLIC
        );

        $this->hub->publish($update);
    }

    /**
     * Publier une notification utilisateur
     *
     * @param int    $userId  L'ID de l'utilisateur
     * @param string $message Le message de notification
     * @param string $type    Le type de notification (info, warning, error, success)
     */
    public function publishUserNotification(int $userId, string $message, string $type = 'info'): void
    {
        $this->publish(
            "/user/{$userId}/notifications",
            [
                'type' => $type,
                'message' => $message,
                'timestamp' => time(),
                'userId' => $userId
            ],
            ["/user/{$userId}"],
            true
        );
    }

    /**
     * Publier une mise à jour de chat
     *
     * @param int   $chatId      L'ID du chat
     * @param array $messageData Les données du message
     */
    public function publishChatMessage(int $chatId, array $messageData): void
    {
        $this->publish(
            "/chat/{$chatId}/messages",
            [
                'chatId' => $chatId,
                'message' => $messageData,
                'timestamp' => time()
            ]
        );
    }

    /**
     * Publier une mise à jour d'entité
     *
     * @param string $entityType Le type d'entité
     * @param int    $entityId   L'ID de l'entité
     * @param string $action     L'action effectuée (created, updated, deleted)
     * @param array  $data       Les données supplémentaires
     */
    public function publishEntityUpdate(string $entityType, int $entityId, string $action, array $data = []): void
    {
        $this->publish(
            "/entity/{$entityType}/{$entityId}",
            [
                'entityType' => $entityType,
                'entityId' => $entityId,
                'action' => $action,
                'data' => $data,
                'timestamp' => time()
            ]
        );
    }

    /**
     * Publier une mise à jour globale (pour tous les utilisateurs)
     *
     * @param string $eventType Le type d'événement
     * @param array  $data      Les données à publier
     */
    public function publishGlobalEvent(string $eventType, array $data): void
    {
        $this->publish(
            "/global/events",
            [
                'eventType' => $eventType,
                'data' => $data,
                'timestamp' => time()
            ]
        );
    }
}