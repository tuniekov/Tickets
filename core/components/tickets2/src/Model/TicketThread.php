<?php

namespace Tickets2\Model;

use PDO;
use xPDO\Om\xPDOSimpleObject;

/**
 * Class TicketThread
 * @package Tickets2\Model
 * @property int $id
 * @property int $resource
 * @property string $name
 * @property array $subscribers
 * @property string $createdon
 * @property int $createdby
 * @property bool $closed
 * @property bool $deleted
 * @property string $deletedon
 * @property int $deletedby
 * @property int $comment_last
 * @property string $comment_time
 * @property int $comments
 */
class TicketThread extends xPDOSimpleObject
{
    /**
     * Get total comments count
     * 
     * @return int
     */
    public function getCommentsCount(): int
    {
        return (int)$this->get('comments');
    }

    /**
     * Update last comment information
     */
    public function updateLastComment(): void
    {
        $q = $this->xpdo->newQuery(TicketComment::class, [
            'thread' => $this->id,
            'published' => 1,
            'deleted' => 0
        ]);
        $q->sortby('createdon', 'DESC');
        $q->limit(1);
        $q->select('id as comment_last, createdon as comment_time');
        
        if ($q->prepare() && $q->stmt->execute()) {
            $comment = $q->stmt->fetch(PDO::FETCH_ASSOC);
            if (empty($comment)) {
                $comment = [
                    'comment_last' => 0,
                    'comment_time' => 0,
                ];
            }
            $this->fromArray($comment);
            $this->save();
        }

        $this->updateCommentsCount();
    }

    /**
     * Update total comments count
     * 
     * @return int
     */
    public function updateCommentsCount(): int
    {
        $comments = 0;
        $q = $this->xpdo->newQuery(TicketComment::class, [
            'thread' => $this->id,
            'published' => 1,
            'deleted' => 0
        ]);
        $q->select('COUNT(`id`)');
        
        if ($q->prepare() && $q->stmt->execute()) {
            $comments = (int)$q->stmt->fetch(PDO::FETCH_COLUMN);
            $this->set('comments', $comments);
            $this->save();
        }

        return $comments;
    }

    /**
     * Build comments tree
     * 
     * @param array $comments
     * @param int $depth
     *
     * @return array
     */
    public function buildTree(array $comments = [], int $depth = 0): array
    {
        $tree = [];
        foreach ($comments as $id => &$row) {
            $row['has_children'] = $row['level'] = 0;

            if (empty($row['parent']) || !isset($comments[$row['parent']])) {
                $tree[$id] = &$row;
            } else {
                $parent = $row['parent'];
                $level = $comments[$parent]['level'];
                $comments[$parent]['has_children'] = 1;

                if (!empty($depth) && $level >= $depth) {
                    $parent = $comments[$parent]['new_parent'];
                    $row['new_parent'] = $parent;
                    $row['level'] = $level;
                } else {
                    $row['level'] = $level + 1;
                }

                $comments[$parent]['children'][$id] = &$row;
            }
        }

        return $tree;
    }

    /**
     * Subscribe user to thread
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

        $subscribers = $this->get('subscribers');
        if (empty($subscribers) || !is_array($subscribers)) {
            $subscribers = [];
        }

        $found = array_search($uid, $subscribers);
        if ($found === false) {
            $subscribers[] = $uid;
        } else {
            unset($subscribers[$found]);
        }
        $this->set('subscribers', array_values($subscribers));
        $this->save();

        return ($found === false);
    }

    /**
     * Check if user is subscribed to thread
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

        $subscribers = $this->get('subscribers');
        if (empty($subscribers) || !is_array($subscribers)) {
            $subscribers = [];
        }

        return in_array($uid, $subscribers);
    }

    /**
     * Remove thread with all comments
     * 
     * @param array $ancestors
     *
     * @return bool
     */
    public function remove(array $ancestors = []): bool
    {
        $collection = $this->xpdo->getIterator(TicketComment::class, [
            'thread' => $this->id,
            'parent' => 0
        ]);
        
        /** @var TicketComment $item */
        foreach ($collection as $item) {
            $item->remove();
        }

        return parent::remove($ancestors);
    }
} 