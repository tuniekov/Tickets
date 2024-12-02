<?php

use MODX\Revolution\modExtraManagerController;
use Tickets2\Tickets2;

class Tickets2HomeManagerController extends modExtraManagerController
{
    /**
     * @return array
     */
    public function getLanguageTopics(): array
    {
        return ['tickets2:default'];
    }

    /**
     * @return null|string
     */
    public function getPageTitle(): ?string
    {
        return $this->modx->lexicon('tickets2');
    }

    /**
     * Load custom CSS/JS for the page
     */
    public function loadCustomCssJs(): void
    {
        /** @var Tickets2 $Tickets2 */
        $Tickets2 = $this->modx->getService('Tickets2');

        $Tickets2->loadManagerFiles($this, [
            'config' => true,
            'utils' => true,
            'css' => true,
            'threads' => true,
            'comments' => true,
            'tickets2' => true,
            'authors' => true,
        ]);
        $this->addLastJavascript($Tickets2->config['jsUrl'] . 'mgr/home.js');
        $this->addLastJavascript($Tickets2->config['jsUrl'] . 'mgr/misc/strftime-min-1.3.js');
        $this->addHtml('
        <script type="text/javascript">
        Ext.onReady(function() {
            MODx.load({xtype: "tickets2-page-home"});
        });
        </script>');
    }

    /**
     * @return string
     */
    public function getTemplateFile(): string
    {
        /** @var Tickets2 $Tickets2 */
        $Tickets2 = $this->modx->getService('Tickets2');

        return $Tickets2->config['templatesPath'] . 'home.tpl';
    }
}