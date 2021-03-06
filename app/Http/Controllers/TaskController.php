<?php

namespace App\Http\Controllers;

use App\Exports\TaskExport;
use App\Http\Requests\TaskStoreRequest;
use App\Http\Requests\TaskUpdateRequest;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskCategory;
use App\Models\TaskStatus;
use Maatwebsite\Excel\Facades\Excel;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $tasks = Task::with('status', 'category')
        ->filter(request(['search', 'filter']))
        ->sort()
        ->paginate(config('contants.pagination_limit'));

        $project = Project::findOrFail(request('filter')['project']);
        $breadcrumbs = [
            ['title' => 'Home', 'url' => route('home')],
            ['title' => 'Projects', 'url' => route('projects.index')],
            ['title' => $project->name, 'url' => route('projects.show', $project->id)],
            ['title' => 'Tasks', 'url' => route('tasks.index', ['filter[project]' => $project->id])],
        ];
        
        return view('tasks.index', [
            'projectId' => $project->id,
            'tasks' => $tasks,
            'options' => [
                'statuses' => TaskStatus::where('project_id', $project->id)->get(),
                'categories' => TaskCategory::where('project_id', $project->id)->get()
            ],
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $project = Project::findOrFail(request('filter')['project']);
        $breadcrumbs = [
            ['title' => 'Home', 'url' => route('home')],
            ['title' => 'Projects', 'url' => route('projects.index')],
            ['title' => $project->name, 'url' => route('projects.show', $project->id)],
            ['title' => 'Tasks', 'url' => route('tasks.index', ['filter[project]' => $project->id])],
            ['title' => 'Create', 'url' => route('tasks.index', ['filter[project]' => $project->id])],
        ];

        return view('tasks.create', [
            'projectId' => $project->id,
            'task' => new Task(),
            'statuses' => TaskStatus::where('project_id', $project->id)->get(),
            'categories' => TaskCategory::where('project_id', $project->id)->get(),
            'users' => $project->users,
            'developers' => collect(),
            'reviewers' => collect(),
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\TaskStoreRequest  $request
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function store(TaskStoreRequest $request)
    {
        $project = Project::findOrFail($request->project_id);
        $request->merge([
            'project_id' => $project->id,
        ]);
        $task = Task::create($request->all());

        $task->developers()->attach($request->developer_ids);
        $task->reviewers()->attach($request->reviewer_ids);

        return redirect()
            ->route('tasks.index', ['filter[project]' => $project->id])
            ->with('success', 'A task has been created.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Project  $project
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function show(Task $task)
    {
        $project = Project::findOrFail(request('filter')['project']);
        $breadcrumbs = [
            ['title' => 'Home', 'url' => route('home')],
            ['title' => 'Projects', 'url' => route('projects.index')],
            ['title' => $project->name, 'url' => route('projects.show', $project->id)],
            ['title' => 'Tasks', 'url' => route('tasks.index', ['filter[project]' => $project->id])],
            ['title' => 'Task Detail', 'url' => route('tasks.show', ['filter[project]' => $project->id, $task->id])],
        ];

        return view('tasks.show', [
            'projectId' => $project->id,
            'task' => $task,
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Project  $project
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function edit(Task $task)
    {
        $project = Project::findOrFail(request('filter')['project']);
        $breadcrumbs = [
            ['title' => 'Home', 'url' => route('home')],
            ['title' => 'Projects', 'url' => route('projects.index')],
            ['title' => $project->name, 'url' => route('projects.show', $project->id)],
            ['title' => 'Tasks', 'url' => route('tasks.index', ['filter[project]' => $project->id])],
            ['title' => 'Edit', 'url' => route('tasks.show', ['filter[project]' => $project->id, $task->id])],
        ];

        return view('tasks.edit', [
            'projectId' => $project->id,
            'task' => $task,
            'statuses' => TaskStatus::where('project_id', $project->id)->get(),
            'categories' => TaskCategory::where('project_id', $project->id)->get(),
            'users' => $project->users,
            'developers' => $task->developers->pluck('id'),
            'reviewers' => $task->reviewers->pluck('id'),
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\TaskUpdateRequest  $request
     * @param  \App\Models\Project  $project
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function update(TaskUpdateRequest $request, Task $task)
    {
        $project = Project::findOrFail($request->project_id);
        $request->merge([
            'project_id' => $project->id,
        ]);
        $task->update($request->all());

        $task->developers()->sync($request->developer_ids);
        $task->reviewers()->sync($request->reviewer_ids);

        return redirect()
            ->route('tasks.index', ['filter[project]' => $project->id])
            ->with('success', 'A task has been updated.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Project  $project
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function destroy(Task $task)
    {
        $project = Project::findOrFail(request('filter')['project']);
        $task->delete();

        return redirect()
            ->route('tasks.index', ['filter[project]' => $project->id])
            ->with('success', 'A task has been deleted.');
    }

    /**
     * Export tasks info
     *
     * @return void
     */
    public function export()
    {
        return Excel::download(new TaskExport(), 'Tasks' . '.xlsx');
    }
}
