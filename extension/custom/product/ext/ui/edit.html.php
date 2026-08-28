<?php
declare(strict_types=1);
/**
 * Overlay medical archive fields onto the original product edit form.
 */
namespace zin;

global $app;

$fields = useFields('product.edit');

include __DIR__ . '/jxarchive.field.php';
jxEnsureProductLineField($fields);
jxRemoveUnusedProductFields($fields);

$archive = null;
$product = data('product');
if($product && !empty($product->id))
{
    $archive = $app->control->loadModel('jxcore')->getProductArchive((int)$product->id);
}
jxAppendArchiveFields($fields, $archive);
jxApplyProductFormLayout($fields, false);

formGridPanel
(
    set::title($lang->product->edit),
    set::defaultMode('full'),
    set::modeSwitcher(false),
    set::fields($fields),
    on::change('[name=program]', 'toggleLineByProgram')
);
