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
        'auto_update'           => 'true',
        'latitude_column'       => 'latitude',
        'longitude_column'      => 'longitude',
        // IP-based Geocoding
        'geocode_ip'            => 'false',
        'ip_column'             => 'ip_address',
        // Address Geocoding
        'geocode_address'       => 'false',
        'address_columns'       => 'street,locality,region,postal_code,country',
        // Geocoder
        'geocoder_provider'     => '\Geocoder\Provider\YahooProvider',
        'geocoder_adapter'      => '\Geocoder\HttpAdapter\CurlHttpAdapter',
        'geocoder_api_key'      => 'false',
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
        if('true' === $this->getParameter('geocode_ip') && !$this->getTable()->containsColumn($this->getParameter('ip_column'))) {
            $this->getTable()->addColumn(array(
                'name' => $this->getParameter('ip_column'),
                'type' => 'CHAR',
                'size' => 15
            ));
        }
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
        if ('false' === $this->getParameter('auto_update')) {
          return "";
        }
        $script = "if (!\$this->isColumnModified(" . $this->getColumnConstant('latitude_column', $builder) . ") && !\$this->isColumnModified(" . $this->getColumnConstant('longitude_column', $builder) . ")) {
    \$this->geocode();
}
";

        return $script;
    }

    public function objectMethods($builder)
    {
        $className = $builder->getStubObjectBuilder()->getClassname();
        $objectName = strtolower($className);
        $peerName = $builder->getStubPeerBuilder()->getClassname();

        $script = "/**
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
 * Returns whether this object has been geocoded or not.
 *
 * @return Boolean
 */
public function isGeocoded()
{
    \$lat = \$this->{$this->getColumnGetter('latitude_column')}();
    \$lng = \$this->{$this->getColumnGetter('longitude_column')}();

    return (!empty(\$lat) && !empty(\$lng));
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
        $script .= "
/**
 * update geocode information
 */
public function geocode()
{
";

        $apiKey = '';
        if ('false' !== $this->getParameter('geocoder_api_key')) {
            $apiKey = sprintf(', \'%s\'', $this->getParameter('geocoder_api_key'));
        }
        $script .= "    \$geocoder = new \Geocoder\Geocoder(new {$this->getParameter('geocoder_provider')}(new {$this->getParameter('geocoder_adapter')}()$apiKey));
";

        if ('true' === $this->getParameter('geocode_ip')) {
            $isModifiedIpStr = sprintf('$this->isColumnModified(%s)', $this->getColumnConstant('ip_column', $builder));
            $script .= "    if($isModifiedIpStr) {
      \$result = \$geocoder->geocode(\$this->{$this->getColumnGetter('ip_column')}());
    }
";
        }

        if ('true' === $this->getParameter('geocode_address') && '' !== $this->getParameter('address_columns')) {
            $script .= "    \$address_parts = array();
    \$address_modified = false;
";
            $table = $this->getTable();
            $address = '';
            foreach (explode(',', $this->getParameter('address_columns')) as $col) {
                if ($column = $table->getColumn(trim($col))) {
                    $isModifiedColStr = sprintf('$this->isColumnModified(%s)', $column->getConstantName());
                    $getColStr = sprintf('$this->get%s()', ucfirst($column->getPhpName()));
                    $script .= "    \$address_modified = \$address_modified || $isModifiedColStr;
    \$address_parts['{$column->getPhpName()}'] = $getColStr;
";
                }
            }
            $script .= "    \$address = join(',', array_filter(\$address_parts));
    if (\$address_modified) {
        \$result = \$geocoder->geocode(\$address);
    }
";
        }

        $script .= "    if (isset(\$result) && \$coordinates = \$result->getCoordinates()) {
        \$this->{$this->getColumnSetter('latitude_column')}(\$coordinates[0]);
        \$this->{$this->getColumnSetter('longitude_column')}(\$coordinates[1]);
    }
";
        $script .= "
}
";
        return $script;
    }

    public function queryMethods($builder)
    {
        $table = $this->getTable();
        foreach ($table->getColumns() as $col)
        {
          if ($col->isPrimaryKey())
          {
            $pks[] = "\$this->getModelAliasOrName().'.".$col->getPhpName()."'";
          }
        }

        $builder->declareClass('Criteria', 'PDO');

        $queryClassName = $builder->getStubQueryBuilder()->getClassname();
        $peerName = $builder->getStubPeerBuilder()->getClassname();

        return  "/**
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
    if ($peerName::MILES_UNIT === \$unit) {
        \$earthRadius = 3959;
    } elseif ($peerName::NAUTICAL_MILES_UNIT === \$unit) {
        \$earthRadius = 3440;
    } else {
        \$earthRadius = 6371;
    }

    \$sql = 'ABS(%s * ACOS(%s * COS(RADIANS(%s)) * COS(RADIANS(%s) - %s) + %s * SIN(RADIANS(%s))))';
    \$preparedSql = sprintf(\$sql,
        \$earthRadius,
        cos(deg2rad(\$latitude)),
        \$this->getAliasedColName({$this->getColumnConstant('latitude_column', $builder)}),
        \$this->getAliasedColName({$this->getColumnConstant('longitude_column', $builder)}),
        deg2rad(\$longitude),
        sin(deg2rad(\$latitude)),
        \$this->getAliasedColName({$this->getColumnConstant('latitude_column', $builder)})
    );

    return \$this
        ->withColumn(\$preparedSql, 'Distance')
        ->where(sprintf('%s %s ?', \$preparedSql, \$comparison), \$distance, PDO::PARAM_STR)
        ;
}
";
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
}
