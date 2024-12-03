<?php

namespace Tickets2\Model;

use MODX\Revolution\modResource;
use MODX\Revolution\modX;
use PDO;
use xPDO\Om\xPDOObject;
use xPDO\xPDO;

// /** @noinspection PhpIncludeInspection */
// require_once MODX_CORE_PATH . 'components/tickets2/src/Processors/Mgr/Ticket/Create.php';
// /** @noinspection PhpIncludeInspection */
// require_once MODX_CORE_PATH . 'components/tickets2/src/Processors/Mgr/Ticket/Update.php';

class Ticket extends modResource
{
    // public bool $showInContextMenu = false;
    // public bool $allowChildrenResources = false;
    private int $_oldAuthor = 0;

    // /**
    //  * @param xPDO $xpdo
    //  * @param string $className
    //  * @param null $criteria
    //  * @param bool $cacheFlag
    //  *
    //  * @return modResource|null|object
    //  */
    // public static function load(xPDO & $xpdo, $className, $criteria = null, $cacheFlag = true)
    // {
    //     if (!is_object($criteria)) {
    //         $criteria = $xpdo->getCriteria($className, $criteria, $cacheFlag);
    //     }
    //     /** @noinspection PhpParamsInspection */
    //     $xpdo->addDerivativeCriteria($className, $criteria);

    //     return parent::load($xpdo, $className, $criteria, $cacheFlag);
    // }

    // /**
    //  * @param xPDO $xpdo
    //  * @param string $className
    //  * @param null $criteria
    //  * @param bool $cacheFlag
    //  *
    //  * @return array
    //  */
    // public static function loadCollection(xPDO & $xpdo, $className, $criteria = null, $cacheFlag = true): array
    // {
    //     if (!is_object($criteria)) {
    //         $criteria = $xpdo->getCriteria($className, $criteria, $cacheFlag);
    //     }
    //     /** @noinspection PhpParamsInspection */
    //     $xpdo->addDerivativeCriteria($className, $criteria);

    //     return parent::loadCollection($xpdo, $className, $criteria, $cacheFlag);
    // }

    /**
     * @param xPDO $modx
     *
     * @return string
     */
    public static function getControllerPath(xPDO &$modx): string
    {
        return $modx->getOption('tickets2.core_path', null,
            $modx->getOption('core_path') . 'components/tickets2/') . 'controllers/ticket/';
    }

    /**
     * @return array
     */
    public function getContextMenuText(): array
    {
        $this->xpdo->lexicon->load('tickets2:default');

        return [
            'text_create' => $this->xpdo->lexicon('tickets2'),
            'text_create_here' => $this->xpdo->lexicon('ticket_create_here'),
        ];
    }

    /**
     * @return string
     */
    public function getResourceTypeName(): string
    {
        $this->xpdo->lexicon->load('tickets2:default');

        return $this->xpdo->lexicon('ticket');
    }

    /**
     * @param array|string $k
     * @param null $format
     * @param null $formatTemplate
     *
     * @return int|mixed|string|array
     */
    public function get($k, $format = null, $formatTemplate = null)
    {
        $fields = ['comments', 'views', 'stars', 'rating', 'date_ago'];

        if (is_array($k)) {
            $values = [];
            foreach ($k as $v) {
                $values[$v] = $this->get($v, $format, $formatTemplate);
            }

            return $values;
        }
        
        switch ($k) {
            case 'comments':
            case 'views':
            case 'stars':
            case 'rating':
                $values = $this->_getVirtualFields();
                $value = $values[$k];
                break;
            case 'date_ago':
                $value = $this->getDateAgo();
                break;
            default:
                $value = parent::get($k, $format, $formatTemplate);
        }

        if (isset($this->_fieldMeta[$k]) && $this->_fieldMeta[$k]['phptype'] == 'string') {
            $properties = $this->getProperties();
            if (!$properties['process_tags'] && !in_array($k, $fields)) {
                $value = str_replace(
                    ['[', ']', '`', '{', '}'],
                    ['&#91;', '&#93;', '&#96;', '&#123;', '&#125;'],
                    $value
                );
            }
        }
        
        return $value;
    }

