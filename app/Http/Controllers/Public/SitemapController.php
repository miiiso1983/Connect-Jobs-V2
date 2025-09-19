<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Job;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $urls = [];
        // Static important pages
        $urls[] = [ 'loc' => url('/'), 'changefreq' => 'daily', 'priority' => '1.0' ];
        $urls[] = [ 'loc' => route('jobs.index'), 'changefreq' => 'hourly', 'priority' => '0.9' ];

        // Jobs (avoid timestamps entirely if table lacks them)
        $jobs = Job::query()
            ->select(['id'])
            ->where('approved_by_admin', true)
            ->where('status','open')
            ->orderByDesc('id')
            ->limit(5000)
            ->get();

        foreach ($jobs as $job) {
            $urls[] = [
                'loc' => route('jobs.show', $job),
                // 'lastmod' omitted if timestamps not available
                'changefreq' => 'daily',
                'priority' => '0.8',
            ];
        }

        $xml = view('public.sitemap.xml', compact('urls'))->render();
        return response($xml, 200)->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}

