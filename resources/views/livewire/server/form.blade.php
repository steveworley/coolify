<div>
    <form wire:submit.prevent='submit' class="flex flex-col">
        <div class="flex gap-2">
            <h2>General</h2>
            @if ($server->id === 0)
                <x-new-modal buttonTitle="Save" title="Change Localhost" action="submit">
                    You could lost a lot of functionalities if you change the server details of the server where Coolify
                    is
                    running on.<br>Please think again.
                </x-new-modal>
            @else
                <x-forms.button type="submit">Save</x-forms.button>
                @if ($server->isFunctional())
                    <x-slide-over closeWithX fullScreen>
                        <x-slot:title>Validate & configure</x-slot:title>
                        <x-slot:content>
                            <livewire:server.validate-and-install :server="$server" ask />
                        </x-slot:content>
                        <x-forms.button @click="slideOverOpen=true" wire:click.prevent='validateServer' isHighlighted>
                            Revalidate server
                        </x-forms.button>
                    </x-slide-over>
                @endif
            @endif
        </div>
        @if ($server->isFunctional())
            Server is reachable and validated.
        @else
            You can't use this server until it is validated.
        @endif
        @if ((!$server->settings->is_reachable || !$server->settings->is_usable) && $server->id !== 0)
            <x-slide-over closeWithX fullScreen>
                <x-slot:title>Validate & configure</x-slot:title>
                <x-slot:content>
                    <livewire:server.validate-and-install :server="$server" />
                </x-slot:content>
                <x-forms.button @click="slideOverOpen=true"
                    class="w-full mt-8 mb-4 font-bold box-without-bg bg-coollabs hover:bg-coollabs-100"
                    wire:click.prevent='validateServer' isHighlighted>
                    Validate Server & Install Docker Engine
                </x-forms.button>
            </x-slide-over>
        @endif
        @if ((!$server->settings->is_reachable || !$server->settings->is_usable) && $server->id === 0)
            <x-forms.button class="mt-8 mb-4 font-bold box-without-bg bg-coollabs hover:bg-coollabs-100"
                wire:click.prevent='checkLocalhostConnection' isHighlighted>
                Validate Server
            </x-forms.button>
        @endif
        <div class="flex flex-col gap-2 pt-4">
            <div class="flex flex-col w-full gap-2 lg:flex-row">
                <x-forms.input id="server.name" label="Name" required />
                <x-forms.input id="server.description" label="Description" />
                @if (!$server->settings->is_swarm_worker && !$server->settings->is_build_server)
                    <x-forms.input placeholder="https://example.com" id="wildcard_domain" label="Wildcard Domain"
                        helper='A wildcard domain allows you to receive a randomly generated domain for your new applications. <br><br>For instance, if you set "https://example.com" as your wildcard domain, your applications will receive domains like "https://randomId.example.com".' />
                @endif

            </div>
            <div class="flex flex-col w-full gap-2 lg:flex-row">
                <x-forms.input id="server.ip" label="IP Address/Domain"
                    helper="An IP Address (127.0.0.1) or domain (example.com)." required />
                <div class="flex gap-2">
                    <x-forms.input id="server.user" label="User" required />
                    <x-forms.input type="number" id="server.port" label="Port" required />
                </div>
            </div>
            <div class="w-64">
                @if (!$server->isLocalhost())
                    @if ($server->settings->is_build_server)
                        <x-forms.checkbox instantSave disabled id="server.settings.is_build_server"
                            label="Use it as a build server?" />
                    @else
                        <x-forms.checkbox instantSave
                            helper="If you are using Cloudflare Tunnels, enable this. It will proxy all SSH requests to your server through Cloudflare.<br><span class='text-warning'>Coolify does not install or set up Cloudflare (cloudflared) on your server.</span>"
                            id="server.settings.is_cloudflare_tunnel" label="Cloudflare Tunnel" />
                        @if ($server->isSwarm())
                            <div class="pt-6"> Swarm support is experimental. </div>
                        @endif
                        @if ($server->settings->is_swarm_worker)
                            <x-forms.checkbox disabled instantSave type="checkbox" id="server.settings.is_swarm_manager"
                                helper="For more information, please read the documentation <a class='text-white' href='https://coolify.io/docs/docker/swarm' target='_blank'>here</a>."
                                label="Is it a Swarm Manager?" />
                        @else
                            <x-forms.checkbox instantSave type="checkbox" id="server.settings.is_swarm_manager"
                                helper="For more information, please read the documentation <a class='text-white' href='https://coolify.io/docs/docker/swarm' target='_blank'>here</a>."
                                label="Is it a Swarm Manager?" />
                        @endif
                        @if ($server->settings->is_swarm_manager)
                            <x-forms.checkbox disabled instantSave type="checkbox" id="server.settings.is_swarm_worker"
                                helper="For more information, please read the documentation <a class='text-white' href='https://coolify.io/docs/docker/swarm' target='_blank'>here</a>."
                                label="Is it a Swarm Worker?" />
                        @else
                            <x-forms.checkbox instantSave type="checkbox" id="server.settings.is_swarm_worker"
                                helper="For more information, please read the documentation <a class='text-white' href='https://coolify.io/docs/docker/swarm' target='_blank'>here</a>."
                                label="Is it a Swarm Worker?" />
                        @endif
                    @endif
                @endif
            </div>
        </div>

        @if ($server->isFunctional())
            <h3 class="py-4">Settings</h3>
            <div class="flex gap-2">
                <x-forms.input id="cleanup_after_percentage" label="Disk cleanup threshold (%)" required
                    helper="The disk cleanup task will run when the disk usage exceeds this threshold." />
                <x-forms.input id="server.settings.concurrent_builds" label="Number of concurrent builds" required
                    helper="You can specify the number of simultaneous build processes/deployments that should run concurrently." />
                <x-forms.input id="server.settings.dynamic_timeout" label="Deployment timeout (seconds)" required
                    helper="You can define the maximum duration for a deployment to run before timing it out." />
            </div>
        @endif
    </form>
</div>
