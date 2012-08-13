
/**
 * Update geocode information.
 *
 * @return \Geocoder\Result\ResultInterface|null
 */
public function geocode()
{
    $geocodedResult = null;
    $geocoder = $this->getGeocoder();

<?php if ($geocodeIp) : ?>
    if ($this->isColumnModified(<?php echo $ipColumnConstant ?>)) {
        $geocodedResult = $geocoder->geocode($this-><?php echo $ipColumnGetter ?>());
    }

<?php endif; ?>
<?php if ($geocodeAddress) : ?>
    if ($this->hasAddressChanged() && $address = join(',', array_filter($this->getAddressParts()))) {
        $geocodedResult = $geocoder->geocode($address);
    }

<?php endif; ?>
    if (null !== $geocodedResult && $coordinates = $geocodedResult->getCoordinates()) {
        $this->setCoordinates($coordinates[0], $coordinates[1]);
    }

    return $geocodedResult;
}
