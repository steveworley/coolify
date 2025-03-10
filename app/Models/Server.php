<?php

namespace App\Models;

use App\Actions\Server\InstallDocker;
use App\Enums\ProxyStatus;
use App\Enums\ProxyTypes;
use App\Notifications\Server\Revived;
use App\Notifications\Server\Unreachable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\DB;
use Spatie\SchemalessAttributes\Casts\SchemalessAttributes;
use Spatie\SchemalessAttributes\SchemalessAttributesTrait;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;

class Server extends BaseModel
{
    use SchemalessAttributesTrait;
    public static $batch_counter = 0;

    protected static function booted()
    {
        static::saving(function ($server) {
            $payload = [];
            if ($server->user) {
                $payload['user'] = Str::of($server->user)->trim();
            }
            if ($server->ip) {
                $payload['ip'] = Str::of($server->ip)->trim();
            }
            $server->forceFill($payload);
        });
        static::created(function ($server) {
            ServerSetting::create([
                'server_id' => $server->id,
            ]);
        });
        static::deleting(function ($server) {
            $server->destinations()->each(function ($destination) {
                $destination->delete();
            });
            $server->settings()->delete();
        });
    }

    public $casts = [
        'proxy' => SchemalessAttributes::class,
        'logdrain_axiom_api_key' => 'encrypted',
        'logdrain_newrelic_license_key' => 'encrypted',
    ];
    protected $schemalessAttributes = [
        'proxy',
    ];
    protected $guarded = [];

    static public function isReachable()
    {
        return Server::ownedByCurrentTeam()->whereRelation('settings', 'is_reachable', true);
    }

    static public function ownedByCurrentTeam(array $select = ['*'])
    {
        $teamId = currentTeam()->id;
        $selectArray = collect($select)->concat(['id']);
        return Server::whereTeamId($teamId)->with('settings', 'swarmDockers', 'standaloneDockers')->select($selectArray->all())->orderBy('name');
    }

    static public function isUsable()
    {
        return Server::ownedByCurrentTeam()->whereRelation('settings', 'is_reachable', true)->whereRelation('settings', 'is_usable', true)->whereRelation('settings', 'is_swarm_worker', false)->whereRelation('settings', 'is_build_server', false);
    }

    static public function destinationsByServer(string $server_id)
    {
        $server = Server::ownedByCurrentTeam()->get()->where('id', $server_id)->firstOrFail();
        $standaloneDocker = collect($server->standaloneDockers->all());
        $swarmDocker = collect($server->swarmDockers->all());
        return $standaloneDocker->concat($swarmDocker);
    }
    public function settings()
    {
        return $this->hasOne(ServerSetting::class);
    }
    public function addInitialNetwork()
    {
        if ($this->id === 0) {
            if ($this->isSwarm()) {
                SwarmDocker::create([
                    'id' => 0,
                    'name' => 'coolify',
                    'network' => 'coolify-overlay',
                    'server_id' => $this->id,
                ]);
            } else {
                StandaloneDocker::create([
                    'id' => 0,
                    'name' => 'coolify',
                    'network' => 'coolify',
                    'server_id' => $this->id,
                ]);
            }
        } else {
            if ($this->isSwarm()) {
                SwarmDocker::create([
                    'name' => 'coolify-overlay',
                    'network' => 'coolify-overlay',
                    'server_id' => $this->id,
                ]);
            } else {
                StandaloneDocker::create([
                    'name' => 'coolify',
                    'network' => 'coolify',
                    'server_id' => $this->id,
                ]);
            }
        }
    }
    public function proxyType()
    {
        $proxyType = $this->proxy->get('type');
        if ($proxyType === ProxyTypes::NONE->value) {
            return $proxyType;
        }
        if (is_null($proxyType)) {
            $this->proxy->type = ProxyTypes::TRAEFIK_V2->value;
            $this->proxy->status = ProxyStatus::EXITED->value;
            $this->save();
        }
        return $this->proxy->get('type');
    }
    public function scopeWithProxy(): Builder
    {
        return $this->proxy->modelScope();
    }

