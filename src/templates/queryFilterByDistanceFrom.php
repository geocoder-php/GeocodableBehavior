
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
    if (<?php echo $peerClassName ?>::MILES_UNIT === $unit) {
        $earthRadius = 3959;
    } elseif (<?php echo $peerClassName ?>::NAUTICAL_MILES_UNIT === $unit) {
        $earthRadius = 3440;
    } else {
        $earthRadius = 6371;
    }

    $sql = 'ABS(%s * ACOS(%s * COS(RADIANS(%s)) * COS(RADIANS(%s) - %s) + %s * SIN(RADIANS(%s))))';
    $preparedSql = sprintf($sql,
        $earthRadius,
        cos(deg2rad($latitude)),
        $this->getAliasedColName(<?php echo $latitudeColumnConstant ?>),
        $this->getAliasedColName(<?php echo $longitudeColumnConstant ?>),
        deg2rad($longitude),
        sin(deg2rad($latitude)),
        $this->getAliasedColName(<?php echo $latitudeColumnConstant ?>)
    );

    return $this
        ->withColumn($preparedSql, 'Distance')
        ->where(sprintf('%s %s ?', $preparedSql, $comparison), $distance, PDO::PARAM_STR)
        ;
}