    /**
     * @param string $keyPrefix
     * @param bool $rawValues
     * @param bool $excludeLazy
     * @param bool $includeRelated
     *
     * @return array
     */
    public function toArray($keyPrefix = '', $rawValues = false, $excludeLazy = false, $includeRelated = false): array
    {
        $fields = $this->_getVirtualFields();
        if (!empty($keyPrefix)) {
            foreach ($fields as $k => $v) {
                $fields[$keyPrefix . $k] = $v;
                unset($fields[$k]);
            }
        }

        return array_merge(
            parent::toArray($keyPrefix, $rawValues, $excludeLazy, $includeRelated),
            $fields
        );
    }

    /**
     * @return string
     */
    public function process(): string
    {
        if ($this->privateweb && !$this->xpdo->hasPermission('ticket_view_private') && $id = $this->getOption('tickets2.private_ticket_page')) {
            $this->xpdo->sendForward($id);
            die;
        }

        return parent::process();
    }

    /**
     * @param array $options
     *
     * @return string
     */
    public function getContent(array $options = [])
    {
        $content = parent::get('content');
        $properties = $this->getProperties();

        if (!$properties['disable_jevix']) {
            $content = $this->Jevix($content, false);
        }
        if (!$properties['process_tags']) {
            $content = str_replace(
                ['[', ']', '`', '{', '}'],
                ['&#91;', '&#93;', '&#96;', '&#123;', '&#125;'],
                $content
            );
        }
        $content = preg_replace('/<cut(.*?)>/i', '<a name="cut"></a>', $content);

        return $content;
    }

    /**
     * Html filter and typograf
     *
     * @param string $text
     * @param bool $replaceTags
     *
     * @return string
     */
    public function Jevix(string $text, bool $replaceTags = true): string
    {
        /** @var \Tickets2 $Tickets2 */
        if ($Tickets2 = $this->xpdo->getService('Tickets2')) {
            return $Tickets2->Jevix($text, 'Ticket', $replaceTags);
        }

        return 'Error on loading class "Tickets2".';
    }

    /**
     * Generate intro text from content buy cutting text before tag <cut/>
     *
     * @param string|null $content
     * @param bool $jevix
     *
     * @return string
     */
    public function getIntroText(?string $content = null, bool $jevix = true): string
    {
        if (empty($content)) {
            $content = parent::get('content');
        }
        $content = preg_replace('/<cut(.*?)>/i', '<cut/>', $content);

        if (!preg_match('/<cut\/>/', $content)) {
            $introtext = $content;
        } else {
            $tmp = explode('<cut/>', $content);
            $introtext = reset($tmp);
            if ($jevix) {
                $introtext = $this->Jevix($introtext);
            }
        }

        return $introtext;
    }

    /**
     * @param string $alias
     * @param null $criteria
     * @param bool $cacheFlag
     *
     * @return array
     */
    public function & getMany($alias, $criteria = null, $cacheFlag = true): array
    {
        if ($alias == 'Files' || $alias == 'Votes') {
            $criteria = ['class' => $this->class_key];
        }

        return parent::getMany($alias, $criteria, $cacheFlag);
    }

    /**
     * @param mixed $obj
     * @param string $alias
     *
     * @return bool
     */
    public function addMany(& $obj, $alias = ''): bool
    {
        $added = false;
        if (is_array($obj)) {
            foreach ($obj as $o) {
                /** @var xPDOObject $o */
                if (is_object($o)) {
                    $o->set('class', $this->class_key);
                    $added = parent::addMany($obj, $alias);
                }
            }

            return $added;
        }
        
        return parent::addMany($obj, $alias);
    }

