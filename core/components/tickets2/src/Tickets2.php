<?php

namespace Tickets2;

use MODX\Revolution\modX;
use MODX\Revolution\modUser;
use MODX\Revolution\modSnippet;
use MODX\Revolution\modProcessorResponse;

class Tickets2
{
    /** @var modX $modx */
    public modX $modx;
    /** @var pdoFetch $pdoTools */
    public $pdoTools;
    public array $initialized = [];
    public bool $authenticated = false;
    private ?string $prepareCommentCustom = null;
    private int $last_view = 0;
    public array $config = [];

    /**
     * @param modX $modx
     * @param array $config
     */
    public function __construct(modX &$modx, array $config = [])
    {
        $this->modx =& $modx;

        $corePath = $this->modx->getOption('tickets2.core_path', $config,
            $this->modx->getOption('core_path') . 'components/tickets2/');
        $assetsPath = $this->modx->getOption('tickets2.assets_path', $config,
            $this->modx->getOption('assets_path') . 'components/tickets2/');
        $assetsUrl = $this->modx->getOption('tickets2.assets_url', $config,
            $this->modx->getOption('assets_url') . 'components/tickets2/');
        $actionUrl = $this->modx->getOption('tickets2.action_url', $config, $assetsUrl . 'action.php');
        $connectorUrl = $assetsUrl . 'connector.php';

        $this->config = array_merge([
            'assetsUrl' => $assetsUrl,
            'cssUrl' => $assetsUrl . 'css/',
            'jsUrl' => $assetsUrl . 'js/',
            'jsPath' => $assetsPath . 'js/',
            'imagesUrl' => $assetsUrl . 'img/',

            'connectorUrl' => $connectorUrl,
            'actionUrl' => $actionUrl,

            'corePath' => $corePath,
            // 'modelPath' => $corePath . 'model/',
            'chunksPath' => $corePath . 'elements/chunks/',
            'templatesPath' => $corePath . 'elements/templates/',
            'chunkSuffix' => '.chunk.tpl',
            'snippetsPath' => $corePath . 'elements/snippets/',
            'processorsPath' => $corePath . 'processors/',

            'fastMode' => false,
            'dateFormat' => 'd F Y, H:i',
            'dateNow' => 10,
            'dateDay' => 'day H:i',
            'dateMinutes' => 59,
            'dateHours' => 10,
            'charset' => $this->modx->getOption('modx_charset'),
            'snippetPrepareComment' => $this->modx->getOption('tickets2.snippet_prepare_comment'),
            'commentEditTime' => $this->modx->getOption('tickets2.comment_edit_time', null, 180),
            'depth' => 0,

            'gravatarUrl' => 'https://www.gravatar.com/avatar/',
            'gravatarSize' => 24,
            'gravatarIcon' => 'mm',

            'json_response' => true,
            'nestedChunkPrefix' => 'tickets2_',
            'allowGuest' => false,
            'allowGuestEdit' => false,
            'allowGuestEmails' => false,
            'enableCaptcha' => false,

            'requiredFields' => '',
        ], $config);

        // $this->modx->addPackage('Tickets2\Model', $this->config['modelPath']);
        $this->modx->lexicon->load('tickets2:default');

        if ($name = $this->config['snippetPrepareComment']) {
            if ($snippet = $this->modx->getObject(modSnippet::class, ['name' => $name])) {
                $this->prepareCommentCustom = $snippet->get('content');
            }
        }

        $this->authenticated = $this->modx->user->isAuthenticated($this->modx->context->get('key'));
    }

