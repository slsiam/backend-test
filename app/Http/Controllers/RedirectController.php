<?php

namespace App\Http\Controllers;

use App\Models\Redirect;
use App\Http\Requests\RedirectRequest;
use App\Http\Requests\RedirectUpdateRequest;
use Illuminate\Http\Request;
use App\Services\UrlBuilderService;

class RedirectController extends Controller
{
    public function index()
    {
        $redirects = Redirect::select('id', 'active', 'url', 'created_at', 'updated_at')
            ->with([
                'redirectLogs' => function ($query) {
                    $query->latest()->limit(1);
                }
            ])
            ->latest()
            ->get();

        return response()->json($redirects, 200);
    }

    public function store(RedirectRequest $request)
    {
        $redirect = Redirect::create(['url' => $request->input('url')]);
        return response()->json($redirect, 201);
    }

    public function update(RedirectUpdateRequest $request, Redirect $redirect)
    {

        $redirect->update($request->all());

        return response()->json(['message' => 'Redirecionamento atualizado com sucesso.'], 200);


    }

    public function destroy(Redirect $redirect)
    {
        $redirect->delete();
        return response()->json(['message' => 'Redirecionamento deletado com sucesso.'], 200);

    }

    public function redirect(Request $request, Redirect $redirect)
    {
        if (!$redirect->active) {
            abort(404);
        }

        $redirect->redirectLogs()->create([
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referer' => $request->header('referer'),
            'query_params' => $request->getQueryString()
        ]);

        $destinationUrl = UrlBuilderService::buildUrl($request->query(), $redirect->url);

        return redirect()->away($destinationUrl);
    }

    public function stats(Request $request, Redirect $redirect)
    {
        $logs = $redirect->redirectLogs;

        $totalAccesses = $logs->count();

        $uniqueAccesses = $logs->unique('ip')->count();

        $topReferrers = $logs->groupBy('referer')
            ->map(function ($group) {
                return [
                    'referer' => $group->first()->referer,
                    'count' => $group->count()
                ];
            })
            ->sortByDesc('count')
            ->take(5);

        $last10Days = $logs->where('created_at', '>=', now()->subDays(10))
            ->groupBy(function ($log) {
                return $log->created_at->format('Y-m-d');
            })
            ->map(function ($logs) {
                $uniqueCount = $logs->unique('ip')->count();
                return [
                    'date' => $logs->first()->created_at->format('Y-m-d'),
                    'total' => $logs->count(),
                    'unique' => $uniqueCount
                ];
            });

        return response()->json([
            'total_accesses' => $totalAccesses,
            'unique_accesses' => $uniqueAccesses,
            'top_referrers' => $topReferrers->values(),
            'last_10_days' => $last10Days->values(),
        ], 200);
    }
    
    public function logs(Request $request, Redirect $redirect)
    {
        $logs = $redirect->redirectLogs;
        return response()->json($logs, 200);
    }

}
