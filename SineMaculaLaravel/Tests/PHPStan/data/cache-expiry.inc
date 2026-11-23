<?php

namespace CacheExpiry;

use Illuminate\Cache\CacheManager;
use Illuminate\Cache\TaggedCache;
use Illuminate\Contracts\Cache\LockProvider;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Contracts\Cache\Store;
use Illuminate\Support\Facades\Cache;

class Basket
{
    public function put(string $key, mixed $value, ?int $ttl = null): void
    {
    }

    public function forever(string $key, mixed $value): void
    {
    }
}

class Writes
{
    public function __construct(
        private Repository $repository,
        private Store $store,
        private LockProvider $locks,
        private CacheManager $manager,
        private TaggedCache $tagged,
        private ?Repository $optional,
        private Basket $basket,
        private Repository|Store $either,
        private Repository|Basket $maybe,
    ) {
    }

    public function forbidden(): void
    {
        Cache::forever('k', 'v');
        Cache::rememberForever('k', fn () => 1);
        Cache::sear('k', fn () => 1);
        Cache::FOREVER('k', 'v');
        cache()->forever('k', 'v');
        $this->repository->forever('k', 'v');
        $this->store->forever('k', 'v');
        $this->manager->forever('k', 'v');
        Cache::store('redis')->forever('k', 'v');
        Cache::tags(['a'])->forever('k', 'v');
        $this->optional?->forever('k', 'v');
        $this->either->forever('k', 'v');
        $this->basket->forever('k', 'v');
        $this->maybe->forever('k', 'v');
    }

    public function writes(mixed $mixed, ?int $maybeNull, array $args): void
    {
        Cache::put('k', 'v');
        Cache::put('k', 'v', null);
        Cache::put('k', 'v', 60);
        Cache::put('k', 'v', $maybeNull);
        Cache::put(key: 'k', value: 'v');
        Cache::put(ttl: 60, value: 'v', key: 'k');
        Cache::put(['k' => 'v']);
        Cache::put(['k' => 'v'], 60);
        Cache::put(value: 60, key: ['k' => 'v']);
        Cache::put(ttl: 60, key: ['k' => 'v'], value: null);
        Cache::put('k', 'v', ...$args);
        Cache::put('k', 'v', null, ...$args);
        Cache::put(...);
        $this->repository->add('k', 'v');
        $this->repository->add('k', 'v', 60);
        $this->repository->remember('k', null, fn () => 1);
        $this->repository->remember('k', 60, fn () => 1);
        $this->repository->remember('k', fn () => null, fn () => 1);
        $this->repository->rememberWithWarmth('k', null, fn () => 1);
        $this->repository->set('k', 'v');
        $this->repository->set(['k' => 'v'], 60);
        $this->repository->setMultiple(['k' => 'v']);
        $this->repository->putMany(['k' => 'v']);
        $this->repository->putMany(['k' => 'v'], ttl: 60);
        $this->store->put('k', 'v', null);
        $this->store->put('k', 'v', seconds: 60);
        $this->store->putMany(['k' => 'v'], 60);
        $this->manager->put('k', 'v');
        $this->tagged->put('k', 'v');
        $this->optional?->add('k', 'v');
        $this->either->put('k', 'v', null);
        $this->maybe->put('k', 'v');
        $this->basket->put('k', 'v');
        $mixed->put('k', 'v');
    }

    public function locks(int $seconds): void
    {
        Cache::lock('k');
        Cache::lock('k', 0);
        Cache::lock('k', -1);
        Cache::lock('k', 10);
        Cache::lock('k', $seconds);
        $this->locks->lock('k');
        $this->locks->lock('k', seconds: 10);
        Cache::store()->lock('k');
        Cache::store()->lock('k', seconds: 10);
        Cache::lock('k', 10)->block(5);
        Cache::restoreLock('k', 'owner');
    }

    public function helper(): void
    {
        cache(['k' => 'v']);
        cache(['k' => 'v'], null);
        cache(['k' => 'v'], 60);
        cache(['k' => 'v'], default: 60);
        cache('k');
    }
}
