<x-app-layout>
    <x-slot name="header">
        <div class="flex items-end justify-between">
            <h2 class="text-gray text-sm">My projects</h2>
            <a v-on:click.prevent="$modal.show('new-project-modal')" href=" /projects/create" class="button">New project</a>
        </div>
    </x-slot>
    <section class="grid gap-6 sm:grid-cols-1 xl:grid-cols-3">
        @forelse($projects as $project)
            @include('projects.card')
        @empty
            <div>No projects yet !</div>
        @endforelse
    </section>
    <new-project-modal></new-project-modal>
</x-app-layout>