    /**
     * @return array
     */
    protected function _getVirtualFields(): array
    {
        /** @var TicketTotal $total */
        if (!$total = $this->getOne('Total')) {
            $total = $this->xpdo->newObject(TicketTotal::class);
            $total->fromArray([
                'id' => $this->id,
                'class' => 'Ticket',
            ], '', true, true);
            $total->fetchValues();
            $total->save();
        }

        return $total->get([
            'comments',
            'views',
            'stars',
            'rating',
            'rating_plus',
            'rating_minus',
        ]);
    }

    /**
     * @return int
     */
    public function getViewsCount(): int
    {
        return $this->xpdo->getCount(TicketView::class, ['parent' => $this->id]);
    }

    /**
     * @return int
     */
    public function getCommentsCount(): int
    {
        $q = $this->xpdo->newQuery(TicketThread::class, ['name' => 'resource-' . $this->id]);
        $q->leftJoin(TicketComment::class, 'TicketComment',
            "`TicketThread`.`id` = `TicketComment`.`thread` AND `TicketComment`.`published` = 1");
        $q->select('COUNT(`TicketComment`.`id`) as `comments`');

        $count = 0;
        $tstart = microtime(true);
        if ($q->prepare() && $q->stmt->execute()) {
            $this->xpdo->startTime += microtime(true) - $tstart;
            $this->xpdo->executedQueries++;
            $count = (int)$q->stmt->fetchColumn();
        }

        return $count;
    }

    /**
     * @return int
     */
    public function getStarsCount(): int
    {
        return $this->xpdo->getCount(TicketStar::class, ['id' => $this->id, 'class' => 'Ticket']);
    }

    /**
     * @return string
     */
    public function getDateAgo(): string
    {
        $createdon = parent::get('createdon');
        /** @var \Tickets2 $Tickets2 */
        if ($Tickets2 = $this->xpdo->getService('Tickets2')) {
            $createdon = $Tickets2->dateFormat($createdon);
        }

        return $createdon;
    }

    /**
     * @return int
     */
    public function getVote(): int
    {
        $q = $this->xpdo->newQuery(TicketVote::class);
        $q->where([
            'id' => $this->id,
            'createdby' => $this->xpdo->user->id,
            'class' => 'Ticket',
        ]);
        $q->select('`value`');

        $vote = 0;
        $tstart = microtime(true);
        if ($q->prepare() && $q->stmt->execute()) {
            $this->xpdo->startTime += microtime(true) - $tstart;
            $this->xpdo->executedQueries++;
            $vote = (int)$q->stmt->fetchColumn();
        }

        return $vote;
    }

    /**
     * @return array
     */
    public function getRating(): array
    {
        $rating = ['rating' => 0, 'rating_plus' => 0, 'rating_minus' => 0];

        $q = $this->xpdo->newQuery(TicketVote::class);
        $q->innerJoin(Ticket::class, 'Ticket', 'Ticket.id = TicketVote.id');
        $q->where([
            'class' => 'Ticket',
            'id' => $this->id,
            'Ticket.deleted' => 0,
            'Ticket.published' => 1,
        ]);
        $q->select('value');
        $tstart = microtime(true);
        if ($q->prepare() && $q->stmt->execute()) {
            $this->xpdo->startTime += microtime(true) - $tstart;
            $this->xpdo->executedQueries++;
            $rows = $q->stmt->fetchAll(PDO::FETCH_COLUMN);
            foreach ($rows as $value) {
                $rating['rating'] += $value;
                if ($value > 0) {
                    $rating['rating_plus'] += $value;
                } elseif ($value < 0) {
                    $rating['rating_minus'] += abs($value);
                }
            }
        }

        return $rating;
    }

