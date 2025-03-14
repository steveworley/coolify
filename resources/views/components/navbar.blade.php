@auth
    <nav class="fixed h-full pt-28 scrollbar">
        <a href="/" class="fixed top-0 z-50 mx-3 mt-3 bg-transparent cursor-pointer"><img
                class="transition rounded w-11 h-11" src="{{ asset('coolify-transparent.png') }}"></a>
        <ul class="flex flex-col h-full gap-4 menu flex-nowrap">
            <li title="Dashboard">
                <a class="hover:bg-transparent" href="/">
                    <svg xmlns="http://www.w3.org/2000/svg" class="{{ request()->is('/') ? 'text-warning icon' : 'icon' }}"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                </a>
            </li>
            <li title="Servers">
                <a class="hover:bg-transparent" href="/servers">
                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="{{ request()->is('server/*') || request()->is('servers') ? 'text-warning icon' : 'icon' }}"
                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M3 4m0 3a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v2a3 3 0 0 1 -3 3h-12a3 3 0 0 1 -3 -3z" />
                        <path d="M15 20h-9a3 3 0 0 1 -3 -3v-2a3 3 0 0 1 3 -3h12" />
                        <path d="M7 8v.01" />
                        <path d="M7 16v.01" />
                        <path d="M20 15l-2 3h3l-2 3" />
                    </svg>
                </a>
            </li>
            <li title="Projects">
                <a class="hover:bg-transparent" href="/projects">
                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="{{ request()->is('project/*') || request()->is('projects') ? 'text-warning icon' : 'icon' }}"
                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M12 4l-8 4l8 4l8 -4l-8 -4" />
                        <path d="M4 12l8 4l8 -4" />
                        <path d="M4 16l8 4l8 -4" />
                    </svg>
                </a>
            </li>
            <div class="inline-block text-left " x-data="{ open: false }">
                <div>
                    <button x-on:click.prevent="open = !open" x-on:click.away="open = false" type="button"
                        class="py-4 mx-4" id="menu-button" aria-expanded="true" aria-haspopup="true">
                        <svg class="icon" viewBox="0 0 256 256" xmlns="http://www.w3.org/2000/svg">
                            <path fill="currentColor"
                                d="M224 128a8 8 0 0 1-8 8h-80v80a8 8 0 0 1-16 0v-80H40a8 8 0 0 1 0-16h80V40a8 8 0 0 1 16 0v80h80a8 8 0 0 1 8 8" />
                        </svg>
                    </button>
                </div>
                <div x-show="open" x-cloak
                    class="absolute left-0 z-10 w-56 mx-4 mt-2 origin-top-right rounded shadow-lg bg-coolgray-100 ring-1 ring-black ring-opacity-5 focus:outline-none"
                    role="menu" aria-orientation="vertical" aria-labelledby="menu-button" tabindex="-1">
                    <div class="py-1" role="none">
                        <li title="Tags" class="border-transparent hover:bg-coolgray-200 ">
                            <a class=" hover:bg-transparent hover:no-underline" href="{{ route('tags.index') }}">
                                <svg class="{{ request()->is('tags*') ? 'text-warning icon' : 'icon' }}"viewBox="0 0 24 24"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                        stroke-width="2">
                                        <path
                                            d="M3 8v4.172a2 2 0 0 0 .586 1.414l5.71 5.71a2.41 2.41 0 0 0 3.408 0l3.592-3.592a2.41 2.41 0 0 0 0-3.408l-5.71-5.71A2 2 0 0 0 9.172 6H5a2 2 0 0 0-2 2" />
                                        <path d="m18 19l1.592-1.592a4.82 4.82 0 0 0 0-6.816L15 6m-8 4h-.01" />
                                    </g>
                                </svg>
                                Tags
                            </a>
                        </li>
                        <li title="Command Center" class="hover:bg-coolgray-200">
                            <a class="hover:bg-transparent hover:no-underline" href="{{ route('command-center') }}">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="{{ request()->is('command-center*') ? 'text-warning icon' : 'icon' }}"
                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M5 7l5 5l-5 5" />
                                    <path d="M12 19l7 0" />
                                </svg>
                                Command Center
                            </a>
                        </li>
                        <li title="Source" class="hover:bg-coolgray-200">
                            <a class="hover:bg-transparent hover:no-underline" href="{{ route('source.all') }}">
                                <svg class="{{ request()->is('source*') ? 'text-warning icon' : 'icon' }}"
                                    viewBox="0 0 15 15" xmlns="http://www.w3.org/2000/svg">
                                    <path fill="currentColor"
                                        d="m6.793 1.207l.353.354l-.353-.354ZM1.207 6.793l-.353-.354l.353.354Zm0 1.414l.354-.353l-.354.353Zm5.586 5.586l-.354.353l.354-.353Zm1.414 0l-.353-.354l.353.354Zm5.586-5.586l.353.354l-.353-.354Zm0-1.414l-.354.353l.354-.353ZM8.207 1.207l.354-.353l-.354.353ZM6.44.854L.854 6.439l.707.707l5.585-5.585L6.44.854ZM.854 8.56l5.585 5.585l.707-.707l-5.585-5.585l-.707.707Zm7.707 5.585l5.585-5.585l-.707-.707l-5.585 5.585l.707.707Zm5.585-7.707L8.561.854l-.707.707l5.585 5.585l.707-.707Zm0 2.122a1.5 1.5 0 0 0 0-2.122l-.707.707a.5.5 0 0 1 0 .708l.707.707ZM6.44 14.146a1.5 1.5 0 0 0 2.122 0l-.707-.707a.5.5 0 0 1-.708 0l-.707.707ZM.854 6.44a1.5 1.5 0 0 0 0 2.122l.707-.707a.5.5 0 0 1 0-.708L.854 6.44Zm6.292-4.878a.5.5 0 0 1 .708 0L8.56.854a1.5 1.5 0 0 0-2.122 0l.707.707Zm-2 1.293l1 1l.708-.708l-1-1l-.708.708ZM7.5 5a.5.5 0 0 1-.5-.5H6A1.5 1.5 0 0 0 7.5 6V5Zm.5-.5a.5.5 0 0 1-.5.5v1A1.5 1.5 0 0 0 9 4.5H8ZM7.5 4a.5.5 0 0 1 .5.5h1A1.5 1.5 0 0 0 7.5 3v1Zm0-1A1.5 1.5 0 0 0 6 4.5h1a.5.5 0 0 1 .5-.5V3Zm.646 2.854l1.5 1.5l.707-.708l-1.5-1.5l-.707.708ZM10.5 8a.5.5 0 0 1-.5-.5H9A1.5 1.5 0 0 0 10.5 9V8Zm.5-.5a.5.5 0 0 1-.5.5v1A1.5 1.5 0 0 0 12 7.5h-1Zm-.5-.5a.5.5 0 0 1 .5.5h1A1.5 1.5 0 0 0 10.5 6v1Zm0-1A1.5 1.5 0 0 0 9 7.5h1a.5.5 0 0 1 .5-.5V6ZM7 5.5v4h1v-4H7Zm.5 5.5a.5.5 0 0 1-.5-.5H6A1.5 1.5 0 0 0 7.5 12v-1Zm.5-.5a.5.5 0 0 1-.5.5v1A1.5 1.5 0 0 0 9 10.5H8Zm-.5-.5a.5.5 0 0 1 .5.5h1A1.5 1.5 0 0 0 7.5 9v1Zm0-1A1.5 1.5 0 0 0 6 10.5h1a.5.5 0 0 1 .5-.5V9Z" />
                                </svg>
                                Sources
                            </a>
                        </li>
                        <li title="Security" class="hover:bg-coolgray-200">
                            <a class="hover:bg-transparent hover:no-underline"
                                href="{{ route('security.private-key.index') }}">
                                <svg class="{{ request()->is('security*') ? 'text-warning icon' : 'icon' }}"
                                    viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path fill="none" stroke="currentColor" stroke-linecap="round"
                                        stroke-linejoin="round" stroke-width="2"
                                        d="m16.555 3.843l3.602 3.602a2.877 2.877 0 0 1 0 4.069l-2.643 2.643a2.877 2.877 0 0 1-4.069 0l-.301-.301l-6.558 6.558a2 2 0 0 1-1.239.578L5.172 21H4a1 1 0 0 1-.993-.883L3 20v-1.172a2 2 0 0 1 .467-1.284l.119-.13L4 17h2v-2h2v-2l2.144-2.144l-.301-.301a2.877 2.877 0 0 1 0-4.069l2.643-2.643a2.877 2.877 0 0 1 4.069 0zM15 9h.01" />
                                </svg>
                                Security
                            </a>
                        </li>
                        <li title="Profile" class="hover:bg-coolgray-200">
                            <a class="hover:bg-transparent hover:no-underline" href="{{ route('profile') }}">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="{{ request()->is('profile*') ? 'text-warning icon' : 'icon' }}"
                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" />
                                    <path d="M12 10m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" />
                                    <path d="M6.168 18.849a4 4 0 0 1 3.832 -2.849h4a4 4 0 0 1 3.834 2.855" />
                                </svg>
                                Profile
                            </a>
                        </li>
                        <li title="Teams" class="hover:bg-coolgray-200">
                            <a class="hover:bg-transparent hover:no-underline" href="{{ route('team.index') }}">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                    class="{{ request()->is('team*') ? 'text-warning icon' : 'icon' }}"
                                    stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M10 13a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" />
                                    <path d="M8 21v-1a2 2 0 0 1 2 -2h4a2 2 0 0 1 2 2v1" />
                                    <path d="M15 5a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" />
                                    <path d="M17 10h2a2 2 0 0 1 2 2v1" />
                                    <path d="M5 5a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" />
                                    <path d="M3 13v-1a2 2 0 0 1 2 -2h2" />
                                </svg>
                                Teams @if (isCloud())
                                    / Subscription
                                @endif
                            </a>
                        </li>

                        @if (isInstanceAdmin())
                            <li title="Settings" class="hover:bg-coolgray-200">
                                <a class="hover:bg-transparent hover:no-underline" href="/settings">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="{{ request()->is('settings*') ? 'text-warning icon' : 'icon' }}"
                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path
                                            d="M10.325 4.317c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756 .426 1.756 2.924 0 3.35a1.724 1.724 0 0 0 -1.066 2.573c.94 1.543 -.826 3.31 -2.37 2.37a1.724 1.724 0 0 0 -2.572 1.065c-.426 1.756 -2.924 1.756 -3.35 0a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065z" />
                                        <path d="M9 12a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" />
                                    </svg>
                                    Settings
                                </a>
                            </li>
                        @endif
                        <li title="Boarding" class="hover:bg-coolgray-200">
                            <a class="hover:bg-transparent hover:no-underline" href="{{ route('boarding') }}">
                                <svg class="{{ request()->is('boarding*') ? 'text-warning icon' : 'icon' }}"
                                    viewBox="0 0 256 256" xmlns="http://www.w3.org/2000/svg">
                                    <path fill="currentColor"
                                        d="M224 128a8 8 0 0 1-8 8h-88a8 8 0 0 1 0-16h88a8 8 0 0 1 8 8m-96-56h88a8 8 0 0 0 0-16h-88a8 8 0 0 0 0 16m88 112h-88a8 8 0 0 0 0 16h88a8 8 0 0 0 0-16M82.34 42.34L56 68.69L45.66 58.34a8 8 0 0 0-11.32 11.32l16 16a8 8 0 0 0 11.32 0l32-32a8 8 0 0 0-11.32-11.32m0 64L56 132.69l-10.34-10.35a8 8 0 0 0-11.32 11.32l16 16a8 8 0 0 0 11.32 0l32-32a8 8 0 0 0-11.32-11.32m0 64L56 196.69l-10.34-10.35a8 8 0 0 0-11.32 11.32l16 16a8 8 0 0 0 11.32 0l32-32a8 8 0 0 0-11.32-11.32" />
                                </svg>
                                Boarding
                            </a>
                        </li>
                    </div>
                </div>
            </div>
            @if (isCloud() && isInstanceAdmin())
                <li title="Admin">
                    <a class="hover:bg-transparent" href="/admin">
                        <svg class="text-pink-600 icon" viewBox="0 0 256 256" xmlns="http://www.w3.org/2000/svg">
                            <path fill="currentColor"
                                d="M177.62 159.6a52 52 0 0 1-34 34a12.2 12.2 0 0 1-3.6.55a12 12 0 0 1-3.6-23.45a28 28 0 0 0 18.32-18.32a12 12 0 0 1 22.9 7.2ZM220 144a92 92 0 0 1-184 0c0-28.81 11.27-58.18 33.48-87.28a12 12 0 0 1 17.9-1.33l19.69 19.11L127 19.89a12 12 0 0 1 18.94-5.12C168.2 33.25 220 82.85 220 144m-24 0c0-41.71-30.61-78.39-52.52-99.29l-20.21 55.4a12 12 0 0 1-19.63 4.5L80.71 82.36C67 103.38 60 124.06 60 144a68 68 0 0 0 136 0" />
                        </svg>
                    </a>
                </li>
            @endif
            <div class="flex-1"></div>
            @if (isInstanceAdmin() && !isCloud())
                @persist('upgrade')
                    <livewire:upgrade />
                @endpersist
            @endif
            <li title="Help us!">
                <a class="hover:bg-transparent" href="https://coolify.io/sponsorships" target="_blank">
                    <svg class="icon hover:text-pink-500" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                            stroke-width="2">
                            <path d="M19.5 12.572L12 20l-7.5-7.428A5 5 0 1 1 12 6.006a5 5 0 1 1 7.5 6.572" />
                            <path
                                d="M12 6L8.707 9.293a1 1 0 0 0 0 1.414l.543.543c.69.69 1.81.69 2.5 0l1-1a3.182 3.182 0 0 1 4.5 0l2.25 2.25m-7 3l2 2M15 13l2 2" />
                        </g>
                    </svg>
                </a>
            </li>

            <li title="Send us feedback or get help!" class="hover:bg-transparent">
                <div class="justify-center" wire:click="help" onclick="help.showModal()">
                    <svg class="icon" viewBox="0 0 256 256" xmlns="http://www.w3.org/2000/svg">
                        <path fill="currentColor"
                            d="M140 180a12 12 0 1 1-12-12a12 12 0 0 1 12 12M128 72c-22.06 0-40 16.15-40 36v4a8 8 0 0 0 16 0v-4c0-11 10.77-20 24-20s24 9 24 20s-10.77 20-24 20a8 8 0 0 0-8 8v8a8 8 0 0 0 16 0v-.72c18.24-3.35 32-17.9 32-35.28c0-19.85-17.94-36-40-36m104 56A104 104 0 1 1 128 24a104.11 104.11 0 0 1 104 104m-16 0a88 88 0 1 0-88 88a88.1 88.1 0 0 0 88-88" />
                    </svg>
                </div>
            </li>
            <form action="/logout" method="POST" class="hover:bg-transparent">
                <li title="Logout" class="mb-6 hover:transparent">
                    @csrf
                    <button type="submit" class="rounded-none hover:text-white hover:bg-transparent">
                        <svg class="icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path fill="currentColor"
                                d="M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2a9.985 9.985 0 0 1 8 4h-2.71a8 8 0 1 0 .001 12h2.71A9.985 9.985 0 0 1 12 22m7-6v-3h-8v-2h8V8l5 4z" />
                        </svg>
                    </button>
                </li>
            </form>
        </ul>
    </nav>
@endauth