    /**
     * Initializes component into different contexts.
     *
     * @param string $ctx The context to load. Defaults to web.
     * @param array $scriptProperties
     *
     * @return boolean
     */
    public function initialize(string $ctx = 'web', array $scriptProperties = []): bool
    {
        $this->config = array_merge($this->config, $scriptProperties);
        if (!$this->pdoTools) {
            $this->loadPdoTools();
        }
        $this->pdoTools->setConfig($this->config);

        $this->config['ctx'] = $ctx;
        if (empty($this->initialized[$ctx])) {
            $config_js = [
                'ctx' => $ctx,
                'jsUrl' => $this->config['jsUrl'] . 'web/',
                'cssUrl' => $this->config['cssUrl'] . 'web/',
                'actionUrl' => $this->config['actionUrl'],
                'close_all_message' => $this->modx->lexicon('tickets2_message_close_all'),
                'tpanel' => (int)$this->authenticated,
                'enable_editor' => (int)$this->modx->getOption('tickets2.enable_editor'),
            ];
            
            $this->modx->regClientStartupScript(
                '<script type="text/javascript">
                    if (typeof Tickets2Config == "undefined") {
                        Tickets2Config=' . json_encode($config_js) . ';
                    } else {
                        MergeConfig=' . json_encode($config_js) . ';
                        for (var attrname in MergeConfig) {
                            Tickets2Config[attrname] = MergeConfig[attrname];
                        }
                    }
                </script>',
                true
            );

            if ($config_js['enable_editor']) {
                $this->modx->regClientStartupScript(
                    '<script type="text/javascript">
                        Tickets2Config.editor={
                            ticket: ' . $this->modx->getOption('tickets2.editor_config.ticket') . ',
                            comment: ' . $this->modx->getOption('tickets2.editor_config.comment') . '
                        };
                    </script>',
                    true
                );
                $this->modx->regClientScript($this->config['jsUrl'] . 'web/editor/jquery.markitup.js');
                $this->modx->regClientCSS($this->config['jsUrl'] . 'web/editor/editor.css');
            }
            $this->initialized[$ctx] = true;
        }

        if (!defined('MODX_API_MODE') || !MODX_API_MODE) {
            $config = $this->makePlaceholders($this->config);

            $css = !empty($this->config['frontend_css'])
                ? $this->config['frontend_css']
                : $this->modx->getOption('tickets2.frontend_css');
            if (!empty($css) && preg_match('/\.css/i', $css)) {
                $this->modx->regClientCSS(str_replace($config['pl'], $config['vl'], $css));
            }

            $js = !empty($this->config['frontend_js'])
                ? $this->config['frontend_js']
                : $this->modx->getOption('tickets2.frontend_js');
            if (!empty($js) && preg_match('/\.js/i', $js)) {
                $this->modx->regClientScript(str_replace($config['pl'], $config['vl'], $js));
            }
        }

        return true;
    }

    /**
     * Shorthand for the call of processor
     *
     * @param string $action Path to processor
     * @param array $data Data to be transmitted to the processor
     *
     * @return modProcessorResponse|null
     */
    public function runProcessor(string $action = '', array $data = []): ?modProcessorResponse
    {
        if (empty($action)) {
            return null;
        }
        $this->modx->error->reset();
        $processorsPath = !empty($this->config['processorsPath'])
            ? $this->config['processorsPath']
            : MODX_CORE_PATH . 'components/tickets2/processors/';

        return $this->modx->runProcessor($action, $data, ['processors_path' => $processorsPath]);
    }

    /**
     * Returns sanitized preview of Ticket
     *
     * @param array $data section, pagetitle, text, etc
     *
     * @return array|string
     */
    public function previewTicket(array $data = [])
    {
        $message = '';
        foreach ($data as $k => $v) {
            if ($k == 'content') {
                if (!$data[$k] = $this->Jevix($v, 'Ticket')) {
                    return $this->error($this->modx->lexicon('err_no_jevix'));
                }
            } else {
                $data[$k] = $this->sanitizeString($v);
            }
        }

        $preview = $this->getChunk($this->config['tplPreview'], $data);
        $preview = $this->pdoTools->fastProcess($preview);

        return $this->success($message, ['preview' => $preview]);
    }

    /**
     * Delete/unDelete ticket through processor and redirect to link
     *
     * @param array $data id, redirect
     * @param bool $restore
     *
     * @return array|string
     */
    public function deleteTicket(array $data, bool $restore = false)
    {
        $id = (int)$data['id'];
        if (empty($data['id'])) {
            return $this->error($this->modx->lexicon('ticket_err_id', ['id' => $id]));
        }
        
        $fields = [
            'class_key' => Model\Ticket::class,
            'id' => $id
        ];
        $processorname = $restore ? 'web/ticket/undelete' : 'web/ticket/delete';
        $response = $this->runProcessor($processorname, $fields);

        /** @var modProcessorResponse $response */
        if ($response->isError()) {
            $this->modx->log(modX::LOG_LEVEL_INFO,
                '[Tickets2] Unable to delete Ticket: ' . $response->getMessage());

            return $this->error($response->getMessage(), $response->getFieldErrors());
        }
        
        $message = $this->modx->lexicon($restore ? 'ticket_undeleted_text' : 'ticket_deleted_text');
        $is_redir = $restore ? 'redirectUnDeleted' : 'redirectDeleted';
        if (!empty($this->config[$is_redir])) {
            $url = $this->modx->makeUrl((int)$this->config[$is_redir], '', '', 'full');
        } else {
            $url = $_SERVER['HTTP_REFERER'];
            if (!preg_match('/\b' . $id . '\b/', $url)) {
                $url .= strpos($url, '?') !== false
                    ? '&tid=' . $id
                    : '?tid=' . $id;
            }
        }
        if (empty($url)) {
            $url = $this->modx->getOption('site_url');
        }
        $results['redirect'] = $url;

        return $this->success($message, $results);
    }

