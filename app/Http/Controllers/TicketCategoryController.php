<?php

namespace App\Http\Controllers;

use App\Models\TicketCategory;
use App\Models\TicketSubcategory;
use Illuminate\Http\Request;

class TicketCategoryController extends Controller
{
    public function index()
    {
        try {
            $categories = TicketCategory::where('is_active', true)
                ->with('subcategories')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $categories
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch categories: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getSubcategories($categoryId)
    {
        try {
            $subcategories = TicketSubcategory::where('category_id', $categoryId)
                ->where('is_active', true)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $subcategories
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch subcategories: ' . $e->getMessage()
            ], 500);
        }
    }
}
