<?php

namespace Tickets2\Processors\Mgr\Ticket;

use MODX\Revolution\Processors\Resource\Update as ResourceUpdate;
use MODX\Revolution\modResource;
use Tickets2\Model\Ticket;
use Tickets2\Model\TicketAuthor;
use Tickets2\Model\TicketFile;

class Update extends ResourceUpdate
{
    public $classKey = Ticket::class;
    public $permission = 'ticket_save';
    public $languageTopics = ['resource', 'tickets2:default'];
    private ?bool $_published = null;
    private bool $_sendEmails = false;

    /**
     * @return bool|null|string
     */
    public function initialize()
    {
        $primaryKey = $this->getProperty($this->primaryKeyField, false);
        if (empty($primaryKey)) {
            return $this->modx->lexicon($this->objectType . '_err_ns');
        }

        if (!$this->modx->getCount($this->classKey, [
                'id' => $primaryKey,
                'class_key' => $this->classKey,
            ]) && $res = $this->modx->getObject(modResource::class, ['id' => $primaryKey])
        ) {
            $res->set('class_key', $this->classKey);
            $res->save();
        }

        return parent::initialize();
    }

    /**
     * @return bool|null|string
     */
    public function beforeSet()
    {
        $this->_published = $this->getProperty('published', null);
        if ($this->_published && !$this->modx->hasPermission('ticket_publish')) {
            return $this->modx->lexicon('ticket_err_publish');
        }

        if ($this->object->createdby != $this->modx->user->id && !$this->modx->hasPermission('edit_document')) {
            return $this->modx->lexicon('ticket_err_wrong_user');
        }

        // Required fields
        $requiredFields = $this->getProperty('requiredFields', ['parent', 'pagetitle', 'content']);
        foreach ($requiredFields as $field) {
            $value = trim($this->getProperty($field));
            if (empty($value) && $this->modx->context->key != 'mgr') {
                $this->addFieldError($field, $this->modx->lexicon('field_required'));
            } else {
                $this->setProperty($field, $value);
            }
        }
        $content = $this->getProperty('content');
        $length = mb_strlen(strip_tags($content), $this->modx->getOption('modx_charset', null, 'UTF-8', true));
        $max = $this->modx->getOption('tickets2.ticket_max_cut', null, 1000, true);
        if (empty($content) && $this->modx->context->key != 'mgr') {
            return $this->modx->lexicon('ticket_err_empty');
        } elseif ($this->modx->context->key != 'mgr' && !preg_match('#<cut\b.*?>#', $content) && $length > $max) {
            return $this->modx->lexicon('ticket_err_cut', ['length' => $length, 'max_cut' => $max]);
        }

        $set = parent::beforeSet();
        if ($this->hasErrors()) {
            return $this->modx->lexicon('ticket_err_form');
        }
        $this->setFieldDefault();
        $this->unsetProperty('action');

        return $set;
    }

    /**
     * @return bool
     */
    public function setFieldDefault(): bool
    {
        // Ticket properties
        $properties = $this->modx->context->key == 'mgr'
            ? $this->getProperty('properties')
            : $this->object->getProperties();
        $this->unsetProperty('properties');

        // Define introtext
        $introtext = $this->getProperty('introtext');
        if (empty($introtext)) {
            $introtext = $this->object->getIntroText($this->getProperty('content'), false);
        }
        if (empty($properties['disable_jevix'])) {
            $introtext = $this->object->Jevix($introtext);
        }

        // Set properties
        if ($this->modx->context->key != 'mgr') {
            $this->unsetProperty('properties');
            $this->unsetProperty('published');
            $tmp = $this->parentResource->getProperties();
            $template = $tmp['template'];
            if (empty($template)) {
                $template = $this->modx->context->getOption('tickets2.default_template',
                    $this->modx->context->getOption('default_template'));
            }
            $this->setProperty('template', $template);
        }
        $this->setProperties([
            'class_key' => Ticket::class,
            'syncsite' => 0,
            'introtext' => $introtext,
        ]);
        if ($this->modx->context->key != 'mgr' && !is_null($this->_published)) {
            $this->setProperty('published', $this->_published);
        }
        if ($this->modx->context->key == 'mgr') {
            $properties['disable_jevix'] = !empty($properties['disable_jevix']);
            $properties['process_tags'] = !empty($properties['process_tags']);
            $this->object->setProperties($properties, 'tickets2', true);
        }

        return true;
    }

    /**
     * @return bool
     */
    public function beforeSave(): bool
    {
        $time = time();
        if ($this->_published) {
            $properties = $this->object->getProperties();
            // First publication
            if (isset($properties['was_published']) && empty($properties['was_published'])) {
                $this->object->set('createdon', $time, 'integer');
                $this->object->set('publishedon', $time, 'integer');
                unset($properties['was_published']);
                $this->object->set('properties', $properties);
                $this->_sendEmails = true;

                /** @var Tickets2Section $section */
                if ($section = $this->object->getOne('Section')) {
                    $ratings = $section->getProperties('ratings');
                    if (isset($ratings['min_ticket_create']) && $ratings['min_ticket_create'] !== '') {
                        if ($profile = $this->modx->getObject(TicketAuthor::class, $this->object->get('createdby'))) {
                            $min = (float)$ratings['min_ticket_create'];
                            $rating = $profile->get('rating');
                            if ($rating < $min) {
                                return $this->modx->lexicon('ticket_err_rating_ticket', ['rating' => $min]);
                            }
                        }
                    }
                }
            }
        }
        $this->object->set('editedby', $this->modx->user->get('id'));
        $this->object->set('editedon', $time, 'integer');

        return !$this->hasErrors();
    }

