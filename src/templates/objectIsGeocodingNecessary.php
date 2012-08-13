
/**
 * Check whether the current object is required to be geocoded (again).
 *
 * @return boolean
 */
public function isGeocodingNecessary()
{
    return !$this->isColumnModified(<?php echo $latitudeColumnConstant ?>) && !$this->isColumnModified(<?php echo $longitudeColumnConstant ?>);
}
