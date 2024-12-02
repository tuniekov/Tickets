<?php
/** @var array $scriptProperties */
/** @var Tickets2 $Tickets2 */
$Tickets2 = $modx->getService('tickets2', 'Tickets2', $modx->getOption('tickets2.core_path', null,
        $modx->getOption('core_path') . 'components/tickets2/') . 'model/tickets2/', $scriptProperties);
$Tickets2->initialize($modx->context->key, $scriptProperties);

if (!$Tickets2->authenticated) {
    return $modx->lexicon('ticket_err_no_auth');
}

$tplSectionRow = $modx->getOption('tplSectionRow', $scriptProperties, 'tpl.Tickets2.sections.row');
$tplFormCreate = $modx->getOption('tplFormCreate', $scriptProperties, 'tpl.Tickets2.form.create');
$tplFormUpdate = $modx->getOption('tplFormUpdate', $scriptProperties, 'tpl.Tickets2.form.update');
$tplFiles = $modx->getOption('tplFiles', $scriptProperties, 'tpl.Tickets2.form.files');
$tplFile = $Tickets2->config['tplFile'] = $modx->getOption('tplFile', $scriptProperties, 'tpl.Tickets2.form.file', true);
$tplImage = $Tickets2->config['tplImage'] = $modx->getOption('tplImage', $scriptProperties, 'tpl.Tickets2.form.image',
    true);
$allowDelete = $modx->getOption('allowDelete', $scriptProperties);
if (empty($source)) {
    $source = $Tickets2->config['source'] = $modx->getOption('tickets2.source_default', null,
        $modx->getOption('default_media_source'));
}
$tid = !empty($_REQUEST['tid'])
    ? (int)$_REQUEST['tid']
    : (!empty($tid) ? (int)$tid : 0);
$parent = !empty($_REQUEST['parent'])
    ? $_REQUEST['parent']
    : '';
$data = array();

// Update of ticket
if (!empty($tid)) {
    $tplWrapper = $tplFormUpdate;
    /** @var Ticket $ticket */
    if ($ticket = $modx->getObject('Ticket', array('class_key' => 'Ticket', 'id' => $tid))) {
        if ($ticket->get('createdby') != $modx->user->id && !$modx->hasPermission('edit_document')) {
            return $modx->lexicon('ticket_err_wrong_user');
        }
        $charset = $modx->getOption('modx_charset');
        $allowedFields = array_map('trim', explode(',', $scriptProperties['allowedFields']));
        $allowedFields = array_unique(array_merge($allowedFields, array('parent', 'pagetitle', 'content')));

        $fields = array_keys($modx->getFieldMeta('Ticket'));
        foreach ($allowedFields as $field) {
            $value = in_array($field, $fields)
                ? $ticket->get($field)
                : $ticket->getTVValue($field);
            if (is_string($value)) {
                $value = html_entity_decode($value, ENT_QUOTES, $charset);
                $value = str_replace(
                    array('[^', '^]', '[', ']', '{', '}'),
                    array('&#91;^', '^&#93;', '*(*(*(*(*(*', '*)*)*)*)*)*', '~(~(~(~(~(~', '~)~)~)~)~)~'),
                    $value
                );
                $value = htmlentities($value, ENT_QUOTES, $charset);
            }
            $data[$field] = $value;
        }
        $data['id'] = $ticket->id;
        $data['published'] = $ticket->published;
        $data['deleted'] = $ticket->deleted;
        $data['allowDelete'] = $allowDelete ? 1 : 0;
        if (empty($parent)) {
            $parent = $ticket->get('parent');
        }
    } else {
        return $modx->lexicon('ticket_err_id', array('id' => $tid));
    }
} else {
    $tplWrapper = $tplFormCreate;
}

