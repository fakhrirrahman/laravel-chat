<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
class ChatBox extends Component
{
    public $messages;

    public function __construct($messages = null) // beri default null
    {
        $this->messages = $messages ?? collect(); // jika null, pakai collection kosong
    }

    public function render()
    {
        return view('components.chat-box');
    }
}