    public function isLocalhost()
    {
        return $this->ip === 'host.docker.internal' || $this->id === 0;
    }
    static public function buildServers($teamId)
    {
        return Server::whereTeamId($teamId)->whereRelation('settings', 'is_reachable', true)->whereRelation('settings', 'is_build_server', true);
    }
    public function skipServer()
    {
        if ($this->ip === '1.2.3.4') {
            ray('skipping 1.2.3.4');
            return true;
        }
        return false;
    }
    public function isServerReady(int $tries = 3)
    {
        if ($this->skipServer()) {
            return false;
        }
        $serverUptimeCheckNumber = $this->unreachable_count;
        if ($this->unreachable_count < $tries) {
            $serverUptimeCheckNumber = $this->unreachable_count + 1;
        }
        if ($this->unreachable_count > $tries) {
            $serverUptimeCheckNumber = $tries;
        }

        $serverUptimeCheckNumberMax = $tries;

        // ray('server: ' . $this->name);
        // ray('serverUptimeCheckNumber: ' . $serverUptimeCheckNumber);
        // ray('serverUptimeCheckNumberMax: ' . $serverUptimeCheckNumberMax);

        $result = $this->validateConnection();
        if ($result) {
            if ($this->unreachable_notification_sent === true) {
                $this->update(['unreachable_notification_sent' => false]);
            }
            return true;
        } else {
            if ($serverUptimeCheckNumber >= $serverUptimeCheckNumberMax) {
                // Reached max number of retries
                if ($this->unreachable_notification_sent === false) {
                    ray('Server unreachable, sending notification...');
                    $this->team?->notify(new Unreachable($this));
                    $this->update(['unreachable_notification_sent' => true]);
                }
                if ($this->settings->is_reachable === true) {
                    $this->settings()->update([
                        'is_reachable' => false,
                    ]);
                }

                foreach ($this->applications() as $application) {
                    $application->update(['status' => 'exited']);
                }
                foreach ($this->databases() as $database) {
                    $database->update(['status' => 'exited']);
                }
                foreach ($this->services()->get() as $service) {
                    $apps = $service->applications()->get();
                    $dbs = $service->databases()->get();
                    foreach ($apps as $app) {
                        $app->update(['status' => 'exited']);
                    }
                    foreach ($dbs as $db) {
                        $db->update(['status' => 'exited']);
                    }
                }
            } else {
                $this->update([
                    'unreachable_count' => $this->unreachable_count + 1,
                ]);
            }
            return false;
        }
    }
    public function getDiskUsage()
    {
        return instant_remote_process(["df /| tail -1 | awk '{ print $5}' | sed 's/%//g'"], $this, false);
    }
    public function definedResources()
    {
        $applications = $this->applications();
        $databases = $this->databases();
        $services = $this->services();
        return $applications->concat($databases)->concat($services->get());
    }
    public function stopUnmanaged($id)
    {
        return instant_remote_process(["docker stop -t 0 $id"], $this);
    }
    public function restartUnmanaged($id)
    {
        return instant_remote_process(["docker restart $id"], $this);
    }
    public function startUnmanaged($id)
    {
        return instant_remote_process(["docker start $id"], $this);
    }
    public function loadUnmanagedContainers()
    {
        $containers = instant_remote_process(["docker ps -a  --format '{{json .}}' "], $this);
        $containers = format_docker_command_output_to_json($containers);
        $containers = $containers->map(function ($container) {
            $labels = data_get($container, 'Labels');
            if (!str($labels)->contains("coolify.managed")) {
                return $container;
            }
            return null;
        });
        $containers = $containers->filter();
        return collect($containers);
    }
    public function hasDefinedResources()
    {
        $applications = $this->applications()->count() > 0;
        $databases = $this->databases()->count() > 0;
        $services = $this->services()->count() > 0;
        if ($applications || $databases || $services) {
            return true;
        }
        return false;
    }

