<?php

namespace Tickets2\Model;

use MODX\Revolution\modResource;
use MODX\Revolution\modX;
use xPDO\Om\xPDOObject;
use xPDO\xPDO;
use PDO;
// /** @noinspection PhpIncludeInspection */
// require_once MODX_CORE_PATH . 'components/tickets2/src/Processors/Mgr/Section/Create.php';
// /** @noinspection PhpIncludeInspection */
// require_once MODX_CORE_PATH . 'components/tickets2/src/Processors/Mgr/Section/Update.php';

/**
 * Class Tickets2Section
 * @package Tickets2\Model
 * @property bool $showInContextMenu
 * @property bool $allowChildrenResources
 */
class Tickets2Section extends modResource
{
    // public bool $showInContextMenu = true;
    // public bool $allowChildrenResources = false;
    private $_oldUri = '';
    private $_oldRatings = '';

    /**
     * @param xPDO $xpdo
     */
    public function __construct(xPDO & $xpdo)
    {
        parent::__construct($xpdo);
        $this->set('class_key', self::class);
    }

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
            $modx->getOption('core_path') . 'components/tickets2/') . 'controllers/section/';
    }

    /**
     * @return array
     */
    public function getContextMenuText(): array
    {
        $this->xpdo->lexicon->load('tickets2:default');

        return [
            'text_create' => $this->xpdo->lexicon('tickets2_section'),
            'text_create_here' => $this->xpdo->lexicon('tickets2_section_create_here'),
        ];
    }

    /**
     * @return string
     */
    public function getResourceTypeName(): string
    {
        $this->xpdo->lexicon->load('tickets2:default');
        return $this->xpdo->lexicon('tickets2_section');
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
        if (is_string($k) && ($k == 'alias' || $k == 'uri')) {
            $this->_oldUri = parent::get('uri');
        } elseif (is_string($k) && $k == 'properties' && empty($this->_oldRatings)) {
            if ($properties = parent::get('properties')) {
                if (!empty($properties['ratings'])) {
                    unset(
                        $properties['ratings']['min_ticket_create'],
                        $properties['ratings']['min_comment_create'],
                        $properties['ratings']['days_ticket_vote'],
                        $properties['ratings']['days_comment_vote']
                    );
                    $this->_oldRatings = implode(array_values($properties['ratings']));
                }
            }
        }

        return parent::set($k, $v, $vType);
    }

    /**
     * @param array|string $k
     * @param null $format
     * @param null $formatTemplate
     *
     * @return int|mixed
     */
    public function get($k, $format = null, $formatTemplate = null)
    {
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
            case 'tickets2':
                $values = $this->_getVirtualFields();
                $value = $values[$k];
                break;
            default:
                $value = parent::get($k, $format, $formatTemplate);
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
     * @param array $options
     *
     * @return string
     */
    public function getContent(array $options = [])
    {
        return parent::getContent($options);
    }

    /**
     * Shorthand for get virtual Ticket fields
     *
     * @return array
     */
    protected function _getVirtualFields(): array
    {
        /** @var TicketTotal $total */
        if (!$total = $this->getOne('Total')) {
            $total = $this->xpdo->newObject(TicketTotal::class);
            $total->fromArray([
                'id' => $this->id,
                'class' => self::class,
            ], '', true, true);
            $total->fetchValues();
            $total->save();
        }

        return $total->get([
            'comments',
            'views',
            'tickets2',
            'stars',
            'rating',
            'rating_plus',
            'rating_minus',
        ]);
    }

    /**
     * Get rating
     *
     * @return array
     */
    public function getRating(): array
    {
        $rating = ['rating' => 0, 'rating_plus' => 0, 'rating_minus' => 0];

        $q = $this->xpdo->newQuery(TicketVote::class);
        $q->innerJoin(Ticket::class, 'Ticket', 'Ticket.id = TicketVote.id');
        $q->innerJoin(self::class, 'Section', 'Section.id = Ticket.parent');
        $q->where([
            'class' => Ticket::class,
            'Section.id' => $this->id,
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
     * Returns the number of views of Tickets2 in this Section
     *
     * @return int
     */
    public function getViewsCount(): int
    {
        $q = $this->xpdo->newQuery(Ticket::class, [
            'parent' => $this->id,
            'published' => 1,
            'deleted' => 0
        ]);
        $q->leftJoin(TicketView::class, 'Views');
        $q->select('COUNT(Views.parent) as views');

        $count = 0;
        if ($q->prepare() && $q->stmt->execute()) {
            $count = (int)$q->stmt->fetch(PDO::FETCH_COLUMN);
        }

        return $count;
    }

    /**
     * Returns the number of stars for Tickets2 in this Section
     *
     * @return int
     */
    public function getStarsCount(): int
    {
        $q = $this->xpdo->newQuery(Ticket::class, [
            'parent' => $this->id,
            'published' => 1,
            'deleted' => 0
        ]);
        $q->leftJoin(TicketStar::class, 'Stars');
        $q->select('COUNT(Stars.owner) as views');

        $count = 0;
        if ($q->prepare() && $q->stmt->execute()) {
            $count = (int)$q->stmt->fetch(PDO::FETCH_COLUMN);
        }

        return $count;
    }

    /**
     * Returns count of comments to Tickets2 in this Section
     *
     * @return int
     */
    public function getCommentsCount(): int
    {
        $q = $this->xpdo->newQuery(Ticket::class, [
            'parent' => $this->id,
            'published' => 1,
            'deleted' => 0
        ]);
        $q->leftJoin(TicketThread::class, 'TicketThread', "`TicketThread`.`resource` = `Ticket`.`id`");
        $q->leftJoin(TicketComment::class, 'TicketComment', "`TicketThread`.`id` = `TicketComment`.`thread`");
        $q->select('COUNT(`TicketComment`.`id`) as `comments`');

        $count = 0;
        if ($q->prepare() && $q->stmt->execute()) {
            $count = (int)$q->stmt->fetch(PDO::FETCH_COLUMN);
        }

        return $count;
    }

    /**
     * Returns count of tickets2 in this Section
     *
     * @return int
     */
    public function getTickets2Count(): int
    {
        return $this->xpdo->getCount(Ticket::class, [
            'parent' => $this->id,
            'published' => 1,
            'deleted' => 0
        ]);
    }

    /**
     * @param array $node
     *
     * @return array
     */
    public function prepareTreeNode(array $node = []): array
    {
        $this->xpdo->lexicon->load('tickets2:default');
        $menu = [];

        $idNote = $this->xpdo->hasPermission('tree_show_resource_ids')
            ? ' <span dir="ltr">(' . $this->id . ')</span>'
            : '';
        $menu[] = [
            'text' => '<b>' . $this->get('pagetitle') . '</b>' . $idNote,
            'handler' => 'Ext.emptyFn',
        ];
        $menu[] = '-';
        $menu[] = [
            'text' => $this->xpdo->lexicon('tickets2_section_management'),
            'handler' => 'this.editResource',
        ];
        $menu[] = [
            'text' => $this->xpdo->lexicon('ticket_create_here'),
            'handler' => 'function(itm,e) { var tree = Ext.getCmp("modx-resource-tree"); itm.classKey = "Ticket"; tree.createResourceHere(itm,e); }',
        ];

        $menu[] = '-';
        $menu[] = [
            'text' => $this->xpdo->lexicon('tickets2_section_duplicate'),
            'handler' => 'function(itm,e) {itm.classKey = "Tickets2Section"; this.duplicateResource(itm,e); }',
        ];

        if ($this->get('published')) {
            $menu[] = [
                'text' => $this->xpdo->lexicon('tickets2_section_unpublish'),
                'handler' => 'this.unpublishDocument',
            ];
        } else {
            $menu[] = [
                'text' => $this->xpdo->lexicon('tickets2_section_publish'),
                'handler' => 'this.publishDocument',
            ];
        }
        if ($this->get('deleted')) {
            $menu[] = [
                'text' => $this->xpdo->lexicon('tickets2_section_undelete'),
                'handler' => 'this.undeleteDocument',
            ];
        } else {
            $menu[] = [
                'text' => $this->xpdo->lexicon('tickets2_section_delete'),
                'handler' => 'this.deleteDocument',
            ];
        }
        $menu[] = '-';
        $menu[] = [
            'text' => $this->xpdo->lexicon('tickets2_section_view'),
            'handler' => 'this.preview',
        ];

        $node['menu'] = ['items' => $menu];
        $node['hasChildren'] = true;

        return $node;
    }

    /**
     * Get the properties for the specific namespace for the Resource
     *
     * @param string $namespace
     *
     * @return array
     */
    public function getProperties($namespace = 'tickets2'): array
    {
        $properties = parent::getProperties($namespace);
        if ($namespace == 'tickets2') {
            $default_properties = [
                'template' => $this->xpdo->context->getOption('tickets2.default_template', 0),
                'uri' => '%id-%alias%ext',
                'show_in_tree' => $this->xpdo->context->getOption('tickets2.ticket_show_in_tree_default', false),
                'hidemenu' => $this->xpdo->context->getOption('tickets2.ticket_hidemenu_force',
                    $this->xpdo->context->getOption('hidemenu_default')
                ),
                'disable_jevix' => $this->xpdo->context->getOption('tickets2.disable_jevix_default', false),
                'process_tags' => $this->xpdo->context->getOption('tickets2.process_tags_default', false),
            ];

            // Old default values
            if (array_key_exists('tickets2.ticket_id_as_alias', $this->xpdo->config)) {
                $default_properties['uri'] = $this->xpdo->context->getOption('tickets2.ticket_id_as_alias')
                    ? '%id'
                    : '%alias';
                $default_properties['uri'] .= $this->xpdo->context->getOption('tickets2.ticket_isfolder_force')
                    ? '/'
                    : '%ext';
            }

            foreach ($default_properties as $key => $value) {
                if (!isset($properties[$key])) {
                    $properties[$key] = $value;
                } elseif ($properties[$key] === 'true') {
                    $properties[$key] = true;
                } elseif ($properties[$key] === 'false') {
                    $properties[$key] = false;
                } elseif (is_numeric($value) && ($key == 'disable_jevix' || $key == 'process_tags')) {
                    $properties[$key] = (bool)((int)$value);
                }
            }
        } elseif ($namespace == 'ratings') {
            $default_properties = [
                'ticket' => $this->xpdo->context->getOption('tickets2.rating_ticket_default', 10),
                'comment' => $this->xpdo->context->getOption('tickets2.rating_comment_default', 1),
                'view' => $this->xpdo->context->getOption('tickets2.rating_view_default', 0.1),
                'vote_ticket' => $this->xpdo->context->getOption('tickets2.rating_vote_ticket_default', 1),
                'vote_comment' => $this->xpdo->context->getOption('tickets2.rating_vote_comment_default', 0.2),
                'star_ticket' => $this->xpdo->context->getOption('tickets2.rating_star_ticket_default', 3),
                'star_comment' => $this->xpdo->context->getOption('tickets2.rating_star_comment_default', 0.6),
                'min_ticket_create' => '',
                'min_comment_create' => '',
                'days_ticket_vote' => '',
                'days_comment_vote' => '',
            ];

            foreach ($default_properties as $key => $value) {
                if (!isset($properties[$key])) {
                    $properties[$key] = $value;
                }
            }
        }

        return $properties;
    }

    /**
     * @param null $cacheFlag
     *
     * @return bool
     */
    public function save($cacheFlag = null)
    {
        $this->set('isfolder', 1);
        $update_actions = false;
        if ($properties = parent::get('properties')) {
            if (!empty($properties['ratings'])) {
                unset(
                    $properties['ratings']['min_ticket_create'],
                    $properties['ratings']['min_comment_create'],
                    $properties['ratings']['days_ticket_vote'],
                    $properties['ratings']['days_comment_vote']
                );
                $ratings = implode(array_values($properties['ratings']));
                $update_actions = !empty($this->_oldRatings) && $this->_oldRatings != $ratings;
            }
        }

        $new = $this->isNew();
        $saved = parent::save($cacheFlag);
        if ($saved && !$new) {
            $this->updateChildrenURIs();
        }
        if ($saved && $update_actions) {
            $this->updateAuthorsActions();
        }

        return $saved;
    }

    /**
     * Update all children URIs if section uri was changed
     *
     * @return int
     */
    public function updateChildrenURIs(): int
    {
        $count = 0;
        if (!empty($this->_oldUri) && $this->_oldUri != $this->get('uri')) {
            $sql = "UPDATE {$this->xpdo->getTableName(Ticket::class)}
                SET `uri` = REPLACE(`uri`,'{$this->_oldUri}','{$this->get('uri')}')
                WHERE `parent` = {$this->get('id')}";
            $count = $this->xpdo->exec($sql);
        }

        return $count;
    }

    /**
     * Subscribe user to section
     * 
     * @param int $uid
     *
     * @return bool
     */
    public function Subscribe(int $uid = 0): bool
    {
        if (!$uid) {
            $uid = $this->xpdo->user->id;
        }

        $subscribers = $this->getProperties('subscribers');
        if (empty($subscribers) || !is_array($subscribers)) {
            $subscribers = [];
        }

        $found = array_search($uid, $subscribers);
        if ($found === false) {
            $subscribers[] = $uid;
        } else {
            unset($subscribers[$found]);
        }
        $this->setProperties(array_values($subscribers), 'subscribers', false);
        $this->save();

        return ($found === false);
    }

    /**
     * Check if user is subscribed to section
     * 
     * @param int $uid
     *
     * @return bool
     */
    public function isSubscribed(int $uid = 0): bool
    {
        if (!$uid) {
            $uid = $this->xpdo->user->id;
        }

        $properties = [];
        $q = $this->xpdo->newQuery(self::class, ['id' => $this->id]);
        $q->select('properties');
        $tstart = microtime(true);
        if ($q->prepare() && $q->stmt->execute()) {
            $this->xpdo->queryTime += microtime(true) - $tstart;
            $this->xpdo->executedQueries++;
            $properties = $this->xpdo->fromJSON($q->stmt->fetchColumn());
        }
        $subscribers = !empty($properties['subscribers'])
            ? $properties['subscribers']
            : [];

        return in_array($uid, $subscribers);
    }

    /**
     * Update ratings for authors actions in section
     */
    public function updateAuthorsActions(): void
    {
        $ratings = $this->getProperties('ratings');
        $table = $this->xpdo->getTableName(TicketAuthorAction::class);
        foreach ($ratings as $action => $rating) {
            $sql = "UPDATE {$table} SET `rating` = `multiplier` * {$rating} WHERE `section` = {$this->id} AND `action` = '{$action}';";
            $this->xpdo->exec($sql);
        }

        $c = $this->xpdo->newQuery(TicketAuthorAction::class, ['section' => $this->id]);
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