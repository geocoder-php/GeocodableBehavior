
if (!$this->isColumnModified(<?php echo $latitudeColumnConstant ?>) && !$this->isColumnModified(<?php echo $longitudeColumnConstant ?>)) {
    $this->geocode();
}