    /**
     * Save ticket through processor and redirect to it
     *
     * @param array $data section, pagetitle, text, etc
     *
     * @return array|string
     */
    public function saveTicket(array $data = [])
    {
        $requiredFields = array_map('trim', explode(',', $this->config['requiredFields']));
        $requiredFields = array_unique(array_merge($requiredFields, ['parent', 'pagetitle', 'content']));
        $allowedFields = array_map('trim', explode(',', $this->config['allowedFields']));
        $allowedFields = array_unique(array_merge($allowedFields, $requiredFields));
        $bypassFields = array_map('trim', explode(',', $this->config['bypassFields']));

        $validate = $this->config['validate'];
        $modelPath = $this->modx->getOption('formit.core_path', null, $this->modx->getOption('core_path').'components/formit/') .'model/formit/';
        if (!empty($validate) && file_exists($modelPath . 'formit.class.php')) {
            $fi = $this->modx->getService(
                'formit',
                'FormIt',
                $modelPath,
                $this->config
            );

            if ($fi instanceof \FormIt) {
                $fi->initialize($this->modx->context->get('key'));
                $fi->loadRequest();
                
                $fields = $fi->request->prepare();
                $fi->request->handle($fields);

                $errors = $fi->request->validator->getRawErrors();
                if (!empty($errors)) {
                    $data = [];
                    foreach ($errors as $field => $message) {
                        $data[$field] = ['field' => $field, 'message' => $message];
                    }
                    return $this->error('', $data);
                }
            }
        }

        $fields = [];
        foreach ($allowedFields as $field) {
            if (in_array($field, $allowedFields) && array_key_exists($field, $data)) {
                $value = $data[$field];
                if ($field !== 'content' && !in_array($field, $bypassFields)) {
                    $value = $this->sanitizeString($value);
                }
                $fields[$field] = $value;
            }
        }

        switch ($data['action']) {
            case 'ticket/save':
                $fields['published'] = null;
                break;
            case 'ticket/draft':
                $fields['published'] = false;
                break;
            default:
                $fields['published'] = true;
        }

        $fields['requiredFields'] = $requiredFields;
        $fields['class_key'] = Model\Ticket::class;
        if (!empty($this->config['sections']) && is_array($this->config['sections'])) {
            $fields['sections'] = $this->config['sections'];
        }
        
        if (!empty($data['tid'])) {
            $fields['id'] = (int)$data['tid'];
            if ($ticket = $this->modx->getObject(Model\Ticket::class, [
                'class_key' => Model\Ticket::class,
                'id' => $fields['id']
            ])) {
                $fields['context_key'] = $ticket->get('context_key');
                $fields['alias'] = $ticket->get('alias');
                $response = $this->modx->runProcessor('resource/update', $fields);
            } else {
                return $this->error($this->modx->lexicon('ticket_err_id', ['id' => $fields['id']]));
            }
        } else {
            $response = $this->modx->runProcessor('resource/create', $fields);
        }

        /** @var modProcessorResponse $response */
        if ($response->isError()) {
            $this->modx->log(modX::LOG_LEVEL_INFO,
                '[Tickets2] Unable to save Ticket: ' . $response->getMessage() . print_r($response->getFieldErrors(), 1));

            return $this->error($response->getMessage(), $response->getFieldErrors());
        } elseif ($ticket = $this->modx->getObject(Model\Ticket::class, $response->response['object']['id'])) {
            $ticket = $ticket->toArray();
            $this->sendTicketMails($ticket);
        }

        $id = $response->response['object']['id'];
        $message = '';
        $results = [
            'id' => $id,
            'content' => !empty($ticket['content'])
                ? html_entity_decode($ticket['content'])
                : '',
        ];

        switch ($data['action']) {
            case 'ticket/save':
                $message = $this->modx->lexicon('ticket_saved');
                break;
            case 'ticket/draft':
                if (!empty($this->config['redirectUnpublished'])) {
                    $url = $this->modx->makeUrl((int)$this->config['redirectUnpublished'], '', '', 'full');
                } else {
                    $url = $_SERVER['HTTP_REFERER'];
                    if (!preg_match('/\b' . $id . '\b/', $url)) {
                        $url .= strpos($url, '?') !== false
                            ? '&tid=' . $id
                            : '?tid=' . $id;
                    }
                }
                if (empty($url)) {
                    $url = $this->modx->getOption('site_url');
                }
                $results['redirect'] = $url;
                break;
            default:
                $url = $this->modx->makeUrl($id, '', '', 'full');
                if (empty($url)) {
                    $url = $this->modx->getOption('site_url');
                }
                $results['redirect'] = $url;
        }

        if ($this->modx->getOption('ms2gallery_sync_tickets2')) {
            /** @var ms2Gallery $ms2Gallery */
            $ms2Gallery = $this->modx->getService('ms2gallery', 'ms2Gallery',
                MODX_CORE_PATH . 'components/ms2gallery/model/ms2gallery/');
            if ($ms2Gallery && method_exists($ms2Gallery, 'syncFiles')) {
                $ms2Gallery->syncFiles('tickets2', $id, true);
            }
        }

        return $this->success($message, $results);
    }

