<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProjectStoreRequest;
use App\Http\Requests\ProjectUpdateRequest;
use App\Models\Project;
use App\Models\TaskCategory;
use App\Models\TaskStatus;
use App\Models\User;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $breadcrumbs = [
            ['title' => 'Home', 'url' => route('home')],
            ['title' => 'Projects', 'url' => route('projects.index')],
        ];

        return view('projects.index', [
            'projects' => Project::filter(request(['search', 'filters']))
                ->sort()
                ->paginate(config('contants.pagination_limit')),
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $breadcrumbs = [
            ['title' => 'Home', 'url' => route('home')],
            ['title' => 'Projects', 'url' => route('projects.index')],
            ['title' => 'Project Create', 'url' => route('projects.create')],
        ];

        return view('projects.create', [
            'users' => User::all(),
            'project' => new Project(),
            'selectedUsers' => collect(),
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ProjectStoreRequest $request)
    {
        $project = Project::create($request->all());

        $project->users()->attach($request->user_ids);

        TaskStatus::insert(TaskStatus::getDefaultStatuses($project));

        TaskCategory::insert(TaskCategory::getDefaultCategories($project));

        return redirect()->route('projects.index')->with(['success' => 'A project was created successfully.']);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function show(Project $project)
    {
        $breadcrumbs = [
            ['title' => 'Home', 'url' => route('home')],
            ['title' => 'Projects', 'url' => route('projects.index')],
            ['title' => $project->name, 'url' => route('projects.show', $project->id)],
        ];

        return view('projects.show', [
            'project' => $project,
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function edit(Project $project)
    {
        $breadcrumbs = [
            ['title' => 'Home', 'url' => route('home')],
            ['title' => 'Projects', 'url' => route('projects.index')],
            ['title' => 'Project Edit', 'url' => route('projects.edit', $project->id)],
        ];

        return view('projects.edit', [
            'project' => $project,
            'users' => User::all(),
            'selectedUsers' => $project->users->pluck('id'),
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $requestk
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function update(ProjectUpdateRequest $request, Project $project)
    {
        $project->update($request->all());
        $project->users()->sync($request->user_ids);

        return redirect()->route('projects.index')->with(['success' => 'A project was updated successfully.']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function destroy(Project $project)
    {
        $project->delete();

        return back()->with(['success' => 'A project was deleted successfully.']);
    }
}
