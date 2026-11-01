<?php

namespace App\Services\Sms\Contracts;

interface SmsProviderInterface
{
    /**
     * Send a single SMS to one or more comma-separated numbers.
     *
     * @param string $number Recipient phone number (8801XXXXXXXXX)
     * @param string $message Text message body
     * @param string|null $senderId Optional approved Sender ID
     * @return array Standardized response array with ['status', 'message', 'data']
     */
    public function sendSingle(string $number, string $message, ?string $senderId = null): array;

    /**
     * Send bulk personalized messages in a single batch request.
     *
     * @param array $messageParameters Array of recipient arrays: [['Number' => '...', 'Text' => '...']]
     * @param string|null $senderId Optional approved Sender ID
     * @return array Standardized response array with ['status', 'message', 'data']
     */
    public function sendBulk(array $messageParameters, ?string $senderId = null): array;

    /**
     * Check available gateway balance and estimated remaining SMS count.
     *
     * @return array Standardized response array with ['status', 'message', 'data']
     */
    public function checkBalance(): array;

    /**
     * Look up the delivery status of a sent message by its gateway message ID.
     *
     * @param string $messageId Gateway assigned message ID
     * @return array Standardized response array with ['status', 'message', 'data']
     */
    public function checkDeliveryStatus(string $messageId): array;

    /**
     * Request an Identity Managed OTP code (Gateway generates and sends code).
     *
     * @param string $number Recipient phone number (8801XXXXXXXXX)
     * @return array Standardized response array with ['status', 'message', 'data']
     */
    public function requestIdentityOtp(string $number): array;

    /**
     * Verify an Identity Managed OTP code with the gateway.
     *
     * @param string $number Recipient phone number (8801XXXXXXXXX)
     * @param string $code The 4-digit code entered by user
     * @return array Standardized response array with ['status', 'message', 'data']
     */
    public function verifyIdentityOtp(string $number, string $code): array;

    /**
     * Dispatch custom OTP text with automatic Sender ID rotation and cooldown logic.
     *
     * @param string $number Recipient phone number (8801XXXXXXXXX)
     * @param string $message Full text message including generated OTP code
     * @return array Standardized response array with ['status', 'message', 'data']
     */
    public function sendCustomOtp(string $number, string $message): array;
}