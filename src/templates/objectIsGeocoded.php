
/**
 * Returns whether this object has been geocoded or not.
 *
 * @return Boolean
 */
public function isGeocoded()
{
    $lat = $this-><?php echo $latitudeGetter ?>();
    $lng = $this-><?php echo $longitudeGetter ?>();

    return (!empty($lat) && !empty($lng));
}
