<?php

use Tickets2\Model\Tickets2Section;
use Tickets2\Tickets2;

class Tickets2SectionUpdateManagerController extends ResourceUpdateManagerController
{
    /** @var Tickets2Section $resource */
    public $resource;

    /**
     * Returns language topics
     *
     * @return array
     */
    public function getLanguageTopics(): array
    {
        return ['resource', 'user', 'tickets2:default'];
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
        $this->resourceArray['properties']['tickets2'] = $this->resource->getProperties('tickets2');
        $this->resourceArray['properties']['ratings'] = $this->resource->getProperties('ratings');
        $this->resourceArray['syncsite'] = (bool)$this->resource->getProperties('syncsite');

        /** @var Tickets2 $Tickets2 */
        $Tickets2 = $this->modx->getService('Tickets2');
        $Tickets2->loadManagerFiles($this, [
            'config' => true,
            'utils' => true,
            'css' => true,
            'section' => true,
            'subscribe' => true,
            'comments' => true,
        ]);
        $this->addLastJavascript($Tickets2->config['jsUrl'] . 'mgr/section/update.js');
        $this->addLastJavascript($Tickets2->config['jsUrl'] . 'mgr/misc/strftime-min-1.3.js');

        $ready = [
            'xtype' => 'tickets2-page-section-update',
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
