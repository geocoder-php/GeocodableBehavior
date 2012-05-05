
/**
 * Update geocode information.
 * You can extend this method to fill in other fields.
 *
 * @return \Geocoder\geocodedResult\geocodedResultInterface|null
 */
public function geocode()
{
    $geocodedResult   = null;
<?php if ($geocodeAddress) : ?>
    $address_parts    = array();
    $address_modified = false;

<?php foreach ($columns as $constantName => $phpName) : ?>
    // <?php echo $phpName . "\n" ?>
    $address_modified = $address_modified || $this->isColumnModified(<?php echo $constantName ?>);
    $address_parts['<?php echo strtolower($phpName) ?>'] = $this->get<?php echo ucfirst($phpName) ?>();

<?php endforeach; ?>
<?php endif; ?>
<?php if ($apiKeyProvider) : ?>
    $provider = new <?php echo $apiKeyProvider ?>;
<?php endif; ?>
    $geocoder = new \Geocoder\Geocoder(new <?php echo $geocoderProvider ?>(new <?php echo $geocoderAdapter ?>()<?php echo $apiKey ?>));

<?php if ($geocodeIp) : ?>
    if ($this->isColumnModified(<?php echo $ipColumnConstant ?>)) {
        $geocodedResult = $geocoder->geocode($this-><?php echo $ipColumnGetter ?>());
    }

<?php endif; ?>
<?php if ($geocodeAddress) : ?>
    if ($address = join(',', array_filter($address_parts))) {
        $geocodedResult = $geocoder->geocode($address);
    }

<?php endif; ?>
    if (null !== $geocodedResult && $coordinates = $geocodedResult->getCoordinates()) {
        $this-><?php echo $latitudeSetter ?>($coordinates[0]);
        $this-><?php echo $longitudeSetter ?>($coordinates[1]);
    }

    return $geocodedResult;
}
