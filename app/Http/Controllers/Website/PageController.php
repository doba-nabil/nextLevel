<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Page;
use App\Models\Subscribe;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function show(string $slug)
    {
        $page = Page::where('slug', $slug)
            ->where('active', 1)
            ->firstOrFail();
        return view('website.pages.page', compact('page'));
    }

    public function page()
    {
        $type = request('type');
        switch($type) {
            case 'privacy':
                $title = 'Privacy Policy';
                $content = 'Here goes the privacy policy content.';
                break;

            case 'terms':
                $title = 'Terms & Conditions';
                $content = 'Here goes the terms and conditions content.';
                break;

            case 'deilvery':
                $title = 'Delivery Information';
                $content = 'Here goes the delivery information content.';
                break;

            default:
                $title = 'Page Not Found';
                $content = 'Sorry, the page you are looking for does not exist.';
                break;
        }
        return view('website.pages.page', compact('title','content'));
    }

    public function contact()
    {
        return view('website.pages.contact_page');
    }

    public function contact_post(Request $request)
    {
        Contact::create([
            'name' => $request->first_name .' '. $request->last_name,
            'phone' => $request->phone,
            'email' => $request->email,
            'message' => $request->message
        ]);
        return redirect()->back()->withSuccess('تم ارسال اقتراحك وسيتم مراجعتة شكراً لكـ');
    }
}
