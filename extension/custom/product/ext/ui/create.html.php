<?php
declare(strict_types=1);
/**
 * Overlay medical archive fields onto the original product create form.
 */
namespace zin;

$fields = useFields('product.create');

$fields->autoLoad('program', 'line');

if(empty($config->setCode)) $fields->remove('code');

include __DIR__ . '/jxarchive.field.php';
jxEnsureProductLineField($fields, true);
jxRemoveUnusedProductFields($fields);
jxAppendArchiveFields($fields, data('jxArchive'));
jxApplyProductFormLayout($fields, true);

formGridPanel
(
    set::title($lang->product->create),
    set::defaultMode('full'),
    set::modeSwitcher(false),
    set::fields($fields),
    set::loadUrl($loadUrl),
    on::click('[name=newLine]', 'toggleLine(e.target)')
);
