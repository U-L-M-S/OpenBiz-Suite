<?php

namespace App\Models\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

trait TwoFactorAuthenticatable
{
    /**
     * Determine if two-factor authentication has been enabled.
     */
    public function hasEnabledTwoFactorAuthentication(): bool
    {
        return ! is_null($this->two_factor_secret);
    }

    /**
     * Get the user's two factor authentication recovery codes.
     */
    public function recoveryCodes(): array
    {
        return json_decode(decrypt($this->two_factor_recovery_codes), true);
    }

    /**
     * Replace the given recovery code with a new one in the user's stored codes.
     */
    public function replaceRecoveryCode(string $code): void
    {
        $this->forceFill([
            'two_factor_recovery_codes' => encrypt(str_replace(
                $code,
                $this->generateRecoveryCode(),
                decrypt($this->two_factor_recovery_codes)
            )),
        ])->save();
    }

    /**
     * Generate a new two factor authentication recovery code.
     */
    protected function generateRecoveryCode(): string
    {
        return Str::random(10).'-'.Str::random(10);
    }

    /**
     * Generate fresh recovery codes for the user.
     */
    public function generateRecoveryCodes(): Collection
    {
        return Collection::times(8, function () {
            return $this->generateRecoveryCode();
        });
    }
}
