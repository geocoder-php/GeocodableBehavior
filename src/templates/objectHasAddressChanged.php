
/**
 * Check whether the address of this object has changed.
 *
 * @return boolean
 */
public function hasAddressChanged()
{
    $changed = false;
<?php foreach ($columns as $constantName => $phpName) : ?>
    // <?php echo $phpName . "\n" ?>
    $changed = $changed || $this->isColumnModified(<?php echo $constantName ?>);
<?php endforeach; ?>

    return $changed;
}
