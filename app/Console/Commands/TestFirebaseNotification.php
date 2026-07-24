<?php

namespace App\Console\Commands;

use App\Models\Branch;
use App\Models\Order;
use App\Services\FirebaseNotificationService;
use Illuminate\Console\Command;

class TestFirebaseNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'firebase:test 
                            {--token= : Firebase device token to send test notification to}
                            {--branch= : Branch ID to send notification to}
                            {--order= : Order ID to send new order notification}
                            {--lang=ar : Language for notification (ar/en)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Firebase notification sending';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔥 Testing Firebase Notifications...');
        $this->newLine();

        $firebaseService = new FirebaseNotificationService();

        // Option 1: Send to specific token
        if ($token = $this->option('token')) {
            $this->info("Sending test notification to token: " . substr($token, 0, 20) . "...");
            $lang = $this->option('lang') ?? 'ar';
            $messages = $this->getMessages($lang);
            
            $result = $firebaseService->sendNotification(
                $token,
                $messages['title'],
                $messages['body'],
                ['type' => 'test', 'message' => 'This is a test notification']
            );

            if ($result['success']) {
                $this->info('✅ Notification sent successfully!');
                $this->info('Message ID: ' . ($result['message_id'] ?? 'N/A'));
            } else {
                $this->error('❌ Failed to send notification: ' . $result['message']);
            }
            return 0;
        }

        // Option 2: Send to branch
        if ($branchId = $this->option('branch')) {
            $branch = Branch::find($branchId);
            if (!$branch) {
                $this->error("Branch with ID {$branchId} not found!");
                return 1;
            }

            if (empty($branch->firebase)) {
                $this->error("Branch '{$branch->name}' does not have a Firebase token!");
                $this->warn('Please make sure the branch device is connected and has sent its Firebase token.');
                return 1;
            }

            $this->info("Sending test notification to branch: {$branch->name}");
            $lang = $branch->lang ?? $this->option('lang') ?? 'ar';
            $messages = $this->getMessages($lang);

            $result = $firebaseService->sendNotification(
                $branch->firebase,
                $messages['title'],
                $messages['body'],
                ['type' => 'test', 'message' => 'This is a test notification from admin']
            );

            if ($result['success']) {
                $this->info('✅ Notification sent successfully!');
                $this->info('Message ID: ' . ($result['message_id'] ?? 'N/A'));
                $this->info("Branch Language: {$lang}");
            } else {
                $this->error('❌ Failed to send notification: ' . $result['message']);
            }
            return 0;
        }

        // Option 3: Send new order notification using existing order
        if ($orderId = $this->option('order')) {
            $order = Order::with('branch')->find($orderId);
            if (!$order) {
                $this->error("Order with ID {$orderId} not found!");
                return 1;
            }

            if (!$order->branch) {
                $this->error("Order #{$order->order_number} does not have a branch!");
                return 1;
            }

            if (empty($order->branch->firebase)) {
                $this->error("Branch '{$order->branch->name}' does not have a Firebase token!");
                return 1;
            }

            $this->info("Sending new order notification for Order #{$order->order_number}");
            $lang = $order->branch->lang ?? 'ar';

            $result = $firebaseService->sendNewOrderNotification($order, $lang);

            if ($result['success']) {
                $this->info('✅ Notification sent successfully!');
                $this->info('Message ID: ' . ($result['message_id'] ?? 'N/A'));
                $this->info("Order: {$order->order_number}");
                $this->info("Branch: {$order->branch->name}");
                $this->info("Language: {$lang}");
            } else {
                $this->error('❌ Failed to send notification: ' . $result['message']);
            }
            return 0;
        }

        // Show help if no options provided
        $this->warn('No options provided. Please use one of the following:');
        $this->newLine();
        $this->line('1. Send to specific token:');
        $this->line('   php artisan firebase:test --token="YOUR_FIREBASE_TOKEN" --lang=ar');
        $this->newLine();
        $this->line('2. Send to branch:');
        $this->line('   php artisan firebase:test --branch=1 --lang=ar');
        $this->newLine();
        $this->line('3. Send new order notification:');
        $this->line('   php artisan firebase:test --order=1');
        $this->newLine();
        $this->line('List of branches with Firebase tokens:');
        $this->newLine();
        
        $branches = Branch::whereNotNull('firebase')->where('firebase', '!=', '')->get(['id', 'name', 'lang', 'firebase']);
        
        if ($branches->isEmpty()) {
            $this->warn('No branches found with Firebase tokens.');
        } else {
            $headers = ['ID', 'Name', 'Language', 'Token (first 30 chars)'];
            $data = $branches->map(function ($branch) {
                return [
                    $branch->id,
                    is_array($branch->name) ? ($branch->name['ar'] ?? $branch->name['en'] ?? 'N/A') : $branch->name,
                    $branch->lang ?? 'ar',
                    substr($branch->firebase, 0, 30) . '...',
                ];
            })->toArray();
            
            $this->table($headers, $data);
        }

        return 0;
    }

    /**
     * Get notification messages based on language
     */
    private function getMessages($lang = 'ar')
    {
        $messages = [
            'ar' => [
                'title' => 'إشعار تجريبي 🔔',
                'body' => 'هذا إشعار تجريبي من نظام run2diet',
            ],
            'en' => [
                'title' => 'Test Notification 🔔',
                'body' => 'This is a test notification from run2diet system',
            ],
        ];

        return $messages[$lang] ?? $messages['ar'];
    }
}
