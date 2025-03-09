<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Url;
use App\Models\UrlLog;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class UrlController extends Controller
{
    public function shorten(Request $request)
    {
        $request->validate([
            'url' => 'required|url',
            'alias'=> 'nullable|string|alpha_dash'
        ]);
        $originalUrl = $request->input('url');
        $alias = $request->input('alias');
        if ($alias){
            if (Url::where('alias', $alias)->exists()){
                return response()->json([
                    'message' => 'Alias already exists'
                ], 400);
            }
        }
        else{
            do{
                $alias = Str::random(6);
            } while (Url::where('alias', $alias)->exists());
        }
        $url = Url::create([
            'original_url' => $originalUrl,
            'alias' => $alias
        ]);
        $shortenedUrl = url($alias);

        return response()->json([
            'shortened_url' => $shortenedUrl,
            'original_url' => $originalUrl,
            'alias' => $alias
        ]);
    }

    public function redirect($alias)
    {
        $url = Cache::remember('url_' . $alias, 60, function() use ($alias) {
            return Url::where('alias', $alias)->first();
        });
        if (!$url){
            return response ()->json([
                'message' => 'URL not found'
            ], 404);
        }
        $url->increment('redirect_count');
        UrlLog::create([
            'url_id' => $url->id
        ]);
        return redirect()->away($url->original_url);
    }

    public function analytics($alias)
    {
        $url = Url::where('alias', $alias)->first();

        if (!$url){
            return response()->json([
                'message' => 'URL not found'
            ], 404);
        }
        $totalRedirects =$url->redirect_count;
        $dailystats = UrlLog ::selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->where('url_id', $url->id)
            ->groupBy('date')
            ->get();
        return response()->json([
            'original_url' => $url->original_url,
            'alias' => $url->alias,
            'total_redirects' => $totalRedirects,
            'daily_stats' => $dailystats
        ]);
    }
}



