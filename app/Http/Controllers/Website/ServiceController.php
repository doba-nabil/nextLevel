<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\UserSubscription;
use App\Services\UserService;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function allServices()
    {
        $type = request('type');
        $title = match ($type) {
            'package' => 'الباقات',
            'service' => 'الخدمات',
            'question' => 'اختبار التوافق',
            default => 'المنتجات والخدمات',
        };
        $services = Service::when($type, fn($query) => $query->where('type', $type))->get();
        $package = Service::orderByDesc('price')->where('type','package')->first();
        $package_subcription = UserSubscription::where('id',$package->id)->where('type', 'package')->where('user_id', auth('web')->id())->first();
        $done_test = UserSubscription::where('type', 'question')->where('user_id', auth('web')->id())->first();
        return view('website.services.services', compact('services', 'title', 'package_subcription', 'done_test'));
    }

    public function pay_service($type, $id)
    {
        $title = match ($type) {
            'package' => 'الباقة',
            'service' => 'الخدمة',
            'question' => 'اختبار التوافق',
            default => 'الخدمة',
        };
        $service = Service::where([['type', $type], ['id', $id]])->first();
        return view('website.services.cart', compact('service', 'title'));
    }

    public function payment_success($type, $id)
    {
        $service = Service::where([['type', $type], ['id', $id]])->first();
        UserSubscription::create([
            'user_id' => auth('web')->id(),
            'service_id' => $id,
            'price' => $service->price,
            'type' => $service->type,
        ]);
        return redirect()->route('website.home')->withSuccess('تم الدفع بنجاح');
    }

    public function payments()
    {
        $subscriptions = UserService::where('user_id', auth('web')->id())->orderBy('id', 'desc')->get();
        return view('website.services.subscriptions', compact('subscriptions'));
    }
}