    /**
     * Vote for ticket
     *
     * @param int $id
     * @param int $value
     *
     * @return array|string
     */
    public function voteTicket(int $id, int $value = 1)
    {
        $data = ['id' => $id, 'value' => $value];
        /** @var modProcessorResponse $response */
        if (!empty($id)) {
            $response = $this->runProcessor('web/ticket/vote', $data);
            if ($response->isError()) {
                return $this->error($response->getMessage());
            }
            
            $data = $response->getObject();
            $rating = abs($data['rating_plus']) + abs($data['rating_minus']);
            $data['title'] = $this->modx->lexicon('ticket_rating_total')
                . " {$rating}: ↑{$data['rating_plus']} "
                . $this->modx->lexicon('ticket_rating_and')
                . " ↓{$data['rating_minus']}";
            if ($data['rating'] > 0) {
                $data['rating'] = '+' . $data['rating'];
                $data['status'] = 1;
            } elseif ($data['rating'] < 0) {
                $data['status'] = -1;
            } else {
                $data['status'] = 0;
            }

            return $this->success('', $data);
        }

        return $this->error('tickets2_err_unknown');
    }

    /**
     * Star for ticket
     *
     * @param int $id
     *
     * @return array|string
     */
    public function starTicket(int $id)
    {
        /** @var modProcessorResponse $response */
        if (!empty($id)) {
            $response = $this->runProcessor('web/ticket/star', ['id' => $id]);
            if ($response->isError()) {
                return $this->error($response->getMessage());
            }
            return $this->success('', $response->getObject());
        }

        return $this->error('tickets2_err_unknown');
    }

    /**
     * Returns sanitized preview of comment
     *
     * @param array $data
     *
     * @return array|string
     */
    public function previewComment(array $data = [])
    {
        if (!empty($data['text'])) {
            $data['text'] = $this->Jevix($data['text'], 'Comment');
            $data['text'] = $this->pdoTools->fastProcess($data['text']);
            $preview = $this->getChunk($this->config['tplCommentPreview'], $data);
            $preview = $this->pdoTools->fastProcess($preview);
        }
        if (empty($preview)) {
            $preview = $this->modx->lexicon('ticket_comment_err_no_text');
        }

        return $this->success('', ['preview' => $preview]);
    }

    /**
     * Create or update comment
     *
     * @param array $data
     *
     * @return array|string
     */
    public function saveComment(array $data = [])
    {
        $data['allowGuest'] = $this->config['allowGuest'];
        $data['allowGuestEdit'] = $this->config['allowGuestEdit'];
        $data['allowGuestEmails'] = $this->config['allowGuestEmails'];

        /** @var modProcessorResponse $response */
        if (!empty($data['id'])) {
            $response = $this->runProcessor('web/comment/update', $data);
        } else {
            $response = $this->runProcessor('web/comment/create', $data);
        }

        if ($response->isError()) {
            return $this->error($response->getMessage(), $response->getFieldErrors());
        }

        $comment = $response->getObject();
        $comment['new'] = empty($data['id']);
        if (!empty($comment['name']) || !empty($comment['email'])) {
            $this->setCustomFields($comment);
        }

        return $this->success('', $comment);
    }

