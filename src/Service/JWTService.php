<?php

namespace App\Service;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class MercureTokenService
{
    private string $secret;

    public function __construct(string $mercureJwtSecret)
    {
        $this->secret = $mercureJwtSecret;
    }

    /**
     * Générer un token JWT pour Mercure
     */
    public function generateMercureToken(
        array $subscribe = [],
        array $publish = [],
        ?int $userId = null
    ): string {
        $payload = [
            'mercure' => [
                'subscribe' => $subscribe,
                'publish' => $publish
            ],
            'iat' => time(),
            'exp' => time() + 3600,
        ];

        if ($userId) {
            $payload['sub'] = (string) $userId;
        }

        return JWT::encode($payload, $this->secret, 'HS256');
    }

    /**
     * Valider un token JWT
     */
    public function validateToken(string $token): ?array
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secret, 'HS256'));
            return (array) $decoded;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Générer un token pour un utilisateur spécifique
     */
    public function generateUserToken(int $userId, array $additionalTopics = []): string
    {
        $userTopics = [
            "user/{$userId}",
            "user/{$userId}/notifications",
            "user/{$userId}/messages"
        ];

        $allTopics = array_merge($userTopics, $additionalTopics);

        return $this->generateMercureToken($allTopics, [], $userId);
    }

    /**
     * Générer un token pour un chat
     */
    public function generateChatToken(int $userId, int $chatId): string
    {
        return $this->generateMercureToken([
            "user/{$userId}",
            "chat/{$chatId}/messages",
            "chat/{$chatId}/typing"
        ], [], $userId);
    }
}