    /**
     * @return bool
     */
    public function afterSave(): bool
    {
        $parent = parent::afterSave();
        if ($this->_sendEmails && $this->modx->context->key == 'mgr') {
            $this->sendTicketMails();
        }

        return $parent;
    }

    /**
     * Call method for notify users about new ticket in section
     */
    protected function sendTicketMails(): void
    {
        /** @var \Tickets2 $Tickets2 */
        if ($Tickets2 = $this->modx->getService('Tickets2')) {
            $Tickets2->config['tplTicketEmailBcc'] = 'tpl.Tickets2.ticket.email.bcc';
            $Tickets2->config['tplTicketEmailSubscription'] = 'tpl.Tickets2.ticket.email.subscription';
            $Tickets2->sendTicketMails($this->object->toArray());
        }
    }

    /**
     * @return mixed|string
     */
    public function checkFriendlyAlias(): mixed
    {
        $alias = parent::checkFriendlyAlias();

        if ($this->modx->context->key != 'mgr') {
            foreach ($this->modx->error->errors as $k => $v) {
                if ($v['id'] == 'alias' || $v['id'] == 'uri') {
                    unset($this->modx->error->errors[$k]);
                }
            }
        }

        return $alias;
    }

    /**
     * @return int|mixed|null|string
     */
    public function handleParent(): mixed
    {
        if ($this->modx->context->key == 'manager') {
            return parent::handleParent();
        }

        $parent = null;
        $parentId = (int)$this->getProperty('parent');
        if ($parentId > 0) {
            $sections = $this->getProperty('sections');
            if (!empty($sections) && !in_array($parentId, $sections)) {
                return $this->modx->lexicon('ticket_err_wrong_parent');
            }
            $this->parentResource = $this->modx->getObject('Tickets2Section', $parentId);
            if ($this->parentResource) {
                if ($this->parentResource->get('class_key') != 'Tickets2Section') {
                    $this->addFieldError('parent', $this->modx->lexicon('ticket_err_wrong_parent'));
                } elseif (!$this->parentResource->checkPolicy(['section_add_children' => true])) {
                    $this->addFieldError('parent', $this->modx->lexicon('ticket_err_wrong_parent'));
                }
            } else {
                $this->addFieldError('parent', $this->modx->lexicon('resource_err_nfs', ['id' => $parentId]));
            }
        }

        return $parent;
    }

    /**
     * @return bool
     */
    public function checkPublishingPermissions(): bool
    {
        if ($this->modx->context->key == 'mgr') {
            return parent::checkPublishingPermissions();
        }

        return true;
    }

    /**
     * Clear ticket cache
     */
    public function clearCache(): void
    {
        $this->object->clearCache();
        /** @var Tickets2Section $section */
        if ($section = $this->object->getOne('Section')) {
            $section->clearCache();
        }
    }

    /**
     * @return array|mixed
     */
    public function saveTemplateVariables(): array
    {
        if ($this->modx->context->key != 'mgr') {
            $values = [];
            $tvs = $this->object->getMany('TemplateVars');

            /** @var modTemplateVarResource $tv */
            foreach ($tvs as $tv) {
                $values['tv' . $tv->get('id')] = $this->getProperty($tv->get('name'), $tv->get('value'));
            }

            if (!empty($values)) {
                $this->setProperties($values);
                $this->setProperty('tvs', 1);
            }
        }

        return parent::saveTemplateVariables();
    }

    /**
     * @return array
     */
    public function cleanup(): array
    {
        $this->processFiles();

        return parent::cleanup();
    }

    /**
     * Add uploaded files to ticket
     *
     * @return bool|int
     */
    public function processFiles()
    {
        $q = $this->modx->newQuery(TicketFile::class);
        $q->where(['class' => 'Ticket']);
        $q->andCondition(['parent' => $this->object->id, 'createdby' => $this->modx->user->id], null, 1);
        //$q->orCondition(['parent' => $this->object->id], null, 1);
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
                $item->set('parent', $this->object->id);
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
                'introtext' => $this->object->get('introtext'),
                'content' => $this->object->get('content'),
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
                                    if (strpos($thumb, '/' . $key . '/') === false) {
                                        // Old thumbnails
                                        $src[] = preg_replace('#\.[a-z]+$#i', '_thumb$0', $from);
                                        $dst[] = preg_replace('#\.[a-z]+$#i', '_thumb$0', $thumb);
                                    } else {
                                        // New thumbnails
                                        $src[] = str_replace('/' . $this->object->id . '/', '/0/', $thumb);
                                        $dst[] = str_replace('/0/', '/' . $this->object->id . '/', $thumb);
                                    }
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