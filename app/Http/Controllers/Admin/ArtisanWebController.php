<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\File;

class ArtisanWebController extends Controller
{
    protected $adminEmail = 'dobanabil40@gmail.com';
    protected $storagePath;

    public function __construct()
    {
        $this->storagePath = storage_path('app/artisan_access.json');
    }

    public function showRequest()
    {
        return view('admin.artisan.request');
    }

    public function sendCode(Request $request)
    {
        if ($request->email !== $this->adminEmail) {
            return back()->with('error', 'Unauthorized email address.');
        }

        $code = rand(100000, 999999);
        $expiresAt = now()->addMinutes(15)->timestamp;

        File::put($this->storagePath, json_encode([
            'code' => $code,
            'expires_at' => $expiresAt
        ]));

        try {
            Mail::raw("Your Artisan Console access code is: $code. It expires in 15 minutes.", function ($message) {
                $message->to($this->adminEmail)
                    ->subject('Artisan Console Access Code');
            });
            return redirect()->route('admin.artisan.console')->with('success', 'Access code sent to your email.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to send email: ' . $e->getMessage());
        }
    }

    protected function checkAccess(Request $request)
    {
        if (!File::exists($this->storagePath)) {
            return false;
        }

        $data = json_decode(File::get($this->storagePath), true);
        $providedCode = $request->code ?: $request->query('code');

        if (!$providedCode || $providedCode != $data['code']) {
            return false;
        }

        if (now()->timestamp > $data['expires_at']) {
            return false;
        }

        return true;
    }

    public function showConsole(Request $request)
    {
        return view('admin.artisan.console');
    }

    public function execute(Request $request)
    {
        if (!$this->checkAccess($request)) {
            return redirect()->route('admin.artisan.console')->with('error', 'Invalid or expired access code.');
        }

        $request->validate([
            'command' => 'required|string',
            'code' => 'required|numeric'
        ]);
        
        $command = trim($request->command);
        
        // Auto-add --force for sensitive commands in production
        $protectedCommands = ['migrate', 'migrate:fresh', 'migrate:rollback', 'migrate:reset', 'db:seed'];
        $baseCommand = explode(' ', $command)[0];
        
        if (in_array($baseCommand, $protectedCommands) && !str_contains($command, '--force')) {
            $command .= ' --force';
        }

        try {
            Artisan::call($command);
            $output = Artisan::output();
            
            return view('admin.artisan.console', [
                'output' => $output,
                'exitCode' => 0,
                'lastCommand' => $command
            ]);
        } catch (\Exception $e) {
            return view('admin.artisan.console', [
                'output' => $e->getMessage(),
                'exitCode' => 1,
                'lastCommand' => $command
            ]);
        }
    }

    public function listLogs(Request $request)
    {
        $logPath = storage_path('logs');
        $files = File::glob($logPath . '/*.log');
        
        $logs = array_map(function($file) {
            return [
                'name' => basename($file),
                'size' => round(File::size($file) / 1024, 2) . ' KB',
                'modified' => date('Y-m-d H:i:s', File::lastModified($file))
            ];
        }, $files);

        usort($logs, function($a, $b) {
            return strcmp($b['modified'], $a['modified']);
        });

        return view('admin.logs.index', compact('logs'));
    }

    public function viewLog(Request $request, $filename)
    {
        $filePath = storage_path('logs/' . $filename);
        
        if (!File::exists($filePath)) {
            return back()->with('error', 'Log file not found.');
        }

        $content = File::get($filePath);
        
        return view('admin.logs.show', [
            'filename' => $filename,
            'content' => $content
        ]);
    }

    public function deleteLog(Request $request, $filename)
    {
        if (!$this->checkAccess($request)) {
            return redirect()->route('admin.artisan.logs.list')->with('error', 'Invalid or expired access code.');
        }

        $filePath = storage_path('logs/' . $filename);
        
        if (File::exists($filePath)) {
            File::delete($filePath);
            return redirect()->route('admin.artisan.logs.list', ['code' => $request->code])->with('success', "Log file $filename deleted.");
        }

        return back()->with('error', 'Log file not found.');
    }
}
