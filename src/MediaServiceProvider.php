<?php

namespace Dnsoft\Media;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Intervention\Image\Image;
use Dnsoft\Acl\Facades\Permission;
use Dnsoft\Core\Events\CoreAdminMenuRegistered;
use Dnsoft\Media\Events\MediaUploadedEvent;
use Dnsoft\Media\Facades\Conversion;
use Dnsoft\Media\Jobs\PerformConversions;
use Dnsoft\Media\Models\Media;
use Dnsoft\Media\Models\Mediable;
use Dnsoft\Media\Models\MediaTag;
use Dnsoft\Media\Repositories\MediableRepository;
use Dnsoft\Media\Repositories\MediableRepositoryInterace;
use Dnsoft\Media\Repositories\MediaRepository;
use Dnsoft\Media\Repositories\MediaRepositoryInterface;
use Dnsoft\Media\Repositories\MediaTagRepositoryInterface;

class MediaServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/media.php', 'media'
        );

        $this->app->singleton(ConversionRegistry::class);
        $this->app->singleton(MediaUploader::class);

        $this->app->singleton(MediaRepositoryInterface::class, function () {
            return new MediaRepository(new Media());
        });

        $this->app->singleton(MediableRepositoryInterace::class, function () {
            return new MediableRepository(new Mediable());
        });
        $this->app->singleton(MediaTagRepositoryInterface::class, function () {
            return new MediaRepository(new MediaTag());
        });
    }

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/media.php' => config_path('media.php'),
        ], 'config');

        $this->publishes([
            __DIR__ . '/../public' => public_path('vendor/media'),
        ], 'media');

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'media');

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'media');

        $this->registerDefaultConversion();

        $this->loadRoutes();

        $this->registerPermissions();

        $this->registerAdminMenus();

        $this->registerBlade();
    }

    protected function registerDefaultConversion()
    {
        $thumbSize = config('media.thumbsize', []);
        if ($thumbSize && count($thumbSize) == 2) {
            Conversion::register('thumb', function (Image $image) use ($thumbSize) {
                return $image->fit($thumbSize[0], $thumbSize[1]);
            });

            Event::listen(MediaUploadedEvent::class, function (MediaUploadedEvent $event) {
                PerformConversions::dispatch(
                    $event->media, ['thumb']
                );
            });
        }
    }

    protected function loadRoutes()
    {
        Route::middleware(['web', 'admin.auth'])
            ->prefix('admin')
            ->group(__DIR__.'/../routes/admin.php');

//        Route::middleware(['web'])
//            ->group(__DIR__.'/../routes/web.php');

//        Route::middleware(['web'])->group('./../routes/web.php');
    }

    protected function registerPermissions()
    {
        Permission::add('media.admin.index', __('media::permission.media.index'));
        Permission::add('media.admin.upload', __('media::permission.media.upload'));
    }

    private function registerAdminMenus()
    {
        Event::listen(CoreAdminMenuRegistered::class, function ($menu) {
            $menu->add(__('Media'), [
                'route' => 'media.admin.media.index',
                'parent' => $menu->system->id
            ])->data('order', 12)->prepend('<i class="fas fa-boxes"></i>');
        });
    }

    public function registerBlade()
    {
        Blade::include('media::admin.modals.tag-modal', 'modalMedia');
        Blade::include('media::admin.forms.file-manager', 'fileManager');
        Blade::include('media::admin.forms.single-file', 'singleFile');
    }
}
