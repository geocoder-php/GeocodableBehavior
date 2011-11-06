<?php

/**
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

/**
 * @author     William Durand <william.durand1@gmail.com>
 * @package    propel.generator.behavior
 */
class GeocodableBehavior extends Behavior
{
    // default parameters value
    protected $parameters = array(
        // Base
        'latitude_column'   => 'latitude',
        'longitude_column'  => 'longitude',
        // IP-based Geocoding
        'geocode_ip'        => 'false',
        'ip_column'         => 'ip_address',
        'ipinfodb_api_key'  => '',
        // Address Geocoding
        'geocode_address'   => 'false',
        'address_columns'   => 'street,locality,region,postal_code,country',
        'yahoo_api_key'     => ''
    );

    /**
     * Add the latitude_column, longitude_column, ip_column to the current table
     */
    public function modifyTable()
    {
        if(!$this->getTable()->containsColumn($this->getParameter('latitude_column'))) {
            $this->getTable()->addColumn(array(
                'name' => $this->getParameter('latitude_column'),
                'type' => 'DOUBLE'
            ));
        }
        if(!$this->getTable()->containsColumn($this->getParameter('longitude_column'))) {
            $this->getTable()->addColumn(array(
                'name' => $this->getParameter('longitude_column'),
                'type' => 'DOUBLE'
            ));
        }
        if('true' === $this->getParameter('geocode_ip') && !$this->getTable()->containsColumn($this->getParameter('ip_address'))) {
            $this->getTable()->addColumn(array(
                'name' => $this->getParameter('ip_column'),
                'type' => 'CHAR',
                'size' => 15
            ));
        }
    }

    /**
     * Get the setter of one of the columns of the behavior
     *
     * @param     string $column One of the behavior colums, 'latitude_column', 'longitude_column', or 'ip_column'
     * @return    string The related setter, 'setLatitude', 'setLongitude', 'setIpAddress'
     */
    protected function getColumnSetter($column)
    {
        return 'set' . $this->getColumnForParameter($column)->getPhpName();
    }

    /**
     * Get the getter of one of the columns of the behavior
     *
     * @param     string $column One of the behavior colums, 'latitude_column', 'longitude_column', or 'ip_column'
     * @return    string The related getter, 'getLatitude', 'getLongitude', 'getIpAddress'
     */
    protected function getColumnGetter($column)
    {
        return 'get' . $this->getColumnForParameter($column)->getPhpName();
    }

    protected function getColumnConstant($columnName, $builder)
    {
        return $builder->getColumnConstant($this->getColumnForParameter($columnName));
    }

    public function staticAttributes($builder)
    {
		return "/**
 * Kilometers unit
 */
const KILOMETERS_UNIT = 1.609344;
/**
 * Miles unit
 */
const MILES_UNIT = 1.1515;
/**
 * Nautical miles unit
 */
const NAUTICAL_MILES_UNIT = 0.8684;
";
    }

    public function preSave($builder)
    {
        $script = '';
        if ('true' === $this->getParameter('geocode_ip')) {
            $script .= "\$queryUrl = sprintf('http://api.ipinfodb.com/v3/ip-city/?key={$this->getParameter('ipinfodb_api_key')}&format=json&ip=%s', \$this->{$this->getColumnGetter('ip_column')}());
\$json = file_get_contents(\$queryUrl);
if (false !== \$json) {
    \$data = json_decode(\$json);
    if (\$data->longitude && \$data->latitude) {
        \$this->{$this->getColumnSetter('longitude_column')}(\$data->longitude);
        \$this->{$this->getColumnSetter('latitude_column')}(\$data->latitude);
    }
}";
        }

        if ('true' === $this->getParameter('geocode_address') && '' !== $this->getParameter('address_columns')) {
            $table = $this->getTable();
            $address = '';
            foreach (explode(',', $this->getParameter('address_columns')) as $col) {
                if ($column = $table->getColumn(trim($col))) {
                    $getColStr = sprintf('$this->get%s()', ucfirst($column->getPhpName()));
                    $address .= ('' === $address) ? $getColStr : ".','.$getColStr";
                }
            }
            $script .= "\$queryUrl = sprintf('http://where.yahooapis.com/geocode?q=%s&flags=CJ&appid={$this->getParameter('yahoo_api_key')}', urlencode($address));
\$json = file_get_contents(\$queryUrl);
if (false !== \$json) {
    \$data = json_decode(\$json)->ResultSet->Results[0];
    if (\$data->longitude && \$data->latitude) {
        \$this->{$this->getColumnSetter('longitude_column')}(\$data->longitude);
        \$this->{$this->getColumnSetter('latitude_column')}(\$data->latitude);
    }
}";
        }

        return $script;
    }