    public function databases()
    {
        return $this->destinations()->map(function ($standaloneDocker) {
            $postgresqls = data_get($standaloneDocker, 'postgresqls', collect([]));
            $redis = data_get($standaloneDocker, 'redis', collect([]));
            $mongodbs = data_get($standaloneDocker, 'mongodbs', collect([]));
            $mysqls = data_get($standaloneDocker, 'mysqls', collect([]));
            $mariadbs = data_get($standaloneDocker, 'mariadbs', collect([]));
            return $postgresqls->concat($redis)->concat($mongodbs)->concat($mysqls)->concat($mariadbs);
        })->filter(function ($item) {
            return data_get($item, 'name') !== 'coolify-db';
        })->flatten();
    }
    public function applications()
    {
        $applications = $this->destinations()->map(function ($standaloneDocker) {
            return $standaloneDocker->applications;
        })->flatten();
        $additionalApplicationIds = DB::table('additional_destinations')->where('server_id', $this->id)->get('application_id');
        $additionalApplicationIds = collect($additionalApplicationIds)->map(function ($item) {
            return $item->application_id;
        });
        Application::whereIn('id', $additionalApplicationIds)->get()->each(function ($application) use ($applications) {
            $applications->push($application);
        });
        return $applications;
    }
    public function dockerComposeBasedApplications()
    {
        return $this->applications()->filter(function ($application) {
            return data_get($application, 'build_pack') === 'dockercompose';
        });
    }
    public function dockerComposeBasedPreviewDeployments()
    {
        return $this->previews()->filter(function ($preview) {
            $applicationId = data_get($preview, 'application_id');
            $application = Application::find($applicationId);
            if (!$application) {
                return false;
            }
            return data_get($application, 'build_pack') === 'dockercompose';
        });
    }
    public function services()
    {
        return $this->hasMany(Service::class);
    }
    public function getIp(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (isDev()) {
                    return '127.0.0.1';
                }
                if ($this->isLocalhost()) {
                    return base_ip();
                }
                return $this->ip;
            }
        );
    }
    public function previews()
    {
        return $this->destinations()->map(function ($standaloneDocker) {
            return $standaloneDocker->applications->map(function ($application) {
                return $application->previews;
            })->flatten();
        })->flatten();
    }

    public function destinations()
    {
        $standalone_docker = $this->hasMany(StandaloneDocker::class)->get();
        $swarm_docker = $this->hasMany(SwarmDocker::class)->get();
        $asd = $this->belongsToMany(StandaloneDocker::class, 'additional_destinations')->withPivot('server_id')->get();
        return $standalone_docker->concat($swarm_docker)->concat($asd);
    }

    public function standaloneDockers()
    {
        return $this->hasMany(StandaloneDocker::class);
    }

    public function swarmDockers()
    {
        return $this->hasMany(SwarmDocker::class);
    }

    public function privateKey()
    {
        return $this->belongsTo(PrivateKey::class);
    }

    public function muxFilename()
    {
        return "{$this->ip}_{$this->port}_{$this->user}";
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }
    public function isProxyShouldRun()
    {
        if ($this->proxyType() === ProxyTypes::NONE->value || $this->settings->is_build_server) {
            return false;
        }
        return true;
    }
    public function isFunctional()
    {
        return $this->settings->is_reachable && $this->settings->is_usable;
    }
    public function isLogDrainEnabled()
    {
        return $this->settings->is_logdrain_newrelic_enabled || $this->settings->is_logdrain_highlight_enabled || $this->settings->is_logdrain_axiom_enabled || $this->settings->is_logdrain_custom_enabled;
    }
    public function validateOS(): bool | Stringable
    {
        $os_release = instant_remote_process(['cat /etc/os-release'], $this);
        $releaseLines = collect(explode("\n", $os_release));
        $collectedData = collect([]);
        foreach ($releaseLines as $line) {
            $item = Str::of($line)->trim();
            $collectedData->put($item->before('=')->value(), $item->after('=')->lower()->replace('"', '')->value());
        }
        $ID = data_get($collectedData, 'ID');
        // $ID_LIKE = data_get($collectedData, 'ID_LIKE');
        // $VERSION_ID = data_get($collectedData, 'VERSION_ID');
        $supported = collect(SUPPORTED_OS)->filter(function ($supportedOs) use ($ID) {
            if (str($supportedOs)->contains($ID)) {
                return str($ID);
            }
        });
        if ($supported->count() === 1) {
            // ray('supported');
            return str($supported->first());
        } else {
            // ray('not supported');
            return false;
        }
    }
    public function isSwarm()
    {
        return data_get($this, 'settings.is_swarm_manager') || data_get($this, 'settings.is_swarm_worker');
    }
    public function isSwarmManager()
    {
        return data_get($this, 'settings.is_swarm_manager');
    }
    public function isSwarmWorker()
    {
        return data_get($this, 'settings.is_swarm_worker');
    }
    public function validateConnection()
    {
        config()->set('coolify.mux_enabled', false);

        $server = Server::find($this->id);
        if (!$server) {
            return false;
        }
        if ($server->skipServer()) {
            return false;
        }
        // EC2 does not have `uptime` command, lol

        $uptime = instant_remote_process(['ls /'], $server, false);
        if (!$uptime) {
            $server->settings()->update([
                'is_reachable' => false,
            ]);
            return false;
        } else {
            $server->settings()->update([
                'is_reachable' => true,
            ]);
            $server->update([
                'unreachable_count' => 0,
            ]);
        }

        if (data_get($server, 'unreachable_notification_sent') === true) {
            $server->team?->notify(new Revived($server));
            $server->update(['unreachable_notification_sent' => false]);
        }

        return true;
    }
    public function installDocker()
    {
        $activity = InstallDocker::run($this);
        return $activity;
    }
    public function validateDockerEngine($throwError = false)
    {
        $dockerBinary = instant_remote_process(["command -v docker"], $this, false);
        if (is_null($dockerBinary)) {
            $this->settings->is_usable = false;
            $this->settings->save();
            if ($throwError) {
                throw new \Exception('Server is not usable. Docker Engine is not installed.');
            }
            return false;
        }
        try {
            instant_remote_process(["docker version"], $this);
        } catch (\Throwable $e) {
            $this->settings->is_usable = false;
            $this->settings->save();
            if ($throwError) {
                throw new \Exception('Server is not usable. Docker Engine is not running.');
            }
            return false;
        }
        $this->settings->is_usable = true;
        $this->settings->save();
        $this->validateCoolifyNetwork(isSwarm: false, isBuildServer: $this->settings->is_build_server);
        return true;
    }
    public function validateDockerCompose($throwError = false)
    {
        $dockerCompose = instant_remote_process(["docker compose version"], $this, false);
        if (is_null($dockerCompose)) {
            $this->settings->is_usable = false;
            $this->settings->save();
            if ($throwError) {
                throw new \Exception('Server is not usable. Docker Compose is not installed.');
            }
            return false;
        }
        $this->settings->is_usable = true;
        $this->settings->save();
        return true;
    }
    public function validateDockerSwarm()
    {
        $swarmStatus = instant_remote_process(["docker info|grep -i swarm"], $this, false);
        $swarmStatus = str($swarmStatus)->trim()->after(':')->trim();
        if ($swarmStatus === 'inactive') {
            throw new \Exception('Docker Swarm is not initiated. Please join the server to a swarm before continuing.');
            return false;
        }
        $this->settings->is_usable = true;
        $this->settings->save();
        $this->validateCoolifyNetwork(isSwarm: true);
        return true;
    }
    public function validateDockerEngineVersion()
    {
        $dockerVersion = instant_remote_process(["docker version|head -2|grep -i version| awk '{print $2}'"], $this, false);
        $dockerVersion = checkMinimumDockerEngineVersion($dockerVersion);
        if (is_null($dockerVersion)) {
            $this->settings->is_usable = false;
            $this->settings->save();
            return false;
        }
        $this->settings->is_reachable = true;
        $this->settings->is_usable = true;
        $this->settings->save();
        return true;
    }
    public function validateCoolifyNetwork($isSwarm = false, $isBuildServer = false)
    {
        if ($isBuildServer) {
            return;
        }
        if ($isSwarm) {
            return instant_remote_process(["docker network create --attachable --driver overlay coolify-overlay >/dev/null 2>&1 || true"], $this, false);
        } else {
            return instant_remote_process(["docker network create coolify --attachable >/dev/null 2>&1 || true"], $this, false);
        }
    }
}
