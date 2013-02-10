/**
 * Filters objects near a given <?php echo $objectClassName ?> object.
 *
 * @param <?php echo $objectClassName ?> $<?php echo $variableName ?> A <?php echo $objectClassName ?> object.
 * @param double $distance The distance between the origin and the objects to find.
 * @param double $unit     The unit measure.
 *
 * @return <?php echo $queryClassName ?> The current query, for fluid interface
 */
public function filterNear(<?php echo $objectClassName ?> $<?php echo $variableName ?>, $distance = 5, $unit = <?php echo $defaultUnit ?>)
{
    return $this
        ->filterByDistanceFrom(
            $<?php echo $variableName ?>-><?php echo $latitudeColumnGetter ?>(),
            $<?php echo $variableName ?>-><?php echo $longitudeColumnGetter ?>(),
            $distance, $unit
        );
}
