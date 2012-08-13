
/**
 * Return a geocoder to be used to geocode the objects information.
 *
 * @return \Geocoder\GeocoderInterface
 */
public function getGeocoder()
{
<?php
/**
 * In case the $apiKeyProvider is set the $apiKey will contain "$provider->getApiKey()" or other executable code.
 *
 * Don't remove the "new";
 * for static methods the $apiKeyProvider is false, but the $apiKey contains executable code as well e.g. "GeoApiKeyProvider::getKey()".
 *
 * @see GeocodableBehavior::objectMethods()
 */
if ($apiKeyProvider) :  ?>
    $provider = new <?php echo $apiKeyProvider ?>;
<?php endif; ?>
    $geocoder = new \Geocoder\Geocoder(new <?php echo $geocoderProvider ?>(new <?php echo $geocoderAdapter ?>()<?php echo $apiKey ?>));

    return $geocoder;
}
