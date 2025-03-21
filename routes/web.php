<?php

use App\Http\Controllers\Api\Server as ApiServer;
use App\Models\GitlabApp;
use App\Models\PrivateKey;
use App\Models\Server;
use App\Models\StandaloneDocker;
use App\Models\SwarmDocker;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Controller;
use App\Http\Controllers\MagicController;

use App\Livewire\Admin\Index as AdminIndex;
use App\Livewire\Dev\Compose as Compose;

use App\Livewire\Dashboard;
use App\Livewire\Boarding\Index as BoardingIndex;
use App\Livewire\Settings\Index as SettingsIndex;
use App\Livewire\Settings\License as SettingsLicense;
use App\Livewire\Profile\Index as ProfileIndex;

use App\Livewire\Team\Index as TeamIndex;
use App\Livewire\Team\Create as TeamCreate;
use App\Livewire\Team\Notification\Index as TeamNotificationIndex;

use App\Livewire\Team\Storage\Index as TeamStorageIndex;
use App\Livewire\Team\Storage\Create as TeamStorageCreate;
use App\Livewire\Team\Storage\Show as TeamStorageShow;
use App\Livewire\Team\Member\Index as TeamMemberIndex;

use App\Livewire\CommandCenter\Index as CommandCenterIndex;
use App\Livewire\ForcePasswordReset;
use App\Livewire\Project\Index as ProjectIndex;
use App\Livewire\Project\Show as ProjectShow;
use App\Livewire\Project\Edit as ProjectEdit;
use App\Livewire\Project\CloneMe as ProjectCloneMe;
use App\Livewire\Project\Resource\Index as ResourceIndex;
use App\Livewire\Project\Resource\Create as ResourceCreate;

use App\Livewire\Project\Application\Configuration as ApplicationConfiguration;
use App\Livewire\Project\Application\Deployment\Index as DeploymentIndex;
use App\Livewire\Project\Application\Deployment\Show as DeploymentShow;
use App\Livewire\Project\Database\Configuration as DatabaseConfiguration;
use App\Livewire\Project\Database\Backup\Index as DatabaseBackupIndex;
use App\Livewire\Project\Database\Backup\Execution as DatabaseBackupExecution;

use App\Livewire\Project\Service\Configuration as ServiceConfiguration;
use App\Livewire\Project\Service\Index as ServiceIndex;

use App\Livewire\Project\EnvironmentEdit;
use App\Livewire\Project\Shared\ExecuteContainerCommand;
use App\Livewire\Project\Shared\Logs;
use App\Livewire\Project\Shared\ScheduledTask\Show as ScheduledTaskShow;

use App\Livewire\Security\ApiTokens;
use App\Livewire\Security\PrivateKey\Create as SecurityPrivateKeyCreate;
use App\Livewire\Security\PrivateKey\Show as SecurityPrivateKeyShow;

use App\Livewire\Server\Index as ServerIndex;
use App\Livewire\Server\Create as ServerCreate;
use App\Livewire\Server\Show as ServerShow;
use App\Livewire\Server\Resources as ResourcesShow;

use App\Livewire\Server\Destination\Show as DestinationShow;
use App\Livewire\Server\LogDrains;
use App\Livewire\Server\PrivateKey\Show as PrivateKeyShow;
use App\Livewire\Server\Proxy\DynamicConfigurations as ProxyDynamicConfigurations;
use App\Livewire\Server\Proxy\Show as ProxyShow;
use App\Livewire\Server\Proxy\Logs as ProxyLogs;
use App\Livewire\Source\Github\Change as GitHubChange;
use App\Livewire\Subscription\Index as SubscriptionIndex;

use App\Livewire\Tags\Index as TagsIndex;
use App\Livewire\Tags\Show as TagsShow;

use App\Livewire\TeamSharedVariablesIndex;
use App\Livewire\Waitlist\Index as WaitlistIndex;

if (isDev()) {
    Route::get('/dev/compose', Compose::class)->name('dev.compose');
}
Route::get('/admin', AdminIndex::class)->name('admin.index');

Route::post('/forgot-password', [Controller::class, 'forgot_password'])->name('password.forgot');
Route::get('/api/v1/test/realtime', [Controller::class, 'realtime_test'])->middleware('auth');
Route::get('/waitlist', WaitlistIndex::class)->name('waitlist.index');
Route::get('/verify', [Controller::class, 'verify'])->middleware('auth')->name('verify.email');
Route::get('/email/verify/{id}/{hash}', [Controller::class, 'email_verify'])->middleware(['auth'])->name('verify.verify');
Route::middleware(['throttle:login'])->group(function () {
    Route::get('/auth/link', [Controller::class, 'link'])->name('auth.link');
});

