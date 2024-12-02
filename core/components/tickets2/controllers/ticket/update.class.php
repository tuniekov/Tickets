<?php

use Tickets2\Model\Ticket;
use Tickets2\Tickets2;

class TicketUpdateManagerController extends ResourceUpdateManagerController
{
    /** @var Ticket $resource */
    public $resource;

    /**
     * Returns language topics
     * @return array
     */
    public function getLanguageTopics(): array
    {
        return ['resource', 'tickets2:default'];
    }

    /**
     * Check for any permissions or requirements to load page
     * @return bool
     */
    public function checkPermissions(): bool
    {
        return $this->modx->hasPermission('new_document');
    }

    /**
     * Register custom CSS/JS for the page
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
        $this->resourceArray['properties']['tickets2'] = $this->resource->getProperties('tickets2');

        /** @var Tickets2 $Tickets2 */
        $Tickets2 = $this->modx->getService('Tickets2');
        $Tickets2->loadManagerFiles($this, [
            'config' => true,
            'utils' => true,
            'css' => true,
            'ticket' => true,
            'comments' => true,
        ]);
        $this->addLastJavascript($Tickets2->config['jsUrl'] . 'mgr/ticket/update.js');
        $this->addLastJavascript($Tickets2->config['jsUrl'] . 'mgr/misc/strftime-min-1.3.js');

        $neighborhood = [];
        if ($this->resource instanceof Ticket) {
            $neighborhood = $this->resource->getNeighborhood();
        }
        $ready = [
            'xtype' => 'tickets2-page-ticket-update',
            'resource' => $this->resource->get('id'),
            'record' => $this->resourceArray,
            'publish_document' => (int)$this->canPublish,
            'preview_url' => $this->previewUrl,
            'locked' => (int)$this->locked,
            'lockedText' => $this->lockedText,
            'canSave' => (int)$this->canSave,
            'canEdit' => (int)$this->canEdit,
            'canCreate' => (int)$this->canCreate,
            'canDuplicate' => (int)$this->canDuplicate,
            'canDelete' => (int)$this->canDelete,
            'show_tvs' => (int)!empty($this->tvCounts),
            'next_page' => !empty($neighborhood['right'][0])
                ? $neighborhood['right'][0]
                : 0,
            'prev_page' => !empty($neighborhood['left'][0])
                ? $neighborhood['left'][0]
                : 0,
            'up_page' => $this->resource->parent,
            'mode' => 'update',
        ];
        $this->addHtml('
        <script type="text/javascript">
        // <![CDATA[
        MODx.config.publish_document = ' . (int)$this->canPublish . ';
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