    /**
     * Vote for comment
     *
     * @param int $id
     * @param int $value
     *
     * @return array|string
     */
    public function voteComment(int $id, int $value = 1)
    {
        $data = ['id' => $id, 'value' => $value];
        /** @var modProcessorResponse $response */
        if (!empty($id)) {
            $response = $this->runProcessor('web/comment/vote', $data);
            if ($response->isError()) {
                return $this->error($response->getMessage());
            }
            
            $data = $response->getObject();
            if ($data['rating'] > 0) {
                $data['rating'] = '+' . $data['rating'];
                $data['status'] = 1;
            } elseif ($data['rating'] < 0) {
                $data['status'] = -1;
            } else {
                $data['status'] = 0;
            }

            return $this->success('', $data);
        }

        return $this->error('tickets2_err_unknown');
    }

    /**
     * Star for comment
     *
     * @param int $id
     *
     * @return array|string
     */
    public function starComment(int $id)
    {
        /** @var modProcessorResponse $response */
        if (!empty($id)) {
            $response = $this->runProcessor('web/comment/star', ['id' => $id]);
            if ($response->isError()) {
                return $this->error($response->getMessage());
            }
            return $this->success('', $response->getObject());
        }

        return $this->error('tickets2_err_unknown');
    }

    /**
     * Returns sanitized preview of comment
     *
     * @param array $data
     *
     * @return array|string
     */
    public function previewEmail(array $data = [])
    {
        if (!empty($data['text'])) {
            $data['text'] = nl2br($data['text']);
        }
        if (empty($data['text'])) {
            $data['text'] = $this->modx->lexicon('ticket_comment_err_no_text');
        }

        $preview = $this->getChunk($this->config['tplCommentEmailPreview'], $data);
        $preview = $this->pdoTools->fastProcess($preview);

        return $this->success('', ['preview' => $preview]);
    }

    /**
     * Sanitize any text through Jevix snippet
     *
     * @param string $text Text for sanitization
     * @param string $type Type of sanitization
     *
     * @return string
     */
    public function Jevix(string $text, string $type = 'Ticket'): string
    {
        if ($snippet = $this->modx->getObject(modSnippet::class, ['name' => 'Jevix'])) {
            $params = [];
            if ($snippet_params = $snippet->getProperties()) {
                $params = $snippet_params[$type] ?? $snippet_params['Ticket'];
            }
            $text = $snippet->process(['input' => $text] + $params);
        }

        return $text;
    }

    /**
     * Sanitize MODX tags
     *
     * @param string $text Text for sanitization
     *
     * @return string
     */
    public function sanitizeString(string $text): string
    {
        $text = strip_tags($text);
        $text = str_replace([
            '[',
            ']',
            '`',
            '{',
            '}'
        ], [
            '&#91;',
            '&#93;',
            '&#96;',
            '&#123;',
            '&#125;',
        ], $text);

        return $text;
    }

    /**
     * Process template with values
     *
     * @param string $name Name of template
     * @param array $values Values for processing
     *
     * @return string
     */
    public function getChunk(string $name, array $values = []): string
    {
        if (!$this->pdoTools) {
            $this->loadPdoTools();
        }

        return $this->pdoTools->getChunk($name, $values);
    }

    /**
     * Email notifications about new ticket
     *
     * @param array $ticket
     */
    public function sendTicketMails(array $ticket): void
    {
        /** @var Tickets2Section $section */
        if (!$section = $this->modx->getObject(Model\Tickets2Section::class, $ticket['parent'])) {
            return;
        }

        $properties = $section->getProperties();
        $subscribers = !empty($properties['subscribers'])
            ? $properties['subscribers']
            : [];

        $owner_id = $ticket['createdby'];
        if (empty($subscribers) || !is_array($subscribers)) {
            return;
        } elseif (($key = array_search($owner_id, $subscribers)) !== false) {
            unset($subscribers[$key]);
        }

        if (!empty($subscribers)) {
            $users = $this->modx->getIterator(modUser::class, ['id:IN' => $subscribers, 'active' => 1]);

            /** @var modUser $user */
            foreach ($users as $user) {
                /** @var modUserProfile $profile */
                $profile = $user->getOne('Profile');
                if ($profile->get('email')) {
                    $this->modx->log(modX::LOG_LEVEL_INFO, "Sending email to {$profile->get('email')}");
                    $this->modx->runProcessor('web/ticket/email', [
                        'ticket' => $ticket['id'],
                        'email' => $profile->get('email'),
                    ]);
                }
            }
        }
    }

