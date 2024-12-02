<?php

namespace Tickets2\Processors\Web\Comment;

use MODX\Revolution\Processors\Model\CreateProcessor;
use Tickets2\Model\TicketAuthor;
use Tickets2\Model\TicketComment;
use Tickets2\Model\TicketFile;
use Tickets2\Model\TicketThread;
use Tickets2\Model\Ticket;
use Tickets2\Model\Tickets2Section;

class Create extends CreateProcessor
{
    public $classKey = TicketComment::class;
    public $languageTopics = ['tickets2:default'];
    public $permission = 'comment_save';
    public $beforeSaveEvent = 'OnBeforeCommentSave';
    public $afterSaveEvent = 'OnCommentSave';
    /** @var TicketThread $thread */
    private ?TicketThread $thread;
    private bool $guest = false;

    /**
     * @return bool
     */
    public function checkPermissions(): bool
    {
        $this->guest = (bool)$this->getProperty('allowGuest', false);
        $this->unsetProperty('allowGuest');
        $this->unsetProperty('allowGuestEdit');
        $this->unsetProperty('captcha');

        return !empty($this->permission) && !$this->guest
            ? $this->modx->hasPermission($this->permission)
            : true;
    }

    /**
     * @return bool|null|string
     */
    public function beforeSet(): bool|string|null
    {
        $tid = $this->getProperty('thread');
        if (!$this->thread = $this->modx->getObject(TicketThread::class,
            ['name' => $tid, 'deleted' => 0, 'closed' => 0])
        ) {
            return $this->modx->lexicon('ticket_err_wrong_thread');
        } elseif ($pid = $this->getProperty('parent')) {
            if (!$parent = $this->modx->getObject(TicketComment::class,
                ['id' => $pid, 'published' => 1, 'deleted' => 0])
            ) {
                return $this->modx->lexicon('ticket_comment_err_parent');
            }
        }

        // Required fields
        $requiredFields = array_map('trim', explode(',', $this->getProperty('requiredFields', 'name,email')));
        foreach ($requiredFields as $field) {
            $value = $this->modx->stripTags(trim($this->getProperty($field)));
            if (empty($value)) {
                $this->addFieldError($field, $this->modx->lexicon('field_required'));
            } elseif ($field == 'email' && !preg_match('/.+@.+\..+/i', $value)) {
                $this->setProperty('email', '');
                $this->addFieldError($field, $this->modx->lexicon('ticket_comment_err_email'));
            } else {
                if ($field == 'email') {
                    $value = strtolower($value);
                }
                $this->setProperty($field, $value);
            }
        }
        if (!$text = trim($this->getProperty('text'))) {
            return $this->modx->lexicon('ticket_comment_err_empty');
        }
        if (!$this->getProperty('email') && $this->modx->user->isAuthenticated($this->modx->context->key)) {
            return $this->modx->lexicon('ticket_comment_err_no_email');
        }

        // Additional properties
        $properties = $this->getProperties();
        $add = [];
        $meta = $this->modx->getFieldMeta('TicketComment');
        foreach ($properties as $k => $v) {
            if (!isset($meta[$k])) {
                $add[$k] = $this->modx->stripTags($v);
            }
        }
        if (!$this->getProperty('published')) {
            $add['was_published'] = false;
        }
        unset($properties['requiredFields']);

        // Comment values
        $ip = $this->modx->request->getClientIp();
        $this->setProperties([
            'text' => $text,
            'thread' => $this->thread->id,
            'ip' => $ip['ip'],
            'createdon' => date('Y-m-d H:i:s'),
            'createdby' => $this->modx->user->isAuthenticated($this->modx->context->key)
                ? $this->modx->user->id
                : 0,
            'editedon' => '',
            'editedby' => 0,
            'deleted' => 0,
            'deletedon' => '',
            'deletedby' => 0,
            'properties' => $add,
        ]);
        $this->unsetProperty('action');

        return parent::beforeSet();
    }

