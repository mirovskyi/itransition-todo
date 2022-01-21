<?php

declare(strict_types=1);

namespace App\Tests\Api;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\Client;

abstract class AbstractApiTest extends ApiTestCase
{
    /**
     * Available test users
     */
    protected const USER1 = 'test1@todo.io';
    protected const USER2 = 'test2@todo.io';

    /**
     * Users auth token store
     * @var array<string,string>
     */
    private static array $usersToken = [];

    protected static function getAuthToken(string $user): string
    {
        if (!isset(self::$usersToken[$user])) {
            $client = self::createClient();
            $response = $client->request('POST', '/authentication_token', [
                'headers' => ['Content-Type' => 'application/json'],
                'json' => [
                    'email' => $user,
                    'password' => '12345678',
                ],
            ]);
            if ($response->getStatusCode() !== 200) {
                self::fail('Failed to get JWT token for user ' . $user);
            }
            $json = $response->toArray();
            self::$usersToken[$user] = $json['token'];
        }

        return self::$usersToken[$user] ?? '';
    }

    protected function getOptionsWithAuth(string $user, string $contentType = 'application/json-ld'): array
    {
        return ['headers' => [
            'Content-Type' => $contentType,
            'Authorization' => 'Bearer ' . $this->getAuthToken($user)
        ]];
    }

    protected static function createAuthClient(string $user): Client
    {
        return self::createClient([], [
            'headers' => [
                'Authorization' => 'Bearer ' . self::getAuthToken($user),
                'Content-Type' => 'application/ld+json'
            ]
        ]);
    }
}
