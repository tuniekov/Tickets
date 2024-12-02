<?php

use Tickets2\Model\Ticket;
use Tickets2\Model\Tickets2Section;
use Tickets2\Tickets2;

class TicketCreateManagerController extends ResourceCreateManagerController
{
    /** @var Tickets2Section $parent */
    public $parent;
    /** @var Ticket $resource */
    public $resource;

    /**
     * Returns language topics
     *
     * @return array
     */
    public function getLanguageTopics(): array
    {
        return ['resource', 'tickets2:default'];
    }

    /**
     * Return the default template for this resource
     *
     * @return int
     */
    public function getDefaultTemplate(): int
    {
        $properties = $this->parent->getProperties();
        return $properties['template'];
    }

    /**
     * Register custom CSS/JS for the page
     *
     * @return void
     */
    public function loadCustomCssJs(): void
    {
        $html = $this->head['html'];
        parent::loadCustomCssJs();
        $this->head['html'] = $html;

        if (is_null($this->resourceArray['properties'])) {
            $this->resourceArray['properties'] = [];
        }
        $properties = $this->parent->getProperties('tickets2');
        $this->resourceArray = array_merge($this->resourceArray, $properties);
        $this->resourceArray['properties']['tickets2'] = $properties;

        /** @var Tickets2 $Tickets2 */
        $Tickets2 = $this->modx->getService('Tickets2');
        $Tickets2->loadManagerFiles($this, [
            'config' => true,
            'utils' => true,
            'css' => true,
            'ticket' => true,
        ]);
        $this->addLastJavascript($Tickets2->config['jsUrl'] . 'mgr/ticket/create.js');
        $this->addLastJavascript($Tickets2->config['jsUrl'] . 'mgr/misc/strftime-min-1.3.js');

        $ready = [
            'xtype' => 'tickets2-page-ticket-create',
            'record' => $this->resourceArray,
            'publish_document' => (int)$this->canPublish,
            'canSave' => (int)$this->canSave,
            'show_tvs' => (int)!empty($this->tvCounts),
            'mode' => 'create',
        ];
        $this->addHtml('
        <script type="text/javascript">
        // <![CDATA[
        MODx.config.publish_document = ' . (int)$this->canPublish . ';
        MODx.config.default_template = ' . $this->modx->getOption('tickets2.default_template', null,
                $this->modx->getOption('default_template'), true) . ';
        MODx.onDocFormRender = "' . $this->onDocFormRender . '";
        MODx.ctx = "' . $this->ctx . '";
        Ext.onReady(function() {
            MODx.load(' . json_encode($ready) . ');
        });
        // ]]>
        </script>');
        
        // load RTE
        $this->loadRichTextEditor();
    }
}