    /**
     * @return bool|null|string
     */
    public function beforeSave(): bool|string|null
    {
        /** @var TicketThread $thread */
        if ($thread = $this->object->getOne('Thread')) {
            /** @var Ticket $ticket */
            if ($ticket = $thread->getOne('Ticket')) {
                /** @var Tickets2Section $section */
                if ($section = $ticket->getOne('Section')) {
                    $ratings = $section->getProperties('ratings');
                    if (isset($ratings['min_comment_create']) && $ratings['min_comment_create'] !== '') {
                        if ($profile = $this->modx->getObject(TicketAuthor::class, $this->object->get('createdby'))) {
                            $min = (float)$ratings['min_comment_create'];
                            $rating = $profile->get('rating');
                            if ($rating < $min) {
                                return $this->modx->lexicon('ticket_err_rating_comment', ['rating' => $min]);
                            }
                        }
                    }
                }
            }
        }

        return true;
    }

    /**
     * @return bool
     */
    public function afterSave(): bool
    {
        if ($this->object->get('published')) {
            $this->thread->fromArray([
                'comment_last' => $this->object->get('id'),
                'comment_time' => $this->object->get('createdon'),
            ]);
            $this->thread->save();
        }

        if ($this->guest) {
            if (!isset($_SESSION['TicketComments'])) {
                $_SESSION['TicketComments'] = ['ids' => []];
            }
            $_SESSION['TicketComments']['name'] = $this->object->get('name');
            $_SESSION['TicketComments']['email'] = $this->object->get('email');
            $_SESSION['TicketComments']['ids'][$this->object->get('id')] = 1;
        }

        $this->thread->updateCommentsCount();
        $this->object->clearTicketCache();
        $this->processFiles();

        return parent::afterSave();
    }

    /**
     * Add uploaded files to comment
     *
     * @return bool|int
     */
    public function processFiles(): bool|int
    {
        $q = $this->modx->newQuery(TicketFile::class);
        $q->where(['class' => 'TicketComment']);
        $q->andCondition(['parent' => 0, 'createdby' => $this->modx->user->id], null, 1);
        $q->sortby('createdon', 'ASC');
        $collection = $this->modx->getIterator(TicketFile::class, $q);

        $replace = [];
        $count = 0;
        /** @var TicketFile $item */
        foreach ($collection as $item) {
            if ($item->get('deleted')) {
                $replace[$item->get('url')] = '';
                $item->remove();
            } else {
                $old_url = $item->get('url');
                $item->set('parent', $this->object->get('id'));
                $item->save();
                $replace[$old_url] = [
                    'url' => $item->get('url'),
                    'thumb' => $item->get('thumb'),
                    'thumbs' => $item->get('thumbs'),
                ];
                $count++;
            }
        }

        // Update ticket links
        if (!empty($replace)) {
            $array = [
                'raw' => $this->object->get('raw'),
                'text' => $this->object->get('text'),
            ];
            $update = false;
            foreach ($array as $field => $text) {
                $pcre = '#<a.*?>.*?</a>|<img.*?>#s';
                preg_match_all($pcre, $text, $matches);
                $src = $dst = [];
                foreach ($matches[0] as $tag) {
                    foreach ($replace as $from => $to) {
                        if (strpos($tag, $from) !== false) {
                            if (is_array($to)) {
                                $src[] = $from;
                                $dst[] = $to['url'];
                                if (empty($to['thumbs'])) {
                                    $to['thumbs'] = [$to['thumb']];
                                }
                                foreach ($to['thumbs'] as $key => $thumb) {
                                    $src[] = str_replace('/' . $this->object->id . '/', '/0/', $thumb);
                                    $dst[] = str_replace('/0/', '/' . $this->object->id . '/', $thumb);
                                }
                            } else {
                                $src[] = $tag;
                                $dst[] = '';
                            }
                            break;
                        }
                    }
                }
                if (!empty($src)) {
                    $text = str_replace($src, $dst, $text);
                    if ($text != $this->object->$field) {
                        $this->object->set($field, $text);
                        $update = true;
                    }
                }
            }
            if ($update) {
                $this->object->save();
            }
        }

        return $count;
    }
} 