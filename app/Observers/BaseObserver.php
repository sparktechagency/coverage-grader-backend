<?php

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class BaseObserver
{

    protected function flushCache(Model $model): void
    {
        Cache::tags($model->getTable())->flush();
    }

    public function created(Model $model): void
    {
        $this->flushCache($model);
    }

    public function updated(Model $model): void
    {
        $this->flushCache($model);
    }

    public function deleted(Model $model): void
    {
        $this->flushCache($model);
    }
}
