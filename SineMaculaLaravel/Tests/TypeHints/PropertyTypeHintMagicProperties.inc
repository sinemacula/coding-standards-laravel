<?php

namespace App;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Foundation\Exceptions\Handler;
use Illuminate\Foundation\Http\Kernel;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Support\Providers\EventServiceProvider;
use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\ServiceProvider;

class SyncCommand extends Command
{
    protected $signature = 'app:sync';

    protected $description = 'Sync';

    protected $help = 'Long help';

    protected $aliases = ['app:s'];

    protected $hidden = false;
}

class Post extends Model
{
    protected $table = 'posts';

    protected $primaryKey = 'uuid';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $dateFormat = 'Y-m-d';

    protected $with = ['author'];

    protected $withCount = ['comments'];

    protected $perPage = 25;

    protected $fillable = ['title'];

    protected $guarded = [];

    protected $visible = ['title'];

    protected $casts = ['published_at' => 'datetime'];

    protected $dates = ['published_at'];

    protected $appends = ['slug'];

    protected $attributes = ['title' => ''];

    protected $dispatchesEvents = [];

    protected $touches = ['author'];

    protected $observables = ['synced'];

    protected $connection = 'mysql';

    protected $escapeWhenCastingToString = true;

    public static $snakeAttributes = false;
}

class PostFactory extends Factory
{
    protected $model = Post::class;
}

class CreatePostsTable extends Migration
{
    public $withinTransaction = false;
}

class PostCollection extends ResourceCollection
{
    public static $wrap = 'data';

    public $collects = Post::class;
}

class VerifyCsrf extends VerifyCsrfToken
{
    protected $except = ['webhooks/*'];

    protected $addHttpCookie = true;
}

class Proxies extends TrustProxies
{
    protected $proxies = ['10.0.0.0/8'];

    protected $headers = 30;
}

class HttpKernel extends Kernel
{
    protected $middleware = [];

    protected $middlewareGroups = [];

    protected $middlewareAliases = [];

    protected $middlewarePriority = [];

    protected $routeMiddleware = [];

    protected $commands = [];

    protected $bootstrappers = [];
}

class AppServiceProvider extends ServiceProvider
{
    public $bindings = [];

    public $singletons = [];

    protected $defer = true;

    protected $policies = [];

    protected $namespace = 'App\Http\Controllers';
}

class EventProvider extends EventServiceProvider
{
    protected $listen = [];

    protected $subscribe = [];

    protected $observers = [];
}

class ExceptionHandler extends Handler
{
    protected $dontReport = [];

    protected $dontFlash = ['password'];

    protected $levels = [];
}
