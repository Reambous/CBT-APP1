<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;

class ContactController extends Controller
{
    public function index()
    {
        // Data kontak bisa ditaruh di sini agar View tetap bersih
        $contactData = [
            'whatsapp' => '6285800283579',
            'instagram' => 'cbt_app_official',
            'email' => 'support@cbtapp.com',
            'telegram' => 'cbt_admin_group'
        ];

        return view('user.contact', compact('contactData'));
    }
}
