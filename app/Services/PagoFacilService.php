<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class PagoFacilService
{
    protected string $baseUrl;
    protected ?string $secretToken;
    protected ?string $serviceToken;
    protected int $paymentMethodId;
    protected int $currency;
    protected ?string $callbackUrl;
    protected ?string $clientCallbackUrl;

 public function __construct()
    {
        $this->baseUrl           = rtrim(config('services.pagofacil.base_url'), '/');
        $this->secretToken       = config('services.pagofacil.secret_token');
        $this->serviceToken      = config('services.pagofacil.service_token');
        $this->paymentMethodId   = (int) config('services.pagofacil.payment_method_id', 4);
        $this->currency          = (int) config('services.pagofacil.currency', 2);
        $this->callbackUrl       = config('services.pagofacil.callback_url');
        $this->clientCallbackUrl = config('services.pagofacil.client_callback_url');
    }

    /**
     * Login a PagoFácil y devuelve el accessToken (Bearer).
     */
    public function getAccessToken(): string
    {
        $headers = [
            'tctokensecret'  => $this->secretToken,
            'tctokenservice' => $this->serviceToken,
            'Content-Type'   => 'application/json',
        ];

        $response = Http::withHeaders($headers)
            ->post($this->baseUrl . '/login');

        if (! $response->successful()) {
            Log::error('PagoFácil login HTTP error', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            throw new RuntimeException('No se pudo autenticar con PagoFácil (HTTP).');
        }

        $json = $response->json();

        if (!is_array($json) || ($json['error'] ?? 1) !== 0) {
            Log::error('PagoFácil login logical error', [
                'json' => $json,
            ]);
            throw new RuntimeException($json['message'] ?? 'No se pudo autenticar con PagoFácil.');
        }

        $token = $json['values']['accessToken'] ?? null;

        if (! $token) {
            Log::error('PagoFácil login: no accessToken en respuesta', ['json' => $json]);
            throw new RuntimeException('PagoFácil no devolvió accessToken.');
        }

        return $token;
    }

    /**
     * Generar QR para un pago de CLIENTE.
     *
     * Recibe todos los datos necesarios y devuelve el JSON completo de PagoFácil.
     *
     * @param  array  $data  [
     *   'paymentNumber' => string,
     *   'amount'        => float,
     *   'clientName'    => string,
     *   'email'         => string,
     *   'phone'         => string,
     *   'clientCode'    => string|int,
     *   'concepto'      => string,
     * ]
     *
     * @return array  JSON decodificado de PagoFácil (lo mismo que $response->json()).
     */
    public function generarQrCliente(array $data): array
    {
        $token = $this->getAccessToken();

        $callback = $this->clientCallbackUrl ?: $this->callbackUrl;

        $payload = [
            'paymentMethod' => $this->paymentMethodId,
            'clientName'    => $data['clientName'] ?? 'Cliente',
            'documentType'  => 1,
            'documentId'    => '0',
            'phoneNumber'   => $data['phone'] ?? '0',
            'email'         => $data['email'] ?? 'sin-correo@example.com',

            'paymentNumber' => (string) ($data['paymentNumber'] ?? ''),
            'amount'        => (float) ($data['amount'] ?? 0),
            'currency'      => $this->currency,
            'clientCode'    => (string) ($data['clientCode'] ?? ''),

            'callbackUrl'   => $callback,
            'tcUrlCallBack' => $callback,

            'orderDetail'   => [[
                'serial'   => 1,
                'product'  => $data['concepto'] ?? 'Pago de proyecto',
                'quantity' => 1,
                'price'    => (float) ($data['amount'] ?? 0),
                'discount' => 0,
                'total'    => (float) ($data['amount'] ?? 0),
            ]],
        ];

        $headers = [
            'Authorization'     => 'Bearer ' . $token,
            'Response-Language' => 'es',
            'Content-Type'      => 'application/json',
        ];

        $response = Http::withHeaders($headers)
            ->post($this->baseUrl . '/generate-qr', $payload);

        if (! $response->successful()) {
            Log::error('PagoFácil generate-qr HTTP error', [
                'status'   => $response->status(),
                'payload'  => $payload,
                'response' => $response->body(),
            ]);
            throw new RuntimeException('No se pudo generar el QR en PagoFácil (HTTP).');
        }

        $json = $response->json();

        if (!is_array($json) || ($json['error'] ?? 1) !== 0) {
            Log::error('PagoFácil generate-qr logical error', [
                'payload' => $payload,
                'json'    => $json,
            ]);
            throw new RuntimeException($json['message'] ?? 'No se pudo generar el QR en PagoFácil.');
        }

        return $json;
    }

    /**
     * Consultar una transacción (query-transaction) por transactionId.
     *
     * Devuelve el JSON crudo (igual al de PagoFácil).
     */
    public function consultarTransaccion(string $transactionId): array
    {
        $token = $this->getAccessToken();

        $headers = [
            'Authorization'     => 'Bearer ' . $token,
            'Response-Language' => 'es',
            'Content-Type'      => 'application/json',
        ];

        $body = [
            'pagofacilTransactionId' => $transactionId,
        ];

        $response = Http::withHeaders($headers)
            ->post($this->baseUrl . '/query-transaction', $body);

        if (! $response->successful()) {
            Log::error('PagoFácil query-transaction HTTP error', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            throw new RuntimeException('Error HTTP al consultar estado en PagoFácil.');
        }

        $json = $response->json();

        if (!is_array($json)) {
            Log::error('PagoFácil query-transaction non JSON', [
                'body' => $response->body(),
            ]);
            throw new RuntimeException('Respuesta inválida desde PagoFácil al consultar transacción.');
        }

        return $json;
    }

    /**
     * Helper opcional: determinar si una respuesta de query-transaction está pagada.
     */
    public function isPaidFromQuery(array $json): bool
    {
        $values = $json['values'] ?? [];

        $paymentStatus       = $values['paymentStatus']            ?? null;
        $paymentStatusDesc   = $values['paymentStatusDescription'] ?? null;
        $paymentStatusDescLc = strtolower((string) $paymentStatusDesc);

        return
            ((int) $paymentStatus === 2) ||
            str_contains($paymentStatusDescLc, 'pagado') ||
            str_contains($paymentStatusDescLc, 'aprob')  ||
            str_contains($paymentStatusDescLc, 'complet');
    }
}
