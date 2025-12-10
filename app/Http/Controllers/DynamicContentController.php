<?php

namespace App\Http\Controllers;

use App\Models\DynamicContent;
use Illuminate\Http\Request;

class DynamicContentController extends Controller
{

    public function index(Request $request)
    {
        $query = DynamicContent::query(); 
        $datas = $query->get();
        return success_response($datas);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'status' => 'boolean',
        ]);
    
        // Generate slug 
     
        // Create or Update
        $dynamicPage = DynamicContent::updateOrCreate(
            ['title' => $request->title], 
            [ 
                'content' => $request->content,
                'status' => $request->status ?? true,
            ]
        );
    
        return success_response($dynamicPage);
    }
    
}
