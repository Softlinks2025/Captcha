<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class BannerController extends Controller
{
    protected $cacheDuration = 60; // 1 hour cache

    /**
     * Get active banners with pagination
     */
    public function index(Request $request)
    {
        $platform = $request->get('platform', 'all');
        $perPage = $request->get('per_page', 10);
        $page = $request->get('page', 1);
        
        $cacheKey = "banners_{$platform}_{$perPage}_{$page}";
        
        $banners = Cache::remember($cacheKey, $this->cacheDuration, function () use ($platform, $perPage) {
            return Banner::active()
                ->forPlatform($platform)
                ->ordered()
                ->paginate($perPage);
        });
        
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
     * Get a single banner by ID
     */
    public function show($id)
    {
        $banner = Cache::remember("banner_{$id}", $this->cacheDuration, function () use ($id) {
            return Banner::active()->findOrFail($id);
        });
        
        return response()->json([
            'success' => true,
            'data' => $banner
        ]);
    }
    
    /**
     * Get banners count
     */
    public function count(Request $request)
    {
        $platform = $request->get('platform', 'all');
        
        $count = Cache::remember("banners_count_{$platform}", $this->cacheDuration, function () use ($platform) {
            return Banner::active()
                ->forPlatform($platform)
                ->count();
        });
        
        return response()->json([
            'success' => true,
            'data' => [
                'count' => $count
            ]
        ]);
    }
}
