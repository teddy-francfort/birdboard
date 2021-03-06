<x-app-layout>
    <x-slot name="header">
        <div class="flex items-end justify-between">
            <h2 class="text-muted text-sm"><a href="/projects" class="text-muted">My projects</a> /
                {{ $project->title }}</h2>
            <div class="flex items-center">
                @foreach ($project->members as $member)
                    <img src="{{ $member->avatar }}" alt="{{ $member->name }}'s avatar" class="rounded-full w-8 mr-2">
                @endforeach
                <img src="{{ $project->owner->avatar }}" alt="{{ $project->owner->name }}'s avatar"
                    class="rounded-full rounded-full w-8">
                <a href="{{ $project->path() . '/edit' }}" class="button ml-4">Edit project</a>
            </div>
        </div>
    </x-slot>

    <div class="lg:flex -mx-3">
        <section class="lg:w-3/4 px-3 mb-6">
            <section class="mb-6">
                <h3 class="font-normal text-gray text-lg mb-3">Tasks</h3>
                @foreach ($project->tasks as $task)
                    <div class="card mb-3">
                        <form action="{{ $task->path() }}" method="post">
                            @csrf
                            @method('PATCH')
                            <div class="flex items-center">
                                <input type="text" name="body" value="{{ $task->body }}"
                                    class="bg-card border-none focus:ring-0 focus:outline-none focus:border-none w-full {{ $task->completed ? 'text-muted line-through' : '' }}">
                                <input type="checkbox" name="completed" onchange="this.form.submit()"
                                    {{ $task->completed ? 'checked' : '' }}>
                            </div>
                        </form>
                    </div>
                @endforeach
                <div class="card mb-3">
                    <form action="{{ $project->path() . '/tasks' }}" method="POST">
                        @csrf
                        <input type="text" name="body" placeholder="Add a new task..."
                            class="bg-card border-none focus:ring-0 focus:outline-none focus:border-none w-full">
                    </form>
                </div>
            </section>
            <section>
                <h3 class="font-normal text-gray text-lg mb-3">General notes</h3>
                <form action="{{ $project->path() }}" method="post">
                    @csrf
                    @method('PATCH')
                    <textarea name="notes" class="card w-full mb-3" style="min-height:200px;"
                        placeholder="Write some useful notes about your project">{{ $project->notes }}</textarea>
                    <button type="submit" class="button">Save</button>
                </form>
                @include('projects.errors')
            </section>
        </section>
        <section class="lg:w-1/4 px-3">
            @include('projects.card')
            @include('projects.activity.card')
            @can('manage', $project)
                @include('projects.invite')
            @endcan
        </section>
    </div>
</x-app-layout>
