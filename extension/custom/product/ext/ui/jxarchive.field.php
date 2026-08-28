<?php
declare(strict_types=1);
namespace zin;

if(!function_exists('zin\\jxEnsureProductLineField'))
{
    /**
     * Keep the original product-line picker (with "新建产品线") on create/edit.
     */
    function jxEnsureProductLineField($fields, bool $withNewLine = false)
    {
        global $lang;

        $lineField = $fields->get('line');
        if(is_null($lineField))
        {
            $fields->field('line')
                ->label($lang->product->line)
                ->control('inputGroup')
                ->width('1/2')
                ->items(false)
                ->item(field('line')->control('picker')->name('line')->items(data('fields.line.options'))->value(data('fields.line.default')))
                ->item(field('lineName')->control('input')->className('hidden')->name('lineName'));

            if($withNewLine && hasPriv('product', 'manageLine')) $fields->field('line')->checkbox(array('text' => $lang->product->newLine, 'name' => 'newLine'));
        }
        else
        {
            if($withNewLine && hasPriv('product', 'manageLine'))
            {
                $checkbox = $lineField->get('checkbox');
                if(empty($checkbox)) $fields->field('line')->checkbox(array('text' => $lang->product->newLine, 'name' => 'newLine'));
            }
        }

        $fields->field('line')->foldable(false)->hidden(false)->width('1/2');
    }
}

if(!function_exists('zin\\jxRemoveUnusedProductFields'))
{
    function jxRemoveUnusedProductFields($fields)
    {
        $fields->remove('reviewer,QD,RD');
    }
}

if(!function_exists('zin\\jxAppendArchiveFields'))
{
    function jxAppendArchiveFields($fields, $archive = null)
    {
        global $app, $lang;
        $app->loadLang('jxproduct');

        $val = function(string $key) use ($archive)
        {
            if(!$archive || !isset($archive->$key)) return '';
            return $archive->$key;
        };

        $fields->field('jxModel')
            ->label($lang->jxproduct->model)
            ->control('input')
            ->width('1/2')
            ->foldable(false)
            ->value($val('model'));
        $fields->field('jxCategory')
            ->label($lang->jxproduct->category)
            ->control('picker')
            ->items($lang->jxproduct->categoryList)
            ->width('1/2')
            ->foldable(false)
            ->value($val('category'));
        $fields->field('jxCertNo')
            ->label($lang->jxproduct->certNo)
            ->control('input')
            ->width('1/2')
            ->foldable(false)
            ->value($val('certNo'));
        $fields->field('jxCertValidTo')
            ->label($lang->jxproduct->certValidTo)
            ->control('date')
            ->width('1/2')
            ->foldable(false)
            ->value($val('certValidTo'));
        $fields->field('jxManufacturer')
            ->label($lang->jxproduct->manufacturer)
            ->control('input')
            ->width('1/2')
            ->foldable(false)
            ->value($val('manufacturer'));
        $fields->field('jxTenderCode')
            ->label($lang->jxproduct->tenderCode)
            ->control('input')
            ->width('1/2')
            ->foldable(false)
            ->value($val('tenderCode'));
        $fields->field('jxSpecs')
            ->label($lang->jxproduct->specs)
            ->control('input')
            ->width('full')
            ->foldable(false)
            ->wrapBefore()
            ->value($val('specs'));
        $fields->field('jxPatents')
            ->label($lang->jxproduct->patents)
            ->control('input')
            ->width('full')
            ->foldable(false)
            ->wrapBefore()
            ->value($val('patents'));
    }
}

if(!function_exists('zin\\jxApplyProductFormLayout'))
{
    /**
     * Always show the full form: two-column archive fields after name/code.
     */
    function jxApplyProductFormLayout($fields, bool $isCreate = false)
    {
        global $config;

        $hasCode    = !empty($config->setCode) && !is_null($fields->get('code'));
        $hasProgram = !is_null($fields->get('program'));
        $archiveSeq = 'jxModel,jxCategory,jxCertNo,jxCertValidTo,jxManufacturer,jxTenderCode,jxSpecs,jxPatents';

        if($hasProgram && $hasCode)
        {
            $fields->field('jxModel')->wrapBefore();
            $nameOrder = "name,code,{$archiveSeq}";
        }
        elseif($hasProgram)
        {
            $fields->field('jxModel')->wrapBefore(false);
            $nameOrder = "name,{$archiveSeq}";
        }
        elseif($hasCode)
        {
            $fields->field('jxModel')->wrapBefore(false);
            $nameOrder = "name,code,line,{$archiveSeq}";
        }
        else
        {
            $fields->field('jxModel')->wrapBefore();
            $nameOrder = "name,line,{$archiveSeq}";
        }

        if($isCreate)
        {
            $fields->orders($nameOrder);
            $fields->fullModeOrders($nameOrder);
            return;
        }

        $fields->orders($nameOrder, 'type,status');
        $fields->fullModeOrders($nameOrder, 'type,status');
    }
}
