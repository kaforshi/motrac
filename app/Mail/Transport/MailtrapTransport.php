<?php

namespace App\Mail\Transport;

use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\MessageConverter;
use Symfony\Component\Mime\Email;
use Illuminate\Support\Facades\Http;

class MailtrapTransport extends AbstractTransport
{
    protected $apiToken;
    protected $inboxId;
    protected $baseUrl;

    public function __construct($apiToken, $inboxId = null)
    {
        parent::__construct();
        $this->apiToken = $apiToken;
        $this->inboxId = $inboxId;
        $this->baseUrl = 'https://send.api.mailtrap.io';
    }

    protected function doSend(SentMessage $message): void
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());
        
        // Get from address
        $from = $email->getFrom()[0];
        $fromAddress = [
            'email' => $from->getAddress(),
            'name' => $from->getName(),
        ];
        
        // Get to addresses
        $toAddresses = [];
        foreach ($email->getTo() as $address) {
            $toAddresses[] = [
                'email' => $address->getAddress(),
                'name' => $address->getName(),
            ];
        }
        
        // Get CC addresses (if any)
        $ccAddresses = [];
        foreach ($email->getCc() as $address) {
            $ccAddresses[] = [
                'email' => $address->getAddress(),
                'name' => $address->getName(),
            ];
        }
        
        // Get BCC addresses (if any)
        $bccAddresses = [];
        foreach ($email->getBcc() as $address) {
            $bccAddresses[] = [
                'email' => $address->getAddress(),
                'name' => $address->getName(),
            ];
        }
        
        // Prepare payload for Mailtrap API
        $payload = [
            'from' => $fromAddress,
            'to' => $toAddresses,
            'subject' => $email->getSubject(),
        ];
        
        // Add CC if exists
        if (!empty($ccAddresses)) {
            $payload['cc'] = $ccAddresses;
        }
        
        // Add BCC if exists
        if (!empty($bccAddresses)) {
            $payload['bcc'] = $bccAddresses;
        }
        
        // Get HTML and text body
        $htmlBody = $email->getHtmlBody();
        $textBody = $email->getTextBody();
        
        if ($htmlBody) {
            $payload['html'] = $htmlBody;
        }
        
        if ($textBody) {
            $payload['text'] = $textBody;
        }
        
        // Add attachments if any
        $attachments = [];
        foreach ($email->getAttachments() as $attachment) {
            $attachments[] = [
                'content' => base64_encode($attachment->getBody()),
                'filename' => $attachment->getFilename(),
                'type' => $attachment->getContentType(),
                'disposition' => 'attachment',
            ];
        }
        
        if (!empty($attachments)) {
            $payload['attachments'] = $attachments;
        }
        
        // Add inbox_id if provided
        if ($this->inboxId) {
            $payload['inbox_id'] = $this->inboxId;
        }
        
        // Send to Mailtrap API
        $httpClient = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiToken,
            'Content-Type' => 'application/json',
        ]);
        
        // Disable SSL verification for development (Windows cURL issue)
        // In production, you should fix SSL certificate issue properly
        if (env('APP_ENV') === 'local' || env('APP_DEBUG') === true) {
            $httpClient = $httpClient->withoutVerifying();
        }
        
        $response = $httpClient->post($this->baseUrl . '/api/send', $payload);
        
        if (!$response->successful()) {
            $errorMessage = $response->json()['errors'] ?? $response->body();
            throw new \Exception('Mailtrap API error: ' . (is_array($errorMessage) ? json_encode($errorMessage) : $errorMessage));
        }
    }

    public function __toString(): string
    {
        return 'mailtrap';
    }
}