    /**
     * @param string $alias
     *
     * @return string|bool
     */
    public function setUri(string $alias = '')
    {
        if (empty($alias)) {
            $alias = $this->get('alias');
        }
        /** @var Tickets2Section $section */
        if ($section = $this->xpdo->getObject(Tickets2Section::class, $this->get('parent'))) {
            $properties = $section->getProperties();
        } else {
            return false;
        }
        $template = $properties['uri'];
        if (empty($template) || strpos($template, '%') === false) {
            return false;
        }

        if ($this->get('pub_date')) {
            $date = $this->get('pub_date');
        } else {
            $date = $this->get('published')
                ? $this->get('publishedon')
                : $this->get('createdon');
        }
        $date = strtotime($date);

        $pls = [
            'pl' => ['%y', '%m', '%d', '%id', '%alias', '%ext'],
            'vl' => [
                date('y', $date),
                date('m', $date),
                date('d', $date),
                $this->get('id')
                    ? $this->get('id')
                    : '%id',
                $alias,
            ],
        ];

        /** @var \MODX\Revolution\modContentType $contentType */
        if ($contentType = $this->xpdo->getObject('MODX\Revolution\modContentType', $this->get('content_type'))) {
            $pls['vl'][] = $contentType->getExtension();
        } else {
            $pls['vl'][] = '';
        }

        $uri = rtrim($section->getAliasPath($section->get('alias')), '/') . '/' . str_replace($pls['pl'], $pls['vl'],
                $template);
        $this->set('uri', $uri);
        $this->set('uri_override', true);

        return $uri;
    }

    /**
     * @param string $namespace
     *
     * @return array
     */
    public function getProperties($namespace = 'tickets2'): array
    {
        $properties = parent::getProperties($namespace);

        // Convert old settings
        if (empty($this->reloadOnly)) {
            $flag = false;
            $tmp = ['disable_jevix', 'process_tags', 'rating'];
            if ($old = parent::get('properties')) {
                foreach ($tmp as $v) {
                    if (array_key_exists($v, $old)) {
                        $properties[$v] = $old[$v];
                        $flag = true;
                        unset($old[$v]);
                    }
                }
                if ($flag) {
                    $old['tickets2'] = $properties;
                    $this->set('properties', $old);
                    $this->save();
                }
            }
        }

        if (empty($properties)) {
            /** @var Tickets2Section $parent */
            if (!$parent = $this->getOne('Parent')) {
                $parent = $this->xpdo->newObject(Tickets2Section::class);
            }
            $default_properties = $parent->getProperties($namespace);
            if (!empty($default_properties)) {
                foreach ($default_properties as $key => $value) {
                    if (!isset($properties[$key])) {
                        $properties[$key] = $value;
                    } elseif ($properties[$key] === 'true') {
                        $properties[$key] = true;
                    } elseif ($properties[$key] === 'false') {
                        $properties[$key] = false;
                    } elseif (is_numeric($value) && $key == 'disable_jevix' || $key == 'process_tags') {
                        $properties[$key] = (bool)((int)$value);
                    }
                }
            }
        }

        return $properties;
    }

    /**
     * @param string $k
     * @param null $v
     * @param string $vType
     *
     * @return bool
     */
    public function set($k, $v = null, $vType = ''): bool
    {
        if (is_string($k) && $k == 'createdby' && empty($this->_oldAuthor)) {
            $this->_oldAuthor = parent::get('createdby');
        }

        return parent::set($k, $v, $vType);
    }

    /**
     * @param null $cacheFlag
     *
     * @return bool
     */
    public function save($cacheFlag = null): bool
    {
        $isNew = $this->isNew();
        $action = $isNew || $this->isDirty('deleted') || $this->isDirty('published');
        $enabled = $this->get('published') && !$this->get('deleted');
        $new_parent = $this->isDirty('parent');
        $new_author = $this->isDirty('createdby');
        if ($new_parent || $this->isDirty('alias') || $this->isDirty('published') || ($this->get('uri_override') && !$this->get('uri'))) {
            $save = parent::save();
            $this->setUri($this->get('alias'));
        }
        $save = parent::save();

        /** @var TicketAuthor $profile */
        if ($new_author && $profile = $this->xpdo->getObject(TicketAuthor::class, $this->_oldAuthor)) {
            $profile->removeAction('ticket', $this->id, $this->get('createdby'));
        }
        if ($profile = $this->xpdo->getObject(TicketAuthor::class, $this->get('createdby'))) {
            if (($action || $new_author) && $enabled) {
                $profile->addAction('ticket', $this->id, $this->id, $this->get('createdby'));
            } elseif (!$enabled) {
                $profile->removeAction('ticket', $this->id, $this->get('createdby'));
            }
        }
        if ($new_parent && !$isNew) {
            $this->updateAuthorsActions();
        }

        return $save;
    }

