<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class ChatbotPageController extends Controller
{
    public function index()
    {
        return view('admin.chatbot.index');
    }
}
