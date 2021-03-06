<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Facades\Tests\Setup\ProjectFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\UnauthorizedException;
use Tests\Setup\ProjectFactory as SetupProjectFactory;
use Tests\TestCase;

class ManageProjectsTest extends TestCase
{
    use WithFaker, RefreshDatabase;



    public function test_guests_cannot_manage_projects()
    {
        $this->signIn();
        $project = Project::factory()->create();
        Auth::logout();
        $this->get('/projects')->assertRedirect('/login');
        $this->get('/projects/create')->assertRedirect('/login');
        $this->get($project->path() . '/edit')->assertRedirect('/login');
        $this->get($project->path())->assertRedirect('/login');
        $this->post('/projects', $project->toArray())->assertRedirect('/login');
    }

    public function test_unauthorized_user_cannot_delete_project()
    {
        //Given a project
        $this->signIn();
        $project = ProjectFactory::create();
        Auth::logout();

        //If an unauthorized user try to delete a project
        $response = $this->delete($project->path());
        //A forbidden response is returned
        $response->assertRedirect('/login');
        //The project still exists
        $this->assertDatabaseHas('projects', ['id' => $project->id]);
    }

    /**
     * Test the ability for a user to create a project 
     * 
     * @return void
     */
    public function test_a_user_can_create_a_project()
    {
        $this->signIn();

        $this->get('/projects/create')->assertStatus(200);

        $attributes = [
            "title" => $this->faker->sentence(),
            "description" => $this->faker->sentence(),
            "notes" => "My notes",
        ];

        //Post a project
        $response = $this->post('/projects', $attributes);

        //Test redirect
        $project = Project::first();
        $response->assertRedirect($project->path());

        //Check if the project appears on the projects page
        $this->get($project->path())
            ->assertSee($attributes['title'])
            ->assertSee($attributes['description'])
            ->assertSee($attributes['notes']);
    }

    /**
     * Test the ability for a user to create a project  with tasks
     * 
     * @return void
     */
    public function test_a_user_can_create_a_project_with_tasks()
    {
        $this->signIn();

        $attributes = [
            "title" => $this->faker->sentence(),
            "description" => $this->faker->sentence(),
            "notes" => "My notes",
            "tasks" => [
                ['body' => 'task 1'],
                ['body' => 'task 2'],
            ]
        ];

        //Post a project
        $response = $this->post('/projects', $attributes);

        //Test redirect
        $project = Project::first();

        //Check if the project has tasks
        $this->assertCount(2, $project->tasks);
    }

    /**
     * A user cannot create a project with empty tasks
     * 
     * @return void
     */
    public function test_a_user_cannot_create_a_project_with_empty_tasks()
    {
        $this->signIn();

        $attributes = [
            "title" => $this->faker->sentence(),
            "description" => $this->faker->sentence(),
            "notes" => "My notes",
            "tasks" => [
                ['body' => ''],
                ['body' => 'task 2'],
            ]
        ];

        //Post a project
        $response = $this->post('/projects', $attributes);
        $response->assertSessionHasErrors('tasks.0.body');

        $this->assertDatabaseCount('projects', 0);
        $this->assertDatabaseCount('tasks', 0);
    }


    public function test_a_user_can_update_a_project()
    {

        $this->withoutExceptionHandling();

        $this->signIn();
        $project = ProjectFactory::create();
        Auth::logout();

        $attributes = [
            "title" => $this->faker->sentence(),
            "description" => $this->faker->sentence(),
            "notes" => "Changed",
        ];

        $response = $this->actingAs($project->owner)->patch($project->path(), $attributes);

        $this->actingAs($project->owner)->get($project->path() . '/edit')->assertOk();

        //Test redirect
        $response->assertRedirect($project->path());

        //Check if a the project has been created to the database
        $this->assertDatabaseHas('projects', $attributes);
    }

    public function test_a_user_can_delete_a_project()
    {
        $this->withoutExceptionHandling();

        $user = $this->signIn();
        $project = ProjectFactory::ownedBy($user)->create();

        $this->delete($project->path());

        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    }

    public function test_a_project_requires_a_title()
    {
        $this->signIn();

        //Post a project
        $response = $this->post('/projects', Project::factory()->make(['title' => ''])->toArray());
        $response->assertSessionHasErrors('title');
    }

    public function test_a_project_requires_a_description()
    {
        //$this->withoutExceptionHandling();

        $this->signIn();

        //Post a project
        $response = $this->post('/projects', Project::factory()->make(['description' => ''])->toArray());
        $response->assertSessionHasErrors('description');
    }

    public function test_a_user_can_view_their_project()
    {
        $user = $this->signIn();
        $project = ProjectFactory::ownedBy($user)->create();
        $response  = $this->get($project->path());
        Auth::logout();
        $response->assertSee($project->title);
        $response->assertSee($project->description);
    }

    public function test_a_user_can_view_a_project_he_has_been_invited_to()
    {
        $user = $this->signIn();
        $project = ProjectFactory::ownedBy($user)->create();
        Auth::logout();

        //As an invitee
        $invitee = User::factory()->create();
        $project->invite($invitee);
        $this->signIn($invitee);

        $response  = $this->get($project->path());
        $response->assertSee($project->title);
        $response->assertSee($project->description);
    }

    public function test_a_user_can_view_the_projects_he_has_been_invited_to()
    {
        $this->withoutExceptionHandling();

        $user = $this->signIn();
        $project = ProjectFactory::ownedBy($user)->create();
        Auth::logout();

        //As an invitee
        $invitee = User::factory()->create();
        $project->invite($invitee);
        $this->signIn($invitee);
        $response  = $this->get('/projects');
        $response->assertSee($project->title);
    }

    public function test_an_authenticated_user_cannot_view_a_project_of_another_user()
    {

        $this->signIn();

        $project = Project::factory()->create();

        $this->get($project->path())->assertStatus(403);
    }

    public function test_an_authenticated_user_cannot_update_a_project_of_another_user()
    {

        $this->signIn();

        $project = Project::factory()->create();

        $this->patch($project->path())->assertStatus(403);
    }

    public function test_an_authenticated_user_cannot_view_projects_of_another_user()
    {

        $this->signIn();

        $project = Project::factory()->create();

        $this->get('/projects')->assertDontSee($project->title);
    }
}
