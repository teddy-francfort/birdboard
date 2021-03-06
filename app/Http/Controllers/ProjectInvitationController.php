<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Project;
use Illuminate\Http\Request;
use App\Http\Requests\ProjectInvitationRequest;

class ProjectInvitationController extends Controller
{
    public function invite(ProjectInvitationRequest $request, Project $project)
    {
        $user = User::whereEmail($request->email)->first();
        $project->invite($user);
        return redirect($project->path());
    }
}