Route::prefix('magic')->middleware(['auth'])->group(function () {
    Route::get('/servers', [MagicController::class, 'servers']);
    Route::get('/destinations', [MagicController::class, 'destinations']);
    Route::get('/projects', [MagicController::class, 'projects']);
    Route::get('/environments', [MagicController::class, 'environments']);
    Route::get('/project/new', [MagicController::class, 'newProject']);
    Route::get('/environment/new', [MagicController::class, 'newEnvironment']);
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::middleware(['throttle:force-password-reset'])->group(function () {
        Route::get('/force-password-reset', ForcePasswordReset::class)->name('auth.force-password-reset');
    });

    Route::get('/', Dashboard::class)->name('dashboard');
    Route::get('/boarding', BoardingIndex::class)->name('boarding');

    Route::get('/subscription', SubscriptionIndex::class)->name('subscription.index');

    Route::get('/settings', SettingsIndex::class)->name('settings.index');
    Route::get('/settings/license', SettingsLicense::class)->name('settings.license');

    Route::get('/profile', ProfileIndex::class)->name('profile');
    Route::prefix('tags')->group(function () {
        Route::get('/', TagsIndex::class)->name('tags.index');
        Route::get('/{tag_name}', TagsShow::class)->name('tags.show');
    });
    Route::prefix('team')->group(function () {
        Route::get('/', TeamIndex::class)->name('team.index');
        Route::get('/new', TeamCreate::class)->name('team.create');
        Route::get('/members', TeamMemberIndex::class)->name('team.member.index');
        Route::get('/notifications', TeamNotificationIndex::class)->name('team.notification.index');
        Route::get('/shared-variables', TeamSharedVariablesIndex::class)->name('team.shared-variables.index');
        Route::get('/storages', TeamStorageIndex::class)->name('team.storage.index');
        Route::get('/storages/new', TeamStorageCreate::class)->name('team.storage.create');
        Route::get('/storages/{storage_uuid}', TeamStorageShow::class)->name('team.storage.show');
    });

    Route::get('/command-center', CommandCenterIndex::class)->name('command-center');

    Route::prefix('invitations')->group(function () {
        Route::get('/{uuid}', [Controller::class, 'accept_invitation'])->name('team.invitation.accept');
        Route::get('/{uuid}/revoke', [Controller::class, 'revoke_invitation'])->name('team.invitation.revoke');
    });

    Route::get('/projects', ProjectIndex::class)->name('project.index');
    Route::prefix('project/{project_uuid}')->group(function () {
        Route::get('/', ProjectShow::class)->name('project.show');
        Route::get('/edit', ProjectEdit::class)->name('project.edit');
    });
    Route::prefix('project/{project_uuid}/{environment_name}')->group(function () {
        Route::get('/', ResourceIndex::class)->name('project.resource.index');
        Route::get('/clone', ProjectCloneMe::class)->name('project.clone-me');
        Route::get('/new', ResourceCreate::class)->name('project.resource.create');
        Route::get('/edit', EnvironmentEdit::class)->name('project.environment.edit');
    });
    Route::prefix('project/{project_uuid}/{environment_name}/application/{application_uuid}')->group(function () {
        Route::get('/', ApplicationConfiguration::class)->name('project.application.configuration');
        Route::get('/deployment', DeploymentIndex::class)->name('project.application.deployment.index');
        Route::get('/deployment/{deployment_uuid}', DeploymentShow::class)->name('project.application.deployment.show');
        Route::get('/logs', Logs::class)->name('project.application.logs');
        Route::get('/command', ExecuteContainerCommand::class)->name('project.application.command');
        Route::get('/tasks/{task_uuid}', ScheduledTaskShow::class)->name('project.application.scheduled-tasks');
    });
    Route::prefix('project/{project_uuid}/{environment_name}/database/{database_uuid}')->group(function () {
        Route::get('/', DatabaseConfiguration::class)->name('project.database.configuration');
        Route::get('/logs', Logs::class)->name('project.database.logs');
        Route::get('/command', ExecuteContainerCommand::class)->name('project.database.command');
        Route::get('/backups', DatabaseBackupIndex::class)->name('project.database.backup.index');
        Route::get('/backups/{backup_uuid}', DatabaseBackupExecution::class)->name('project.database.backup.execution');
    });
    Route::prefix('project/{project_uuid}/{environment_name}/service/{service_uuid}')->group(function () {
        Route::get('/', ServiceConfiguration::class)->name('project.service.configuration');
        Route::get('/{stack_service_uuid}', ServiceIndex::class)->name('project.service.index');
        Route::get('/command', ExecuteContainerCommand::class)->name('project.service.command');
        Route::get('/tasks/{task_uuid}', ScheduledTaskShow::class)->name('project.service.scheduled-tasks');
    });

    Route::get('/servers', ServerIndex::class)->name('server.index');
    Route::get('/server/new', ServerCreate::class)->name('server.create');

    Route::prefix('server/{server_uuid}')->group(function () {
        Route::get('/', ServerShow::class)->name('server.show');
        Route::get('/resources', ResourcesShow::class)->name('server.resources');
        Route::get('/proxy', ProxyShow::class)->name('server.proxy');
        Route::get('/proxy/dynamic', ProxyDynamicConfigurations::class)->name('server.proxy.dynamic-confs');
        Route::get('/proxy/logs', ProxyLogs::class)->name('server.proxy.logs');
        Route::get('/private-key', PrivateKeyShow::class)->name('server.private-key');
        Route::get('/destinations', DestinationShow::class)->name('server.destinations');
        Route::get('/log-drains', LogDrains::class)->name('server.log-drains');
    });

    // Route::get('/security', fn () => view('security.index'))->name('security.index');
    Route::get('/security/private-key', fn () => view('security.private-key.index', [
        'privateKeys' => PrivateKey::ownedByCurrentTeam(['name', 'uuid', 'is_git_related'])->get()
    ]))->name('security.private-key.index');
    Route::get('/security/private-key/new', SecurityPrivateKeyCreate::class)->name('security.private-key.create');
    Route::get('/security/private-key/{private_key_uuid}', SecurityPrivateKeyShow::class)->name('security.private-key.show');

    Route::get('/security/api-tokens', ApiTokens::class)->name('security.api-tokens');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/source/new', fn () => view('source.new'))->name('source.new');
    Route::get('/sources', function () {
        $sources = currentTeam()->sources();
        return view('source.all', [
            'sources' => $sources,
        ]);
    })->name('source.all');
    Route::get('/source/github/{github_app_uuid}', GitHubChange::class)->name('source.github.show');
    Route::get('/source/gitlab/{gitlab_app_uuid}', function (Request $request) {
        $gitlab_app = GitlabApp::where('uuid', request()->gitlab_app_uuid)->first();
        return view('source.gitlab.show', [
            'gitlab_app' => $gitlab_app,
        ]);
    })->name('source.gitlab.show');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/destinations', function () {
        $servers = Server::all();
        $destinations = collect([]);
        foreach ($servers as $server) {
            $destinations = $destinations->merge($server->destinations());
        }
        return view('destination.all', [
            'destinations' => $destinations,
        ]);
    })->name('destination.all');
    Route::get('/destination/new', function () {
        $servers = Server::isUsable()->get();
        $pre_selected_server_uuid = data_get(request()->query(), 'server');
        if ($pre_selected_server_uuid) {
            $server = $servers->firstWhere('uuid', $pre_selected_server_uuid);
            if ($server) {
                $server_id = $server->id;
            }
        }
        return view('destination.new', [
            "servers" => $servers,
            "server_id" => $server_id ?? null,
        ]);
    })->name('destination.new');
    Route::get('/destination/{destination_uuid}', function () {
        $standalone_dockers = StandaloneDocker::where('uuid', request()->destination_uuid)->first();
        $swarm_dockers = SwarmDocker::where('uuid', request()->destination_uuid)->first();
        if (!$standalone_dockers && !$swarm_dockers) {
            abort(404);
        }
        $destination = $standalone_dockers ? $standalone_dockers : $swarm_dockers;
        return view('destination.show', [
            'destination' => $destination->load(['server']),
        ]);
    })->name('destination.show');
});

Route::any('/{any}', function () {
    if (auth()->user()) {
        return redirect(RouteServiceProvider::HOME);
    }
    return redirect()->route('login');
})->where('any', '.*');