    public function objectMethods($builder)
    {
        $className = $builder->getStubObjectBuilder()->getClassname();
        $objectName = strtolower($className);
        $peerName = $builder->getStubPeerBuilder()->getClassname();

        return "/**
 * Convenient method to set latitude and longitude values.
 *
 * @param double \$latitude     A latitude value.
 * @param double \$longitude    A longitude value.
 */
public function setCoordinates(\$latitude, \$longitude)
{
    \$this->{$this->getColumnSetter('latitude_column')}(\$latitude);
    \$this->{$this->getColumnSetter('longitude_column')}(\$longitude);
}

/**
 * Returns an array with latitude and longitude values.
 *
 * @return array
 */
public function getCoordinates()
{
    return array(
        '{$this->getParameter('latitude_column')}' => \$this->{$this->getColumnGetter('latitude_column')}(),
        '{$this->getParameter('longitude_column')}' => \$this->{$this->getColumnGetter('longitude_column')}()
    );
}

/**
 * Calculates the distance between a given $objectName and this one.
 *
 * @param $className \${$objectName}    A $className object.
 * @param \$unit    The unit measure.
 *
 * @return double   The distance between the two objects.
 */
public function getDistanceTo($className \${$objectName}, \$unit = $peerName::KILOMETERS_UNIT)
{
    \$dist = rad2deg(acos(sin(deg2rad(\$this->{$this->getColumnGetter('latitude_column')}())) * sin(deg2rad(\${$objectName}->{$this->getColumnGetter('latitude_column')}())) +  cos(deg2rad(\$this->{$this->getColumnGetter('latitude_column')}())) * cos(deg2rad(\${$objectName}->{$this->getColumnGetter('latitude_column')}())) * cos(deg2rad(\$this->{$this->getColumnGetter('longitude_column')}() - \${$objectName}->{$this->getColumnGetter('longitude_column')}())))) * 60 * $peerName::MILES_UNIT;

    if ($peerName::MILES_UNIT === \$unit) {
        return \$dist;
    } else if ($peerName::NAUTICAL_MILES_UNIT === \$unit) {
        return \$dist * $peerName::NAUTICAL_MILES_UNIT;
    }

    return \$dist * $peerName::KILOMETERS_UNIT;
}
";
    }

    public function queryMethods($builder)
    {
        $builder->declareClass('Criteria');

        $queryClassName = $builder->getStubQueryBuilder()->getClassname();
        $peerName = $builder->getStubPeerBuilder()->getClassname();

        return "
/**
 * Filters objects by distance from a given origin.
 *
 * @param	double \$latitude       The latitude of the origin point.
 * @param	double \$longitude      The longitude of the origin point.
 * @param	double \$distance       The distance between the origin and the objects to find.
 * @param	\$unit                  The unit measure.
 * @param	Criteria \$comparison   Comparison sign (default is: `<`).
 *
 * @return	$queryClassName The current query, for fluid interface
 */
public function filterByDistanceFrom(\$latitude, \$longitude, \$distance, \$unit = $peerName::KILOMETERS_UNIT, \$comparison = Criteria::LESS_THAN)
{
    \$sql = '(%s * 60 * ACOS(COS(RADIANS(%s)) * COS(RADIANS(%s)) * COS(RADIANS(%s) - RADIANS(%s)) + SIN(RADIANS(%s)) * SIN(RADIANS(%s))))';
    \$preparedSql = sprintf(\$sql,
        $peerName::MILES_UNIT,
        \$latitude,
        \$this->getAliasedColName({$this->getColumnConstant('latitude_column', $builder)}),
        \$this->getAliasedColName({$this->getColumnConstant('longitude_column', $builder)}),
        \$this->getAliasedColName({$this->getColumnConstant('latitude_column', $builder)}),
        \$longitude,
        \$latitude,
        \$comparison
    );

    return \$this
        ->withColumn(\$preparedSql, 'Distance')
        ->having(sprintf('Distance %s %s', \$comparison, \$distance))
        ;
}
";
    }
}
