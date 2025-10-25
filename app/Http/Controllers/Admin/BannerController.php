<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class BannerController extends Controller
{
    protected $perPage = 10;

    /**
     * Display a listing of the resource with pagination.
     */
    public function index(Request $request)
    {
        $query = Banner::query();
        
        // Apply filters
        if ($request->has('is_active') && in_array($request->is_active, ['0', '1'])) {
            $query->where('is_active', $request->is_active);
        }
        
        if ($request->has('platform') && in_array($request->platform, ['all', 'android', 'ios', 'web'])) {
            $query->where('target_platform', $request->platform);
        }
        
        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        // Ordering
        $orderBy = $request->get('order_by', 'created_at');
        $orderDirection = $request->get('order_direction', 'desc');
        $query->orderBy($orderBy, $orderDirection);
        
        $banners = $query->paginate($request->get('per_page', $this->perPage));
        
        return response()->json([
            'success' => true,
            'data' => $banners->items(),
            'pagination' => [
                'total' => $banners->total(),
                'per_page' => $banners->perPage(),
                'current_page' => $banners->currentPage(),
                'last_page' => $banners->lastPage(),
                'from' => $banners->firstItem(),
                'to' => $banners->lastItem(),
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
            'url' => 'nullable|url',
            'is_active' => 'boolean',
            'display_order' => 'integer|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'target_platform' => 'required|in:all,android,ios,web',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = $request->except('image');
            
            // Handle file upload
            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('banners', 'public');
                $data['image'] = $path;
            }
            
            $banner = Banner::create($data);
            
            return response()->json([
                'success' => true,
                'message' => 'Banner created successfully',
                'data' => $banner
            ], 201);
            
        } catch (\Exception $e) {
            // Delete the uploaded file if there was an error
            if (isset($path)) {
                Storage::disk('public')->delete($path);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create banner',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Banner $banner)
    {
        return response()->json([
            'success' => true,
            'data' => $banner
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Banner $banner)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:5120',
            'url' => 'nullable|url',
            'is_active' => 'boolean',
            'display_order' => 'integer|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'target_platform' => 'sometimes|required|in:all,android,ios,web',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = $request->except('image');
            $oldImage = $banner->image;
            
            // Handle file upload if new image is provided
            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('banners', 'public');
                $data['image'] = $path;
                
                // Delete old image if it exists
                if ($oldImage && Storage::disk('public')->exists($oldImage)) {
                    Storage::disk('public')->delete($oldImage);
                }
            }
            
            $banner->update($data);
            
            return response()->json([
                'success' => true,
                'message' => 'Banner updated successfully',
                'data' => $banner
            ]);
            
        } catch (\Exception $e) {
            // Delete the new uploaded file if there was an error
            if (isset($path)) {
                Storage::disk('public')->delete($path);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update banner',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Banner $banner)
    {
        try {
            $imagePath = $banner->image;
            
            if ($banner->delete()) {
                // Delete the associated image file
                if ($imagePath && Storage::disk('public')->exists($imagePath)) {
                    Storage::disk('public')->delete($imagePath);
                }
                
                return response()->json([
                    'success' => true,
                    'message' => 'Banner deleted successfully'
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete banner'
            ], 500);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting banner',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Toggle banner active status
     */
    public function toggleStatus(Banner $banner)
    {
        try {
            $banner->update(['is_active' => !$banner->is_active]);
            
            return response()->json([
                'success' => true,
                'message' => 'Banner status updated successfully',
                'data' => $banner
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update banner status',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
