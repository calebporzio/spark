<?php

namespace Laravel\Spark\Http\Controllers\API;

use Exception;
use Laravel\Spark\Spark;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Laravel\Spark\Repositories\TeamRepository;

class TeamController extends Controller
{
    /**
     * The team data repository.
     *
     * @var \Laravel\Spark\Repositories\TeamRepository
     */
    protected $teams;

    /**
     * Create a new controller instance.
     *
     * @param  \Laravel\Spark\Repositories\TeamRepository  $teams
     * @return void
     */
    public function __construct(TeamRepository $teams)
    {
        $this->teams = $teams;

        $this->middleware('auth', ['except' => [
            'getInvitation',
        ]]);
    }

    /**
     * Get the team for the given ID.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $teamId
     * @return \Illuminate\Http\Response
     */
    public function getTeam(Request $request, $teamId)
    {
        return $this->teams->getTeam($request->user(), $teamId);
    }

    /**
     * Get all of the teams for the user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getAllTeamsForUser(Request $request)
    {
        return $this->teams->getAllTeamsForUser($request->user());
    }

    /**
     * Get all of the pending invitations for the user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getPendingInvitationsForUser(Request $request)
    {
        return $this->teams->getPendingInvitationsForUser($request->user());
    }

    /**
     * Get the invitation for the given code.
     *
     * This is primarily used during new user registration.
     *
     * @param  string  $code
     * @return \Illuminate\Http\Response
     */
    public function getInvitation($code)
    {
        $model = config('auth.model');

        $model = get_class((new $model)->invitations()
                    ->getQuery()->getModel());

        $invitation = (new $model)->with('team.owner')
                    ->where('token', $code)->firstOrFail();

        if ($invitation->isExpired()) {
            $invitation->delete();

            abort(404);
        }

        $invitation->team->setVisible(['name', 'owner']);

        $invitation->team->owner->setVisible(['name']);

        return $invitation;
    }
}