    /**
     * Formats date to "10 minutes ago" or "Yesterday in 22:10"
     * This algorithm taken from https://github.com/livestreet/livestreet/blob/7a6039b21c326acf03c956772325e1398801c5fe/engine/modules/viewer/plugs/function.date_format.php
     *
     * @param string|int $date Timestamp to format
     * @param string|null $dateFormat
     *
     * @return string
     */
    public function dateFormat($date, ?string $dateFormat = null): string
    {
        $date = preg_match('/^\d+$/', $date)
            ? $date
            : strtotime($date);
        $dateFormat = !empty($dateFormat)
            ? $dateFormat
            : $this->config['dateFormat'];
        $current = time();
        $delta = $current - $date;

        if ($this->config['dateNow']) {
            if ($delta < $this->config['dateNow']) {
                return $this->modx->lexicon('ticket_date_now');
            }
        }

        if ($this->config['dateMinutes']) {
            $minutes = round(($delta) / 60);
            if ($minutes < $this->config['dateMinutes']) {
                if ($minutes > 0) {
                    return $this->declension($minutes,
                        $this->modx->lexicon('ticket_date_minutes_back', ['minutes' => $minutes]));
                }
                return $this->modx->lexicon('ticket_date_minutes_back_less');
            }
        }

        if ($this->config['dateHours']) {
            $hours = round(($delta) / 3600);
            if ($hours < $this->config['dateHours']) {
                if ($hours > 0) {
                    return $this->declension($hours,
                        $this->modx->lexicon('ticket_date_hours_back', ['hours' => $hours]));
                }
                return $this->modx->lexicon('ticket_date_hours_back_less');
            }
        }

        if ($this->config['dateDay']) {
            switch (date('Y-m-d', $date)) {
                case date('Y-m-d'):
                    $day = $this->modx->lexicon('ticket_date_today');
                    break;
                case date('Y-m-d', mktime(0, 0, 0, date('m'), date('d') - 1, date('Y'))):
                    $day = $this->modx->lexicon('ticket_date_yesterday');
                    break;
                case date('Y-m-d', mktime(0, 0, 0, date('m'), date('d') + 1, date('Y'))):
                    $day = $this->modx->lexicon('ticket_date_tomorrow');
                    break;
                default:
                    $day = null;
            }
            if ($day) {
                $format = str_replace("day", preg_replace("#(\w{1})#", '\\\${1}', $day), $this->config['dateDay']);
                return date($format, $date);
            }
        }

        $m = date("n", $date);
        $month_arr = json_decode($this->modx->lexicon('ticket_date_months'), true);
        $month = $month_arr[$m - 1];

        $format = preg_replace("~(?<!\\\\)F~U", preg_replace('~(\w{1})~u', '\\\${1}', $month), $dateFormat);

        return date($format, $date);
    }

    /**
     * Logs user views of a Resource. Need for new comments feature.
     *
     * @param integer $resource An id of resource
     */
    public function logView(int $resource): void
    {
        $key = 'Tickets2_User';

        if (!$this->authenticated) {
            if (!$this->modx->getOption('tickets2.count_guests', false)) {
                return;
            }
            $guest_key = $_SESSION[$key];
        } else {
            if (!empty($_SESSION[$key])) {
                $table = $this->modx->getTableName(Model\TicketView::class);
                $this->modx->exec("DELETE FROM {$table} WHERE `uid` = 0 AND `guest_key` = '{$_SESSION[$key]}' AND `parent` = {$resource};");
            }
            $guest_key = '';
        }

        $key = [
            'uid' => $this->modx->user->get('id'),
            'guest_key' => $guest_key,
            'parent' => $resource,
        ];
        if (!$view = $this->modx->getObject(Model\TicketView::class, $key)) {
            $view = $this->modx->newObject(Model\TicketView::class);
            $view->fromArray($key, '', true, true);
        }
        $view->set('timestamp', date('Y-m-d H:i:s'));
        $view->save();
    }

