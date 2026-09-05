<?php
/**
 * Persist medical archive fields after the original product is saved.
 * Do not hook productZen::responseAfterCreate/Edit: those methods call
 * private locate helpers, and zen hooks redeclare the method on a child class.
 */
public function create(object $product, string $lineName = ''): int|false
{
    $productID = parent::create($product, $lineName);
    if($productID) $this->jxSaveProductArchive((int)$productID);
    return $productID;
}

public function update(int $productID, object $product): array|false
{
    $changes = parent::update($productID, $product);
    if($changes !== false) $this->jxSaveProductArchive($productID);
    return $changes;
}

public function batchUpdate(array $products): array
{
    $postedLines = (isset($_POST['line']) && is_array($_POST['line'])) ? $_POST['line'] : null;
    $archives    = array();
    foreach($products as $productID => $product)
    {
        $patch = array();
        if(isset($product->jxModel))       $patch['model']       = $product->jxModel;
        if(isset($product->jxCategory))    $patch['category']    = $product->jxCategory;
        if(isset($product->jxCertNo))      $patch['certNo']      = $product->jxCertNo;
        if(isset($product->jxCertValidTo)) $patch['certValidTo'] = $product->jxCertValidTo;
        if($patch) $archives[(int)$productID] = $patch;
        unset($product->jxCategory, $product->jxCertNo, $product->jxCertValidTo, $product->jxModel);

        if($postedLines === null || !array_key_exists($productID, $postedLines) || $postedLines[$productID] === '' || $postedLines[$productID] === null || (int)$postedLines[$productID] === 0)
        {
            unset($product->line);
            continue;
        }
        $product->line = (int)$postedLines[$productID];
    }

    $changes = parent::batchUpdate($products);

    /* form::batchData defaults missing line to 0 and would wipe 所属产品线; only persist a real line id. */
    if(is_array($postedLines))
    {
        foreach($postedLines as $productID => $line)
        {
            if($line === '' || $line === null || (int)$line === 0) continue;
            $this->dao->update(TABLE_PRODUCT)->set('line')->eq((int)$line)->where('id')->eq((int)$productID)->exec();
        }
    }

    if($archives)
    {
        $jxcore = $this->loadModel('jxcore');
        foreach($archives as $productID => $patch)
        {
            try
            {
                $jxcore->saveProductArchivePatch((int)$productID, $patch);
            }
            catch(Throwable $e)
            {
                $this->app->loadLang('jxproduct');
                dao::$errors['jxArchive'][] = !empty($this->lang->jxproduct->errorSaveArchive)
                    ? $this->lang->jxproduct->errorSaveArchive
                    : $e->getMessage();
            }
        }
    }

    return $changes;
}

public function getStats(array $productIdList, string $orderBy = '`order`_asc', ?object $pager = null, string $storyType = 'story', int $programID = 0): array
{
    $stats = parent::getStats($productIdList, $orderBy, $pager, $storyType, $programID);
    $this->jxAttachArchives($stats);
    return $stats;
}

public function getByIdList(array $productIdList): array
{
    $products = parent::getByIdList($productIdList);
    $this->jxAttachArchives($products);
    return $products;
}

public function formatDataForList(object $product, array $users): object
{
    $product = parent::formatDataForList($product, $users);
    if(isset($product->jxCertValidTo) && $product->jxCertValidTo !== '' && $product->jxCertValidTo !== null && strpos((string)$product->jxCertValidTo, '<') === false)
    {
        $product->jxCertValidTo = $this->jxFormatCertValidTo((string)$product->jxCertValidTo);
    }
    return $product;
}

public function setMenu(int $productID = 0, string|int $branch = '', string $extra = ''): bool
{
    $result = parent::setMenu($productID, $branch, $extra);
    if(function_exists('jxHideProductMenus')) jxHideProductMenus($this->lang);
    return $result;
}

public function jxSaveProductArchive(int $productID): void
{
    if($productID <= 0) return;
    try
    {
        $this->loadModel('jxcore')->saveProductArchiveFromPost($productID);
    }
    catch(Throwable $e)
    {
        $this->app->loadLang('jxproduct');
        dao::$errors['jxArchive'][] = !empty($this->lang->jxproduct->errorSaveArchive)
            ? $this->lang->jxproduct->errorSaveArchive
            : $e->getMessage();
        return;
    }
}

public function jxAttachArchives(array $products): void
{
    if(empty($products)) return;
    $archives = $this->loadModel('jxcore')->getProductArchives(array_keys($products));
    foreach($products as $productID => $product)
    {
        $archive = $archives[$productID] ?? $archives[(int)$productID] ?? null;
        $product->jxModel       = $archive->model ?? '';
        $product->jxCategory    = $archive->category ?? '';
        $product->jxCertNo      = $archive->certNo ?? '';
        $product->jxCertValidTo = $archive->certValidTo ?? '';
    }
}

public function jxFormatCertValidTo(string $date): string
{
    $date = trim($date);
    if($date === '' || $date === '0000-00-00') return '';
    $this->app->loadLang('jxproduct');
    $today    = helper::today();
    $warnDays = (int)($this->config->jenshin->certWarnDays ?? 90);
    $warnDay  = date('Y-m-d', strtotime("+{$warnDays} days"));
    $label    = $date;
    $class    = '';
    if($date < $today)
    {
        $class = 'text-danger';
        $label = $date . ' ' . ($this->lang->jxproduct->certExpired ?? '');
    }
    elseif($date <= $warnDay)
    {
        $class = 'text-warning';
        $label = $date . ' ' . ($this->lang->jxproduct->certExpiring ?? '');
    }
    return $class === '' ? $date : "<span class='{$class}'>{$label}</span>";
}