// Get available sections for ticket create
$data['sections'] = '';
/** @var modProcessorResponse $response */
$response = $Tickets2->runProcessor('web/section/getlist', array(
    'parents' => $scriptProperties['parents'],
    'resources' => $scriptProperties['resources'],
    'sortby' => !empty($scriptProperties['sortby'])
        ? $scriptProperties['sortby']
        : 'pagetitle',
    'sortdir' => !empty($scriptProperties['sortdir'])
        ? $scriptProperties['sortdir']
        : 'asc',
    'depth' => isset($scriptProperties['depth'])
        ? $scriptProperties['depth']
        : 0,
    'context' => !empty($scriptProperties['context'])
        ? $scriptProperties['context']
        : $modx->context->key,
    'limit' => 0,
));
$response = json_decode($response->getResponse(), true);

if (!empty($response['results'])) {
    $Tickets2->config['sections'] = array();
    foreach ($response['results'] as $v) {
        $v['selected'] = $parent == $v['id'] || $parent == $v['alias']
            ? 'selected'
            : '';
        $data['sections'] .= $Tickets2->getChunk($tplSectionRow, $v);
        $Tickets2->config['sections'][] = $v['id'];
    }
}

if (!empty($allowFiles)) {
    $q = $modx->newQuery('TicketFile');
    $q->where(array('class' => 'Ticket'));
    if (!empty($tid)) {
        $q->andCondition(array('parent' => $tid, 'createdby' => $modx->user->id), null, 1);
    } else {
        $q->andCondition(array('parent' => 0, 'createdby' => $modx->user->id), null, 1);
    }
    $q->sortby('rank', 'ASC');
    $q->sortby('createdon', 'ASC');
    $collection = $modx->getIterator('TicketFile', $q);
    $files = '';
    /** @var TicketFile $item */
    foreach ($collection as $item) {
        if ($item->get('deleted') && !$item->get('parent')) {
            $item->remove();
        } else {
            $item = $item->toArray();
            $item['size'] = round($item['size'] / 1024, 2);
            $item['new'] = empty($item['parent']);
            $tpl = $item['type'] == 'image'
                ? $tplImage
                : $tplFile;
            $files .= $Tickets2->getChunk($tpl, $item);
        }
    }
    $data['files'] = $Tickets2->getChunk($tplFiles, array(
        'files' => $files,
    ));
    /** @var modMediaSource $source */
    if ($source = $modx->getObject('sources.modMediaSource', array('id' => $source))) {
        $properties = $source->getPropertyList();
        $config = array(
            'size' => !empty($properties['maxUploadSize'])
                ? $properties['maxUploadSize']
                : 3145728,
            'height' => !empty($properties['maxUploadHeight'])
                ? $properties['maxUploadHeight']
                : 1080,
            'width' => !empty($properties['maxUploadWidth'])
                ? $properties['maxUploadWidth']
                : 1920,
            'extensions' => !empty($properties['allowedFileTypes'])
                ? $properties['allowedFileTypes']
                : 'jpg,jpeg,png,gif',
        );
        $modx->regClientStartupScript('<script type="text/javascript">Tickets2Config.source=' . json_encode($config) . ';</script>',
            true);
    }
    $modx->regClientScript($Tickets2->config['jsUrl'] . 'web/lib/plupload/plupload.full.min.js');
    $modx->regClientScript($Tickets2->config['jsUrl'] . 'web/files.js');

    $lang = $modx->getOption('cultureKey');
    if ($lang != 'en' && file_exists($Tickets2->config['jsPath'] . 'web/lib/plupload/i18n/' . $lang . '.js')) {
        $modx->regClientScript($Tickets2->config['jsUrl'] . 'web/lib/plupload/i18n/' . $lang . '.js');
    }
}

$output = $Tickets2->getChunk($tplWrapper, $data);
$key = md5(json_encode($Tickets2->config));
$_SESSION['TicketForm'][$key] = $Tickets2->config;
$output = str_ireplace('</form>',
    "\n<input type=\"hidden\" name=\"form_key\" value=\"{$key}\" class=\"disable-sisyphus\" />\n</form>", $output);

return $output;
