<?php

namespace Tickets2\Model;

use MODX\Revolution\modX;
use MODX\Revolution\Sources\modMediaSource;
use xPDO\Om\xPDOSimpleObject;

class TicketFile extends xPDOSimpleObject
{
    public $phpThumb;
    public $mediaSource;

    public function prepareSource(modMediaSource $mediaSource = null)
    {
        if ($mediaSource) {
            $this->mediaSource = $mediaSource;
        } elseif (empty($this->mediaSource) && $source = $this->get('source')) {
            if ($mediaSource = $this->xpdo->getObject(modMediaSource::class, ['id' => $source])) {
                $mediaSource->set('ctx', $this->xpdo->context->key);
                $mediaSource->initialize();
                $this->mediaSource = $mediaSource;
            } else {
                return 'Could not initialize media source with id = ' . $source;
            }
        }

        return !empty($this->mediaSource) && $this->mediaSource instanceof modMediaSource;
    }

    public function generateThumbnails(modMediaSource $mediaSource = null)
    {
        if ($this->get('type') != 'image') {
            return true;
        }

        $prepare = $this->prepareSource($mediaSource);
        if ($prepare !== true) {
            return $prepare;
        }

        $this->mediaSource->errors = [];
        $filename = $this->get('path') . $this->get('file');
        $info = $this->mediaSource->getObjectContents($filename);
        if (!is_array($info)) {
            return "[Tickets] Could not retrieve contents of file {$filename} from media source.";
        } elseif (!empty($this->mediaSource->errors['file'])) {
            return "[Tickets] Could not retrieve file {$filename} from media source: " . $this->mediaSource->errors['file'];
        }

        $properties = $this->mediaSource->getProperties();
        $thumbnails = [];
        if (array_key_exists('thumbnails', $properties) && !empty($properties['thumbnails']['value'])) {
            $thumbnails = json_decode($properties['thumbnails']['value'], true);
        } elseif (array_key_exists('thumbnail', $properties) && !empty($properties['thumbnail']['value'])) {
            $thumbnails = json_decode($properties['thumbnail']['value'], true);
        }

        if (empty($thumbnails)) {
            $thumbnails = [
                'thumb' => [
                    'w' => 120,
                    'h' => 90,
                    'q' => 90,
                    'zc' => 1,
                    'bg' => '000000',
                    'f' => !empty($properties['thumbnailType']['value'])
                        ? $properties['thumbnailType']['value']
                        : 'jpg',
                ],
            ];
        }

        foreach ($thumbnails as $k => $options) {
            if (empty($options['f'])) {
                $options['f'] = !empty($properties['thumbnailType']['value'])
                    ? $properties['thumbnailType']['value']
                    : 'jpg';
            }
            $options['name'] = !is_numeric($k) ? $k : 'thumb';
            if ($image = $this->makeThumbnail($options, $info)) {
                $this->saveThumbnail($image, $options);
            }
        }

        return true;
    }

    public function makeThumbnail($options = [], array $info)
    {
        if (!class_exists('modPhpThumb')) {
            require MODX_CORE_PATH . 'model/phpthumb/modphpthumb.class.php';
        }
        $phpThumb = new \modPhpThumb($this->xpdo);
        $phpThumb->initialize();

        $tf = tempnam(MODX_BASE_PATH, 'tkt_');
        file_put_contents($tf, $info['content']);
        $phpThumb->setSourceFilename($tf);
        foreach ($options as $k => $v) {
            $phpThumb->setParameter($k, $v);
        }

        if ($phpThumb->GenerateThumbnail()) {
            if ($phpThumb->RenderOutput()) {
                @unlink($phpThumb->sourceFilename);
                @unlink($tf);
                $this->xpdo->log(modX::LOG_LEVEL_INFO, '[Tickets] phpThumb messages for "' . $this->get('url') . '". ' .
                    print_r($phpThumb->debugmessages, 1));

                return $phpThumb->outputImageData;
            }
        }
        @unlink($phpThumb->sourceFilename);
        @unlink($tf);

        $this->xpdo->log(modX::LOG_LEVEL_ERROR, '[Tickets] Could not generate thumbnail for "' .
            $this->get('url') . '". ' . print_r($phpThumb->debugmessages, 1));

        return false;
    }

    public function saveThumbnail($raw_image, $options = [])
    {
        $filename = preg_replace('#\.[a-z]+$#i', '', $this->get('file')) . '.' . $options['f'];
        $name = !empty($options['name']) ? $options['name'] : 'thumb';
        $thumb_dir = preg_replace('#[^\w]#', '', $name);
        $path = $this->get('path') . $thumb_dir . '/';

        $this->mediaSource->createContainer($path, '/');
        if ($file = $this->mediaSource->createObject($path, $filename, $raw_image)) {
            $url = $this->mediaSource->getObjectUrl($path . $filename);
            $thumbs = $this->get('thumbs');
            if (!is_array($thumbs)) {
                $thumbs = [];
            }
            $thumbs[$name] = $url;
            $this->set('thumbs', $thumbs);
            if ($name == 'thumb') {
                $this->set('thumb', $url);
            }

            return $this->save();
        }

        return false;
    }

    public function save($cacheFlag = null)
    {
        if ($this->isDirty('parent')) {
            if ($this->prepareSource()) {
                $old_path = $this->get('path');
                $file = $this->get('file');
                $new_path = $this->get('parent') . '/';

                $this->mediaSource->createContainer($new_path, '/');
                if ($this->mediaSource->moveObject($old_path . $file, $new_path)) {
                    $this->set('path', $new_path);
                    $this->set('url', $this->mediaSource->getObjectUrl($new_path . $file));
                }
                if (!$thumbs = $this->get('thumbs')) {
                    $thumbs = ['thumb' => $this->get('thumb')];
                }
                foreach ($thumbs as $key => $thumb) {
                    if (empty($thumb)) {
                        continue;
                    }
                    if (strpos($thumb, '/' . $key . '/') !== false) {
                        $old_path_thumb = $old_path . $key . '/';
                        $new_path_thumb = $new_path . $key . '/';
                        $this->mediaSource->createContainer($new_path_thumb, '/');
                    } else {
                        $old_path_thumb = $old_path;
                        $new_path_thumb = $new_path;
                    }
                    $tmp = explode('/', $thumb);
                    $thumb = end($tmp);
                    if ($this->mediaSource->moveObject($old_path_thumb . $thumb, $new_path_thumb)) {
                        $thumbs[$key] = $this->mediaSource->getObjectUrl($new_path_thumb . $thumb);
                        if ($key == 'thumb') {
                            $this->set('thumb', $this->mediaSource->getObjectUrl($new_path_thumb . $thumb));
                        }
                    }
                }
                $this->set('thumbs', $thumbs);
            }
        }

        return parent::save($cacheFlag);
    }

    public function remove(array $ancestors = [])
    {
        if ($this->prepareSource() === true) {
            if ($this->mediaSource->removeObject($this->get('path') . $this->get('file'))) {
                if (!$thumbs = $this->get('thumbs')) {
                    $thumbs = ['thumb' => $this->get('thumb')];
                }
                foreach ($thumbs as $key => $thumb) {
                    if (empty($thumb)) {
                        continue;
                    }
                    $path = strpos($thumb, '/' . $key . '/') !== false
                        ? $this->get('path') . $key . '/'
                        : $this->get('path');
                    $tmp = explode('/', $thumb);
                    $filename = end($tmp);
                    $this->mediaSource->removeObject($path . $filename);
                }
            }
        }

        return parent::remove($ancestors);
    }
}