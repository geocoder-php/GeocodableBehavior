
/**
 * Returns an array with latitude and longitude values.
 *
 * @return array
 */
public function getCoordinates()
{
    return array(
        '<?php echo $latitudeColumn ?>'  => $this-><?php echo $latitudeGetter ?>(),
        '<?php echo $longitudeColumn ?>' => $this-><?php echo $longitudeGetter ?>()
    );
}
