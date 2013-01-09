
/**
 * Update geocode information.
 * You can extend this method to fill in other fields.
 *
 * @return \Geocoder\Result\ResultInterface|null
 */
public function geocode()
{
    // Do nothing as both 'geocode_ip', and 'geocode_address' are turned off.
    return null;
}
