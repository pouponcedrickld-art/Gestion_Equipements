<?php
namespace App\Services;
use PragmaRX\Google2FA\Google2FA;

class Auth2FAService
{
    private static $google2fa;

    private static function getInstance(): Google2FA
    {
        if (!self::$google2fa) {
            self::$google2fa = new Google2FA();
        }
        return self::$google2fa;
    }

    public static function generateSecret(): string
    {
        return self::getInstance()->generateSecretKey();
    }

    public static function getQRCodeUrl(string $company, string $email, string $secret): string
    {
        return self::getInstance()->getQRCodeUrl($company, $email, $secret);
    }

    public static function verifyCode(string $secret, string $code): bool
    {
        return self::getInstance()->verifyKey($secret, $code);
    }

    public static function generateRecoveryCodes(int $count = 8): array
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = bin2hex(random_bytes(4));
        }
        return $codes;
    }
}
