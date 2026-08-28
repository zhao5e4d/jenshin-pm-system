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
    foreach($products as $productID => $product)
    {
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

    return $changes;
}

public function jxSaveProductArchive(int $productID): void
{
    if($productID <= 0) return;
    try
    {
        $this->loadModel('jxcore')->saveProductArchiveFromPost($productID);
    }
    catch(Throwable $e) {}
}
