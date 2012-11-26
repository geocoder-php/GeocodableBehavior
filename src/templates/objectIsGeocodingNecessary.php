
/**
 * Check whether the current object is required to be geocoded (again).
 *
 * @return boolean
 */
public function isGeocodingNecessary()
{
<?php if ($geocodeIp || $geocodeAddress): ?>
    return !$this->isColumnModified(<?php echo $latitudeColumnConstant ?>) && !$this->isColumnModified(<?php echo $longitudeColumnConstant ?>);
<?php else: ?>
    return false;
<?php endif; ?>
}
