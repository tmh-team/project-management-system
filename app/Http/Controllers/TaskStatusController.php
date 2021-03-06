<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaskStatusStoreRequest;
use App\Http\Requests\TaskStatusUpdateRequest;
use App\Models\Project;
use App\Models\TaskStatus;

class TaskStatusController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Project $project)
    {
        $breadcrumbs = [
            ['title' => 'Home', 'url' => route('home')],
            ['title' => 'Projects', 'url' => route('projects.index')],
            ['title' => $project->name, 'url' => route('projects.show', $project->id)],
            ['title' => 'Statuses', 'url' => route('statuses.index', $project->id)],
        ];

        return view('task_statuses.index', [
            'projectId' => $project->id,
            'statuses' => TaskStatus::where('project_id', $project->id)
                ->filter()
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
    public function create(Project $project)
    {
        $breadcrumbs = [
            ['title' => 'Home', 'url' => route('home')],
            ['title' => 'Projects', 'url' => route('projects.index')],
            ['title' => $project->name, 'url' => route('projects.show', $project->id)],
            ['title' => 'Statuses', 'url' => route('statuses.index', $project->id)],
            ['title' => 'Status Create', 'url' => route('statuses.create', $project->id)],
        ];

        return view('task_statuses.create', [
            'projectId' => $project->id,
            'status' => new TaskStatus(),
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\TaskStatusStoreRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(TaskStatusStoreRequest $request, Project $project)
    {
        $request->merge([
            'project_id' => $project->id,
        ]);
        TaskStatus::create($request->all());

        return redirect()
            ->route('statuses.index', $project->id)
            ->with('success', 'A status has been created.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Project  $project
     * @param  \App\Models\TaskStatus  $status
     * @return \Illuminate\Http\Response
     */
    public function show(Project $project, TaskStatus $status)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Project  $project
     * @param  \App\Models\TaskStatus  $status
     * @return \Illuminate\Http\Response
     */
    public function edit(Project $project, TaskStatus $status)
    {
        $breadcrumbs = [
            ['title' => 'Home', 'url' => route('home')],
            ['title' => 'Projects', 'url' => route('projects.index')],
            ['title' => $project->name, 'url' => route('projects.show', $project->id)],
            ['title' => 'Statuses', 'url' => route('statuses.index', $project->id)],
            ['title' => 'Status Edit', 'url' => route('statuses.edit', [$project->id, $status->id])],
        ];

        return view('task_statuses.edit', [
            'projectId' => $project->id,
            'status' => $status,
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\TaskStatusUpdateRequest  $request
     * @param  \App\Models\Project  $project
     * @param  \App\Models\TaskStatus  $status
     * @return \Illuminate\Http\Response
     */
    public function update(TaskStatusUpdateRequest $request, Project $project, TaskStatus $status)
    {
        $request->merge([
            'project_id' => $project->id,
        ]);
        $status->update($request->all());

        return redirect()
            ->route('statuses.index', $project->id)
            ->with('success', 'A status has been updated.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Project  $project
     * @param  \App\Models\TaskStatus  $status
     * @return \Illuminate\Http\Response
     */
    public function destroy(Project $project, TaskStatus $status)
    {
        $status->delete();

        return redirect()
            ->route('statuses.index', $project->id)
            ->with('success', 'A status has been deleted.');
    }
}