    /**
     * Generate captcha and set it to session
     *
     * @return array
     */
    public function getCaptcha(): array
    {
        $min = !empty($this->config['minCaptcha'])
            ? (int)$this->config['minCaptcha']
            : 1;
        $max = !empty($this->config['maxCaptcha'])
            ? (int)$this->config['maxCaptcha']
            : 10;
        $a = mt_rand($min, $max);
        $b = mt_rand($min, $max);
        $_SESSION['TicketComments']['captcha'] = $a + $b;

        return ['a' => $a, 'b' => $b];
    }

    /**
     * Upload file for ticket
     *
     * @param array $data
     * @param string $class
     *
     * @return array|string
     */
    public function fileUpload(array $data, string $class = 'Ticket')
    {
        if (!$this->authenticated || empty($this->config['allowFiles'])) {
            return $this->error('ticket_err_access_denied');
        }

        $data['source'] = $this->config['source'];
        $data['class'] = $class;

        /** @var modProcessorResponse $response */
        $response = $this->runProcessor('web/file/upload', $data);
        if ($response->isError()) {
            return $this->error($response->getMessage());
        }
        $file = $response->getObject();
        $file['size'] = round($file['size'] / 1024, 2);
        $file['new'] = empty($file['new']);

        $tpl = $file['type'] == 'image'
            ? $this->config['tplImage']
            : $this->config['tplFile'];
        $html = $this->getChunk($tpl, $file);

        return $this->success('', $html);
    }

    /**
     * Upload file for ticket comment
     *
     * @param array $data
     * @param string $class
     *
     * @return array|string
     */
    public function fileUploadComment(array $data, string $class = 'TicketComment')
    {
        $data['source'] = $this->config['source'];
        $data['class'] = $class;

        /** @var modProcessorResponse $response */
        $response = $this->runProcessor('web/file/upload.comment', $data);
        if ($response->isError()) {
            return $this->error($response->getMessage());
        }
        $file = $response->getObject();
        $file['size'] = round($file['size'] / 1024, 2);
        $file['new'] = empty($file['new']);

        $tpl = $file['type'] == 'image'
            ? $this->config['tplImage']
            : $this->config['tplFile'];
        $html = $this->getChunk($tpl, $file);

        return $this->success('', $html);
    }

    /**
     * Delete uploaded file
     *
     * @param int $id
     *
     * @return array|string
     */
    public function fileDelete(int $id)
    {
        if (!$this->authenticated || empty($this->config['allowFiles'])) {
            return $this->error('ticket_err_access_denied');
        }
        /** @var modProcessorResponse $response */
        $response = $this->runProcessor('web/file/delete', ['id' => $id]);
        if ($response->isError()) {
            return $this->error($response->getMessage());
        }

        return $this->success();
    }

    /**
     * Sort uploaded files
     *
     * @param array $rank
     *
     * @return array|string
     */
    public function fileSort(array $rank)
    {
        if (!$this->authenticated) {
            return $this->error('ticket_err_access_denied');
        }
        $response = $this->runProcessor('web/file/sort', ['rank' => $rank]);
        if ($response->isError()) {
            return $this->error($response->getMessage());
        }
        return $this->success();
    }

    /**
     * Returns error response
     *
     * @param string $message A lexicon key for error message
     * @param array $data Additional data
     * @param array $placeholders Array with placeholders for lexicon entry
     *
     * @return array|string
     */
    public function error(string $message = '', array $data = [], array $placeholders = [])
    {
        $response = [
            'success' => false,
            'message' => $this->modx->lexicon($message, $placeholders),
            'data' => $data,
        ];

        return $this->config['json_response']
            ? json_encode($response)
            : $response;
    }

    /**
     * Returns success response
     *
     * @param string $message
     * @param array $data
     * @param array $placeholders
     *
     * @return array|string
     */
    public function success(string $message = '', array $data = [], array $placeholders = [])
    {
        $response = [
            'success' => true,
            'message' => $this->modx->lexicon($message, $placeholders),
            'data' => $data,
        ];

        return $this->config['json_response']
            ? json_encode($response)
            : $response;
    }

    /**
     * Compares MODX version
     *
     * @param string $version
     * @param string $dir
     *
     * @return bool
     */
    public function systemVersion(string $version = '2.3.0', string $dir = '>='): bool
    {
        $this->modx->getVersionData();
        return !empty($this->modx->version) && version_compare($this->modx->version['full_version'], $version, $dir);
    }

