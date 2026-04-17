<?php

namespace App\Controllers;

class Guide extends BaseController
{
    public function index()
    {
        $data = [
            'title' => 'Hướng dẫn sử dụng'
        ];
        
        return view('guide/index', $data);
    }
}
