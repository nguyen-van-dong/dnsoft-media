<?php

namespace DnSoft\Media\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use DnSoft\Core\Traits\CacheableTrait;

/**
 * DnSoft\Media\Models\Media
 *
 * @property int $id
 * @property string $name
 * @property string $file_name
 * @property string $disk
 * @property string $mime_type
 * @property int $size
 * @property string|null $author_type
 * @property int|null $author_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $author
 * @property-read string $extension
 * @property-read string|null $type
 * @property-read \Illuminate\Database\Eloquent\Collection|\DnSoft\Media\Models\Mediable[] $mediables
 * @property-read int|null $mediables_count
 * @method static \Rinvex\Cacheable\EloquentBuilder|\DnSoft\Media\Models\Media newModelQuery()
 * @method static \Rinvex\Cacheable\EloquentBuilder|\Dnsoft\Media\Models\Media newQuery()
 * @method static \Rinvex\Cacheable\EloquentBuilder|\Dnsoft\Media\Models\Media query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Dnsoft\Media\Models\Media whereAuthorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Dnsoft\Media\Models\Media whereAuthorType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Dnsoft\Media\Models\Media whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Dnsoft\Media\Models\Media whereDisk($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Dnsoft\Media\Models\Media whereFileName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Dnsoft\Media\Models\Media whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Dnsoft\Media\Models\Media whereMimeType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Dnsoft\Media\Models\Media whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Dnsoft\Media\Models\Media whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Dnsoft\Media\Models\Media whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Media extends Model
{
  use CacheableTrait;

  protected $table = 'medias';

  protected $fillable = [
    'name',
    'file_name',
    'disk',
    'mime_type',
    'size',
    'folder_id',
    'alt',
    'duration',
    'processed_file',
    'visibility',
    'processing_percentage',
    'processed',
  ];

  protected $casts = [
    'processed' => 'boolean',
  ];

  protected static function boot()
  {
    parent::boot();

    self::deleting(function (Media $model) {
      $model->filesystem()->deleteDirectory(
        $model->getDirectory()
      );
    });
  }

  public function author()
  {
    return $this->morphTo();
  }

  /**
   * 
   */
  public function folder()
  {
    return $this->belongsTo(Folder::class);
  }

  /**
   * Get the file type.
   *
   * @return string|null
   */
  public function getTypeAttribute()
  {
    return Str::before($this->mime_type, '/') ?? null;
  }

  /**
   * Get the file extension.
   *
   * @return string
   */
  public function getExtensionAttribute()
  {
    return pathinfo($this->file_name, PATHINFO_EXTENSION);
  }

  /**
   * Determine if the file is of the specified type.
   *
   * @param  string  $type
   * @return bool
   */
  public function isOfType(string $type)
  {
    return $this->type === $type;
  }

  /**
   * Get the url to the file.
   *
   * @param  string  $conversion
   * @return mixed
   */
  public function getUrl(Folder $folder =null, string $conversion = '')
  {
    return $this->filesystem()->url(
      $this->getPath($folder, $conversion)
    );
  }

  /**
   * Get the full path to the file.
   *
   * @param  string  $conversion
   * @return mixed
   */
  public function getFullPath(Folder $folder = null, string $conversion = '')
  {
    return $this->filesystem()->path(
      $this->getPath($folder, $conversion)
    );
  }

  /**
   * Get the path to the file on disk.
   *
   * @param  string  $conversion
   * @return string
   */
  public function getPath(Folder $selectedFolder = null, string $conversion = '')
  {
    $directory = $this->getDirectory($selectedFolder ? $selectedFolder->name : null);

    if ($conversion) {
      $directory .= '/conversions/' . $conversion;
    }

    return $directory . '/' . $this->file_name;
  }

  /**
   * Get the path to the file on disk.
   *
   * @param  string  $conversion
   * @return string
   */
  public function getM3u8Path()
  {
    $directory = $this->getDirectory($this->folder ? $this->folder->name : null);

    return $directory . '/' . $this->processed_file;
  }

  public function getM3u8Url()
  {
    return $this->filesystem()->url(
      $this->getM3u8Path()
    );
  }

  /**
   * Get the directory for files on disk.
   *
   * @return mixed
   */
  public function getDirectory($parentFolder = '')
  {
    $folder = $parentFolder ? $parentFolder : '';
    return $folder.'/'.$this->created_at->format('Y/m') . '/' . $this->getKey();
  }

  /**
   * Get the filesystem where the associated file is stored.
   *
   * @return \Illuminate\Contracts\Filesystem\Filesystem|\Illuminate\Filesystem\FilesystemAdapter
   */
  public function filesystem()
  {
    return Storage::disk($this->disk);
  }

  public function mediables()
  {
    return $this->hasMany(Mediable::class);
  }

  public function getThumbAttribute()
  {
    return $this->getUrl($this->folder()->first(), 'thumb');
  }

  public function getUrlAttribute()
  {
    return $this->getUrl($this->folder()->first());
  }

  public function __toString()
  {
    return $this->getUrl($this->folder()->first());
  }

  public function mediaTags()
  {
    return $this->hasOne(MediaTag::class, 'media_id');
  }

  // public function crop($width, $height, $format = 'jpg', $quality = 80)
  // {
  //   if (config('media.imageproxy.enable') === true) {
  //     $urlCdn = config('media.imageproxy.server');
  //     return "{$urlCdn}/{$width}x{$height},q{$quality},{$format}/" . $this->getUrl();
  //   } else {
  //     return $this->getUrl();
  //   }
  // }
}
