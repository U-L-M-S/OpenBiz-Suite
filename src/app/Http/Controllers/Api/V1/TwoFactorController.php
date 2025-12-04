<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class TwoFactorController extends Controller
{
    /**
     * Enable two factor authentication.
     */
    public function enable(Request $request)
    {
        $user = $request->user();
        $google2fa = new Google2FA();

        // Generate secret
        $secret = $google2fa->generateSecretKey();

        // Store encrypted secret
        $user->forceFill([
            'two_factor_secret' => encrypt($secret),
            'two_factor_recovery_codes' => encrypt(json_encode($user->generateRecoveryCodes()->toArray())),
        ])->save();

        // Generate QR Code
        $qrCodeUrl = $google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret
        );

        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);
        $qrCodeSvg = $writer->writeString($qrCodeUrl);

        return response()->json([
            'secret' => $secret,
            'qr_code' => $qrCodeSvg,
            'recovery_codes' => $user->recoveryCodes(),
        ]);
    }

    /**
     * Confirm two factor authentication.
     */
    public function confirm(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $user = $request->user();
        $google2fa = new Google2FA();

        $valid = $google2fa->verifyKey(
            decrypt($user->two_factor_secret),
            $request->code
        );

        if (! $valid) {
            return response()->json([
                'message' => 'The provided code is invalid.',
            ], 422);
        }

        $user->forceFill([
            'two_factor_confirmed_at' => now(),
        ])->save();

        return response()->json([
            'message' => 'Two factor authentication has been confirmed.',
        ]);
    }

    /**
     * Disable two factor authentication.
     */
    public function disable(Request $request)
    {
        $user = $request->user();

        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        return response()->json([
            'message' => 'Two factor authentication has been disabled.',
        ]);
    }

    /**
     * Verify two factor code during login.
     */
    public function verify(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'code' => 'required|string',
        ]);

        $user = \App\Models\User::findOrFail($request->user_id);
        $google2fa = new Google2FA();

        $valid = $google2fa->verifyKey(
            decrypt($user->two_factor_secret),
            $request->code
        );

        if (! $valid) {
            // Check if it's a recovery code
            $recoveryCodes = $user->recoveryCodes();

            if (in_array($request->code, $recoveryCodes)) {
                $user->replaceRecoveryCode($request->code);
            } else {
                return response()->json([
                    'message' => 'The provided code is invalid.',
                ], 422);
            }
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    /**
     * Get recovery codes.
     */
    public function recoveryCodes(Request $request)
    {
        return response()->json([
            'recovery_codes' => $request->user()->recoveryCodes(),
        ]);
    }

    /**
     * Regenerate recovery codes.
     */
    public function regenerateRecoveryCodes(Request $request)
    {
        $user = $request->user();

        $user->forceFill([
            'two_factor_recovery_codes' => encrypt(json_encode($user->generateRecoveryCodes()->toArray())),
        ])->save();

        return response()->json([
            'recovery_codes' => $user->recoveryCodes(),
        ]);
    }
}