    /**
     * @param array $ancestors
     *
     * @return bool
     */
    public function remove(array $ancestors = []): bool
    {
        $collection = $this->xpdo->getIterator(TicketThread::class, ['name' => 'resource-' . $this->id]);
        /** @var TicketThread $item */
        foreach ($collection as $item) {
            $item->remove();
        }

        /** @var TicketAuthor $profile */
        if ($profile = $this->xpdo->getObject(TicketAuthor::class, $this->get('createdby'))) {
            $profile->removeAction('ticket', $this->id, $this->get('createdby'));
        }

        /** @var TicketTotal $total */
        if ($total = $this->xpdo->getObject(TicketTotal::class, ['id' => $this->id, 'class' => 'Ticket'])) {
            $total->remove();
        }
        if ($total = $this->xpdo->getObject(TicketTotal::class, ['id' => $this->parent, 'class' => 'Tickets2Section'])) {
            $total->set('children', $total->get('children') - 1);
            $total->save();
        }

        return parent::remove($ancestors);
    }

    /**
     * Update ratings for authors actions in section
     */
    public function updateAuthorsActions(): void
    {
        if (!$section = $this->getOne('Section')) {
            $section = $this->xpdo->newObject(Tickets2Section::class);
        }

        $ratings = $section->getProperties('ratings');
        $table = $this->xpdo->getTableName(TicketAuthorAction::class);
        foreach ($ratings as $action => $rating) {
            $sql = "
                UPDATE {$table} SET `rating` = `multiplier` * {$rating}, `section` = {$section->id}
                WHERE `ticket` = {$this->id} AND `action` = '{$action}';
            ";
            $this->xpdo->exec($sql);
        }

        $c = $this->xpdo->newQuery(TicketAuthorAction::class, ['ticket' => $this->id]);
        $c->select('DISTINCT(owner)');
        $owners = [];
        if ($c->prepare() && $c->stmt->execute()) {
            $owners = $c->stmt->fetchAll(PDO::FETCH_COLUMN);
        }

        $authors = $this->xpdo->getIterator(TicketAuthor::class, ['id:IN' => $owners]);
        /** @var TicketAuthor $author */
        foreach ($authors as $author) {
            $author->updateTotals();
        }
    }

    /**
     * @return array
     */
    public function getNeighborhood(): array
    {
        $arr = [];
        $q = $this->xpdo->newQuery(Ticket::class, ['parent' => $this->parent, 'class_key' => Ticket::class]);
        $q->sortby('id', 'ASC');
        $q->select('id');
        if ($q->prepare() && $q->stmt->execute()) {
            $ids = $q->stmt->fetchAll(PDO::FETCH_COLUMN);
            $current = array_search($this->get('id'), $ids);
            $right = $left = [];
            foreach ($ids as $k => $v) {
                if ($k > $current) {
                    $right[] = $v;
                } elseif ($k < $current) {
                    $left[] = $v;
                }
            }
            $arr = [
                'left' => array_reverse($left),
                'right' => $right,
            ];
        }
        return $arr;
    }

    /**
     * @param string $context
     */
    public function clearCache($context = '')
    {
        if (!$context) {
            $context = $this->get('context_key');
        }
        parent::clearCache($context);
    }
}