    /**
     * Load pdoTools
     */
    private function loadPdoTools(): void
    {
        if (!$this->pdoTools) {
            if (!class_exists('pdoTools')) {
                require_once MODX_CORE_PATH . 'components/pdotools/model/pdotools/pdotools.class.php';
            }
            $this->pdoTools = new \pdoTools($this->modx);
        }
    }

    /**
     * Make placeholders for config
     *
     * @param array $config
     * @param string $prefix
     * @param string $suffix
     *
     * @return array
     */
    private function makePlaceholders(array $config = [], string $prefix = '[[+', string $suffix = ']]'): array
    {
        $result = [
            'pl' => [],
            'vl' => [],
        ];

        foreach ($config as $k => $v) {
            if (is_array($v)) {
                $result = array_merge_recursive($result, $this->makePlaceholders($v, $prefix . $k . '.', $suffix));
            } else {
                $result['pl'][$prefix . $k . $suffix] = '[[+' . $k . ']]';
                $result['vl'][$prefix . $k . $suffix] = $v;
            }
        }

        return $result;
    }

    /**
     * @param \MODX\Revolution\modManagerController $controller
     * @param array $properties
     */
    public function loadManagerFiles(\MODX\Revolution\modManagerController $controller, array $properties = []): void
    {
        $tickets2AssetsUrl = $this->config['assetsUrl'];
        $connectorUrl = $this->config['connectorUrl'];
        $tickets2CssUrl = $this->config['cssUrl'] . 'mgr/';
        $tickets2JsUrl = $this->config['jsUrl'] . 'mgr/';

        if (!empty($properties['config'])) {
            $tmp = [
                'assets_js' => $tickets2AssetsUrl,
                'connector_url' => $connectorUrl,
            ];
            $controller->addHtml('<script type="text/javascript">Tickets2.config = ' . json_encode($tmp) . ';</script>');
        }
        if (!empty($properties['utils'])) {
            $controller->addJavascript($tickets2JsUrl . 'tickets2.js');
            $controller->addLastJavascript($tickets2JsUrl . 'misc/utils.js');
            $controller->addLastJavascript($tickets2JsUrl . 'misc/combos.js');
        }
        if (!empty($properties['css'])) {
            $controller->addCss($tickets2CssUrl . 'tickets2.css');
            $controller->addCss($tickets2CssUrl . 'bootstrap.buttons.css');
        }

        if (!empty($properties['section'])) {
            $controller->addLastJavascript($tickets2JsUrl . 'section/section.common.js');
            $controller->addLastJavascript($tickets2JsUrl . 'ticket/tickets2.panel.js');
            $controller->addLastJavascript($tickets2JsUrl . 'ticket/tickets2.grid.js');
        }
        if (!empty($properties['subscribe'])) {
            $controller->addLastJavascript($tickets2JsUrl . 'subscribe/subscribes.panel.js');
            $controller->addLastJavascript($tickets2JsUrl . 'subscribe/subscribes.grid.js');
        }
        if (!empty($properties['ticket'])) {
            $controller->addLastJavascript($tickets2JsUrl . 'ticket/ticket.common.js');
        }
        if (!empty($properties['tickets2'])) {
            $controller->addLastJavascript($tickets2JsUrl . 'ticket/tickets2.panel.js');
            $controller->addLastJavascript($tickets2JsUrl . 'ticket/tickets2.grid.js');
        }
        if (!empty($properties['threads'])) {
            $controller->addLastJavascript($tickets2JsUrl . 'thread/threads.panel.js');
            $controller->addLastJavascript($tickets2JsUrl . 'thread/threads.grid.js');
            $controller->addLastJavascript($tickets2JsUrl . 'thread/thread.window.js');
            $controller->addLastJavascript($tickets2JsUrl . 'comment/comments.grid.js');
            $controller->addLastJavascript($tickets2JsUrl . 'comment/comment.window.js');
        }
        if (!empty($properties['comments'])) {
            $controller->addLastJavascript($tickets2JsUrl . 'comment/comments.panel.js');
            $controller->addLastJavascript($tickets2JsUrl . 'comment/comments.grid.js');
            $controller->addLastJavascript($tickets2JsUrl . 'comment/comment.window.js');
        }
        if (!empty($properties['authors'])) {
            $controller->addLastJavascript($tickets2JsUrl . 'author/authors.panel.js');
            $controller->addLastJavascript($tickets2JsUrl . 'author/authors.grid.js');
        }
    }
} 