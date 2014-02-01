
/**
 * Filters objects by distance from a given origin.
 *
 * @param double $latitude       The latitude of the origin point.
 * @param double $longitude      The longitude of the origin point.
 * @param double $distance       The distance between the origin and the objects to find.
 * @param double $unit           The unit measure.
 * @param Criteria $comparison   Comparison sign (default is: `<`).
 *
 * @return <?php echo $queryClassName ?> The current query, for fluid interface
 */
public function filterByDistanceFrom($latitude, $longitude, $distance, $unit = <?php echo $defaultUnit ?>, $comparison = Criteria::LESS_THAN)
{
    return $this
        ->withDistance($latitude, $longitude, $unit)
        ->where(sprintf('Distance %s ?', $comparison), $distance, PDO::PARAM_STR)
        ;
}
