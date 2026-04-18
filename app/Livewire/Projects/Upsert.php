<?php

namespace App\Livewire\Projects;

use App\Models\Project;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Upsert extends Component
{
    public ?Project $project = null;

    public $name = '';
    public $description = '';
    public $status = 'pending';
    public $start_date;
    public $due_date;
    public $budget;

    protected function rules(): array
    {
        return [
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'status'      => 'required|in:pending,in_progress,completed,on_hold,cancelled',
            'start_date'  => 'nullable|date',
            'due_date'    => 'nullable|date',
            'budget'      => 'nullable|numeric|min:0',
        ];
    }

    public function mount(?Project $project = null): void
    {
        if ($project && $project->exists) {
            $this->project    = $project;
            $this->name       = $project->name;
            $this->description = $project->description ?? '';
            $this->status     = $project->status;
            $this->start_date = $project->start_date?->format('Y-m-d');
            $this->due_date   = $project->due_date?->format('Y-m-d');
            $this->budget     = $project->budget;
        } else {
            $this->start_date = date('Y-m-d');
        }
    }

    public function save(): void
    {
        $data = $this->validate();

        if ($this->project) {
            $this->project->update($data);
            session()->flash('success', 'Project updated.');
        } else {
            Project::create($data);
            session()->flash('success', 'Project created.');
        }

        $this->redirect(route('projects.index'));
    }

    public function render()
    {
        return view('livewire.projects.upsert');
    }
}
