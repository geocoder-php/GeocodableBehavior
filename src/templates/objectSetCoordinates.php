
/**
 * Convenient method to set latitude and longitude values.
 *
 * @param double $latitude     A latitude value.
 * @param double $longitude    A longitude value.
 */
public function setCoordinates($latitude, $longitude)
{
    $this-><?php echo $latitudeSetter ?>($latitude);
    $this-><?php echo $longitudeSetter ?>($longitude);
}
