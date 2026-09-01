<?php
try { $this->loadModel('jxcore')->ensureSchema(); } catch(Throwable $e) {}

if(function_exists('jxProductNameLink'))
{
    $jxNameLink = jxProductNameLink();
    if(isset($this->config->product->all->dtable->fieldList['name']))
    {
        $this->config->product->all->dtable->fieldList['name']['link'] = $jxNameLink;
    }
    if(isset($this->config->product->dtable->fieldList['name']))
    {
        $this->config->product->dtable->fieldList['name']['link'] = $jxNameLink;
    }
}
