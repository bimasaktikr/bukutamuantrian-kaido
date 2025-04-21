<?php

namespace App\Services;

use App\Models\University;
use Illuminate\Http\Request;


class UniversityService
{
    public function getUniversities()
    {
        return University::all();
    }

    public function searchUniversity(Request $request)
    {
        $query = $request->get('query');
        $universities = University::where('name', 'LIKE', "%{$query}%")->get();
        return response()->json($universities);
    }
    public function storeUniversity(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:universities,name',
        ]);

        $university = University::create($validated);
        return response()->json($university);
    }
}

