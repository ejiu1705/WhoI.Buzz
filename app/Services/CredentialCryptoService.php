<?php

namespace App\Services;

use App\Models\IssuedCredential;
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\Output\QRMarkupSVG;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class CredentialCryptoService
{
    /**
     * Derive a deterministic Ed25519 keypair from APP_KEY so signatures stay verifiable across restarts.
     */
    private function keypair(): string
    {
        $appKey = (string) config('app.key', 'whoibuzz-fallback-key');
        $seed = hash('sha256', 'whoibuzz-ed25519-seed:'.$appKey, true);

        return sodium_crypto_sign_seed_keypair($seed);
    }

    public function canonicalJson(array $payload): string
    {
        ksort($payload);

        return json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }

    /**
     * Sign payload using Ed25519 (libsodium) + HMAC-SHA256 seal.
     */
    public function signPayload(array $payload): array
    {
        $canonical = $this->canonicalJson($payload);
        $hmac = hash_hmac('sha256', $canonical, (string) config('app.key'));

        if (function_exists('sodium_crypto_sign_detached')) {
            $kp = $this->keypair();
            $secret = sodium_crypto_sign_secretkey($kp);
            $edSig = sodium_crypto_sign_detached($canonical, $secret);

            return [
                'algorithm' => 'Ed25519+HMAC-SHA256',
                'signature' => base64_encode($edSig).'.'.$hmac,
            ];
        }

        return [
            'algorithm' => 'HMAC-SHA256',
            'signature' => $hmac,
        ];
    }

    /**
     * Verify cryptographic integrity of an IssuedCredential.
     */
    public function verifyCredential(IssuedCredential $credential): array
    {
        $canonical = $this->canonicalJson($credential->payload_json ?? []);
        $expectedHmac = hash_hmac('sha256', $canonical, (string) config('app.key'));

        $signatureValid = false;
        if (str_contains($credential->signature_hash, '.') && function_exists('sodium_crypto_sign_verify_detached')) {
            [$edB64, $hmacPart] = explode('.', $credential->signature_hash, 2);
            $edBin = base64_decode($edB64, true);
            $kp = $this->keypair();
            $pub = sodium_crypto_sign_publickey($kp);

            $edOk = $edBin !== false && strlen($edBin) === SODIUM_CRYPTO_SIGN_BYTES
                && sodium_crypto_sign_verify_detached($edBin, $canonical, $pub);
            $hmacOk = hash_equals($expectedHmac, $hmacPart);
            $signatureValid = $edOk && $hmacOk;
        } else {
            $signatureValid = hash_equals($expectedHmac, $credential->signature_hash);
        }

        return [
            'signature_valid' => $signatureValid,
            'is_revoked' => $credential->isRevoked(),
            'is_authentic' => $signatureValid && ! $credential->isRevoked(),
            'fingerprint' => strtoupper(substr(hash('sha256', $credential->signature_hash), 0, 20)),
        ];
    }

    /**
     * Render a crisp inline SVG QR code themed in WhoI.Buzz Espresso Bark (#4C2500).
     */
    public function renderQrSvg(string $url, int $scale = 5): string
    {
        $options = new QROptions([
            'outputInterface' => QRMarkupSVG::class,
            'eccLevel' => EccLevel::M,
            'scale' => $scale,
            'outputBase64' => false,
            'drawLightModules' => false,
            'svgAddXmlHeader' => false,
            'cssClass' => 'w-full h-full',
            'keepAsSquare' => [
                QRMatrix::M_FINDER_DARK,
                QRMatrix::M_FINDER_DOT,
                QRMatrix::M_ALIGNMENT_DARK,
            ],
            'moduleValues' => [
                QRMatrix::M_DATA_DARK => '#4C2500',
                QRMatrix::M_FINDER_DARK => '#4C2500',
                QRMatrix::M_FINDER_DOT => '#CD7000',
                QRMatrix::M_ALIGNMENT_DARK => '#4C2500',
                QRMatrix::M_TIMING_DARK => '#4C2500',
                QRMatrix::M_FORMAT_DARK => '#4C2500',
                QRMatrix::M_VERSION_DARK => '#4C2500',
            ],
        ]);

        return (new QRCode($options))->render($url);
    }
}
