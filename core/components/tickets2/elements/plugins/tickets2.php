<?php

use MODX\Revolution\modX;
use MODX\Revolution\modUser;
use Tickets2\Model\Ticket;
use Tickets2\Model\TicketAuthor;
use Tickets2\Model\Tickets2Section;
use Tickets2\Tickets2;

/** @var modX $modx */
switch ($modx->event->name) {

    case 'OnSiteRefresh':
        if ($modx->cacheManager->refresh(['default/tickets2' => []])) {
            $modx->log(modX::LOG_LEVEL_INFO, $modx->lexicon('refresh_default') . ': Tickets2');
        }
        break;

    case 'OnDocFormSave':
        /** @var Ticket $resource */
        if ($mode == 'new' && $resource->class_key == Ticket::class) {
            $modx->cacheManager->delete('tickets2/latest.tickets2');
        }
        break;

    case 'OnWebPagePrerender':
        $output = &$modx->resource->_output;
        $output = str_replace(
            ['*(*(*(*(*(*', '*)*)*)*)*)*', '~(~(~(~(~(~', '~)~)~)~)~)~'],
            ['[', ']', '{', '}'],
            $output
        );
        break;

    case 'OnPageNotFound':
        // It is working only with friendly urls enabled
        $q = trim(@$_REQUEST[$modx->context->getOption('request_param_alias', 'q')]);
        $matches = explode('/', rtrim($q, '/'));
        if (count($matches) < 2) {
            return;
        }

        $ticket_uri = array_pop($matches);
        $section_uri = implode('/', $matches) . '/';

        if ($section_id = $modx->findResource($section_uri)) {
            /** @var Tickets2Section $section */
            if ($section = $modx->getObject(Tickets2Section::class, ['id' => $section_id])) {
                if (is_numeric($ticket_uri)) {
                    $ticket_id = $ticket_uri;
                } elseif (preg_match('#^\d+#', $ticket_uri, $tmp)) {
                    $ticket_id = $tmp[0];
                } else {
                    $properties = $section->getProperties('tickets2');
                    if (!empty($properties['uri']) && strpos($properties['uri'], '%id') !== false) {
                        $pcre = str_replace('%id', '([0-9]+)', $properties['uri']);
                        $pcre = preg_replace('#(\%[a-z]+)#', '(?:.*?)', $pcre);
                        if (@preg_match('#' . trim($pcre, '/') . '#', $ticket_uri, $matches)) {
                            $ticket_id = $matches[1];
                        }
                    }
                }
                if (!empty($ticket_id)) {
                    if ($ticket = $modx->getObject(Ticket::class, ['id' => $ticket_id, 'deleted' => 0])) {
                        if ($ticket->published) {
                            $modx->sendRedirect($modx->makeUrl($ticket_id),
                                ['responseCode' => 'HTTP/1.1 301 Moved Permanently']);
                        } elseif ($unp_id = $modx->getOption('tickets2.unpublished_ticket_page')) {
                            $modx->sendForward($unp_id, 'HTTP/1.1 403 Forbidden');
                        }
                    }
                }
            }
        }
        break;

    case 'OnLoadWebDocument':
        $authenticated = $modx->user->isAuthenticated($modx->context->get('key'));
        $key = 'Tickets2_User';

        if (!$authenticated && !$modx->getOption('tickets2.count_guests')) {
            return;
        }

        if (empty($_COOKIE[$key])) {
            if (!empty($_SESSION[$key])) {
                $guest_key = $_SESSION[$key];
            } else {
                $guest_key = $_SESSION[$key] = md5(rand() . time() . rand());
            }
            setcookie($key, $guest_key, time() + (86400 * 365), '/');
        } else {
            $guest_key = $_COOKIE[$key];
        }

        if (empty($_SESSION[$key])) {
            $_SESSION[$key] = $guest_key;
        }

        if ($authenticated) {
            /** @var TicketAuthor $profile */
            if (!$profile = $modx->user->getOne('AuthorProfile')) {
                $profile = $modx->newObject(TicketAuthor::class);
                $modx->user->addOne($profile);
            }
            $profile->set('visitedon', time());
            $profile->save();
        }
        break;

    case 'OnWebPageComplete':
        /** @var Tickets2 $Tickets2 */
        $Tickets2 = $modx->getService('Tickets2');
        $Tickets2->logView($modx->resource->get('id'));
        break;

    case 'OnUserSave':
        /** @var modUser $user */
        if ($mode == 'new' && $user && !$user->getOne('AuthorProfile')) {
            $profile = $modx->newObject(TicketAuthor::class);
            $user->addOne($profile);
            $profile->save();
        }
        break;
}
