<?php

namespace CacheExpiryConfig;

use Illuminate\Support\Facades\Cache;

Cache::forever('k', 'v');
Cache::increment('k');
Cache::add('k', 'v');
Cache::put('k', 'v');
cache(['k' => 'v']);
Cache::store()->refresh('k');
Cache::store()->refresh('k', 60);
Cache::store()->refresh('k', ttl: 60);
