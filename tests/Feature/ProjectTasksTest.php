<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProjectTasksTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_project_can_have_tasks()
    {

        //$this->withoutExceptionHandling();

        $this->signIn();

        $project = Project::factory()->create(['owner_id' => auth()->id()]);

        $this->post($project->path() . '/tasks', ['body' => 'Test task']);

        $this->get($project->path())
            ->assertSee('Test task');
    }

    public function test_a_task_requires_a_body()
    {
        //$this->withoutExceptionHandling();

        $this->signIn();

        $project = Project::factory()->create(['owner_id' => auth()->id()]);

        $attributes = Task::factory()->make(['body' => ''])->toArray();
        //Post a task
        $response = $this->post($project->path() . '/tasks', $attributes);
        $response->assertSessionHasErrors('body');
    }

}
