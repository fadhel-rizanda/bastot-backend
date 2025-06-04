<?php

namespace App\Http\Middleware;

use App\Models\game\Team;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class isTeamOwner
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $userId = $request->user()->id;
        $teamId = $request->route("teamId");
        $team = Team::find($teamId);
        if (!$team || $team->team_owner_id != $userId) {
            return response()->json([
                'message' => 'You must be the team owner to access this resource.',
            ], 403);
        }
        return $next($request);
    }
}
