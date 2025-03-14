<div>
    <x-team.navbar />

    <form class="flex flex-col gap-2 pb-6" wire:submit='submit'>
        <div class="flex items-end gap-2">
            <h2>General</h2>
            <x-forms.button type="submit">
                Save
            </x-forms.button>
        </div>
        <div class="flex gap-2">
            <x-forms.input id="team.name" label="Name" required />
            <x-forms.input id="team.description" label="Description" />
        </div>
    </form>

    @if (isCloud())
        <div class="pb-8">
            <h2>Subscription</h2>
            @if (data_get(currentTeam(), 'subscription'))
                <livewire:subscription.actions />
            @else
                <x-forms.button class="mt-4"><a class="text-white hover:no-underline"
                        href="{{ route('subscription.index') }}">Subscribe Now</a>
                </x-forms.button>
            @endif

        </div>
    @endif
    <div>
        <h2>Danger Zone</h2>
        <div class="pb-4">Woah. I hope you know what are you doing.</div>
        <h4 class="pb-4">Delete Team</h4>
        @if (session('currentTeam.id') === 0)
            <div>This is the default team. You can't delete it.</div>
        @elseif(auth()->user()->teams()->get()->count() === 1)
            <div>You can't delete your last team.</div>
        @elseif(currentTeam()->subscription && currentTeam()->subscription?->lemon_status !== 'cancelled')
            <div>Please cancel your subscription before delete this team (Manage My Subscription).</div>
        @else
            @if (currentTeam()->isEmpty())
                <div class="pb-4">This will delete your team. Beware! There is no coming back!</div>
                <x-new-modal isErrorButton buttonTitle="Delete">
                    This team be deleted. It is not reversible. <br>Please think again.
                </x-new-modal>
            @else
                <div>
                    <div class="pb-4">You need to delete the following resources to be able to delete the team:</div>
                    @if (currentTeam()->projects()->count() > 0)
                        <h4 class="pb-4">Projects:</h4>
                        <ul class="pl-8 list-disc">
                            @foreach (currentTeam()->projects as $resource)
                                <li>{{ $resource->name }}</li>
                            @endforeach
                        </ul>
                    @endif
                    @if (currentTeam()->servers()->count() > 0)
                        <h4 class="py-4">Servers:</h4>
                        <ul class="pl-8 list-disc">
                            @foreach (currentTeam()->servers as $resource)
                                <li>{{ $resource->name }}</li>
                            @endforeach
                        </ul>
                    @endif
                    @if (currentTeam()->privateKeys()->count() > 0)
                        <h4 class="py-4">Private Keys:</h4>
                        <ul class="pl-8 list-disc">
                            @foreach (currentTeam()->privateKeys as $resource)
                                <li>{{ $resource->name }}</li>
                            @endforeach
                        </ul>
                    @endif
                    @if (currentTeam()->sources()->count() > 0)
                        <h4 class="py-4">Sources:</h4>
                        <ul class="pl-8 list-disc">
                            @foreach (currentTeam()->sources() as $resource)
                                <li>{{ $resource->name }}</li>
                            @endforeach
                        </ul>
                    @endif
            @endif
        @endif
    </div>
</div>
