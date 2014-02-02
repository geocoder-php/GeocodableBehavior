
/**
 * Adds distance from a given origin column to query.
 *
 * @param double $latitude       The latitude of the origin point.
 * @param double $longitude      The longitude of the origin point.
 * @param double $unit           The unit measure.
 *
 * @return <?php echo $queryClassName ?> The current query, for fluid interface
 */
public function withDistance($latitude, $longitude, $unit = <?php echo $defaultUnit ?>)
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
        ->withColumn($preparedSql, 'Distance');
}
