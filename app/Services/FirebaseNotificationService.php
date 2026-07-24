<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Exception\MessagingException;

class FirebaseNotificationService
{
    private $messaging;
    private $credentialsPath;

    public function __construct()
    {
        $this->credentialsPath = config('services.firebase.credentials_path', storage_path('app/firebase_credentials.json'));
        
        if (!file_exists($this->credentialsPath)) {
            Log::warning('Firebase credentials file not found. Firebase notifications will not work.', [
                'path' => $this->credentialsPath
            ]);
            return;
        }

        try {
            $factory = (new Factory)->withServiceAccount($this->credentialsPath);
            $this->messaging = $factory->createMessaging();
        } catch (\ParseError $e) { // Catch ParseError specifically for syntax issues in credentials
            Log::error('Firebase credentials file has a syntax error (ParseError)', [
                'error' => $e->getMessage(),
                'path' => $this->credentialsPath,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to initialize Firebase', [
                'error' => $e->getMessage(),
                'path' => $this->credentialsPath,
            ]);
        }
    }

    /**
     * 
     * @param string 
     * @param string 
     * @param string 
     * @param array 
     * @return array
     */
    public function sendNotification($token, $title, $body, $data = [])
    {
        if (!$this->messaging) {
            return [
                'success' => false,
                'message' => 'Firebase messaging is not initialized. Please check credentials file.'
            ];
        }

        if (empty($token)) {
            return [
                'success' => false,
                'message' => 'Firebase token is empty'
            ];
        }

        try {
            Log::info('Sending Firebase notification', [
                'token' => substr($token, 0, 20) . '...', 
                'title' => $title,
            ]);

            $notification = Notification::create($title, $body);

            $message = CloudMessage::new()
                ->withNotification($notification)
                ->withHighestPossiblePriority()
                ->toToken($token);

            if (!empty($data)) {
                $message = $message->withData($data);
            }

            $result = $this->messaging->send($message);

            Log::info('Firebase notification sent successfully', [
                'message_id' => $result,
            ]);

            return [
                'success' => true,
                'message' => 'Notification sent successfully',
                'message_id' => $result,
            ];

        } catch (MessagingException $e) {
            Log::error('Firebase messaging error', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to send notification: ' . $e->getMessage(),
                'code' => $e->getCode(),
            ];
        } catch (\Exception $e) {
            Log::error('Firebase notification error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Exception: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Send new order notification to branch
     * 
     * @param \App\Models\Order $order
     * @param string 
     * @return array
     */
    public function sendNewOrderNotification($order, $lang = 'ar')
    {
        $alert = $this->getOrderAlertMessage($lang);

        $device_data = [
            'action' => 'order',
            'title' => 'Run2diet App',
            'alert' => $alert,
            'order_id' => (string) $order->id,
        ];

        $title = 'Run2diet App';
        $body = $alert;

        return $this->sendNotification(
            $order->branch->firebase ?? '',
            $title,
            $body,
            $device_data
        );
    }

    /**
     * Get alert message based on language
     * 
     * @param string 
     * @return string
     */
    private function getOrderAlertMessage($lang = 'ar')
    {
        $messages = [
            'ar' => 'طلب جديد',
            'en' => 'New Order',
        ];

        return $messages[$lang] ?? $messages['ar'];
    }
}
