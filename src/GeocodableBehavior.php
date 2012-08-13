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
        'auto_update'               => 'true',
        'latitude_column'           => 'latitude',
        'longitude_column'          => 'longitude',
        // IP-based Geocoding
        'geocode_ip'                => 'false',
        'ip_column'                 => 'ip_address',
        // Address Geocoding
        'geocode_address'           => 'false',
        'address_columns'           => 'street,locality,region,postal_code,country',
        // Geocoder
        'geocoder_provider'         => '\Geocoder\Provider\YahooProvider',
        'geocoder_adapter'          => '\Geocoder\HttpAdapter\CurlHttpAdapter',
        'geocoder_api_key'          => 'false',
        'geocoder_api_key_provider' => 'false',
    );

    /**
     * @var GeocodableBehaviorQueryBuilderModifier
     */
    protected $queryBuilderModifier;

    /**
     * Add the latitude_column, longitude_column, ip_column to the current table
     */
    public function modifyTable()
    {
        if (!$this->getTable()->containsColumn($this->getParameter('latitude_column'))) {
            $this->getTable()->addColumn(array(
                'name' => $this->getParameter('latitude_column'),
                'type' => 'DOUBLE'
            ));
        }
        if (!$this->getTable()->containsColumn($this->getParameter('longitude_column'))) {
            $this->getTable()->addColumn(array(
                'name' => $this->getParameter('longitude_column'),
                'type' => 'DOUBLE'
            ));
        }
        if ('true' === $this->getParameter('geocode_ip') && !$this->getTable()->containsColumn($this->getParameter('ip_column'))) {
            $this->getTable()->addColumn(array(
                'name' => $this->getParameter('ip_column'),
                'type' => 'CHAR',
                'size' => 15
            ));
        }
    }

    public function staticAttributes($builder)
    {
        return $this->renderTemplate('staticAttributes');
    }

    public function preSave($builder)
    {
        if ('false' === $this->getParameter('auto_update')) {
            return '';
        }

        return $this->renderTemplate('objectPreSave');
    }

    public function objectMethods($builder)
    {
        $script     = '';
        $className  = $builder->getStubObjectBuilder()->getClassname();
        $objectName = strtolower($className);
        $peerName   = $builder->getStubPeerBuilder()->getClassname();

        $builder->declareClassFromBuilder($builder->getStubObjectBuilder());

        $script .= $this->renderTemplate('objectSetCoordinates', array(
            'latitudeSetter'  => $this->getColumnSetter('latitude_column'),
            'longitudeSetter' => $this->getColumnSetter('longitude_column'),
        ));

        $script .= $this->renderTemplate('objectGetCoordinates', array(
            'latitudeColumn'  => $this->getParameter('latitude_column'),
            'longitudeColumn' => $this->getParameter('longitude_column'),
            'latitudeGetter'  => $this->getColumnGetter('latitude_column'),
            'longitudeGetter' => $this->getColumnGetter('longitude_column'),
        ));

        $script .= $this->renderTemplate('objectIsGeocoded', array(
            'latitudeGetter'  => $this->getColumnGetter('latitude_column'),
            'longitudeGetter' => $this->getColumnGetter('longitude_column'),
        ));

        $script .= $this->renderTemplate('objectGetDistanceTo', array(
            'objectName'      => $objectName,
            'variableName'    => '$' . $objectName,
            'className'       => $className,
            'peerName'        => $peerName,
            'latitudeGetter'  => $this->getColumnGetter('latitude_column'),
            'longitudeGetter' => $this->getColumnGetter('longitude_column'),
        ));

        if ('true' === $this->getParameter('geocode_ip') || 'true' === $this->getParameter('geocode_address')) {
            $apiKey = '';
            $apiKeyProvider = false;

            if ('false' !== $this->getParameter('geocoder_api_key')) {
                $apiKey = sprintf(', \'%s\'', $this->getParameter('geocoder_api_key'));
            } elseif ('false' !== $this->getParameter('geocoder_api_key_provider')) {
                $apiKeyProvider = $this->getParameter('geocoder_api_key_provider');

                if (false === strpos($apiKeyProvider, '::')) {
                    if (false === strpos($apiKeyProvider, '->')) {
                        $builder->declareClass($apiKeyProvider);

                        $apiKey = ', $provider->getApiKey()';
                    } else {
                        list($class, $method) = explode('->', $apiKeyProvider, 2);
                        $builder->declareClass($class);

                        $apiKeyProvider = $class . '()';
                        $apiKey         = ', $provider->' . $method;
                    }
                } else {
                    $class  = substr($apiKeyProvider, 0, strpos($apiKeyProvider, '::'));
                    $builder->declareClass($class);

                    $apiKey = ', ' . $apiKeyProvider;
                    $apiKeyProvider = false;
                }
            }

            $columns = array();
            foreach (explode(',', $this->getParameter('address_columns')) as $col) {
                if ($column = $this->getTable()->getColumn(trim($col))) {
                    $columns[$column->getConstantName()] = $column->getPhpName();
                }
            }

            $templateOptions = array(
                'apiKeyProvider'    => $apiKeyProvider,
                'apiKey'            => $apiKey,
                'columns'           => $columns,
                'geocodeIp'         => 'true' === $this->getParameter('geocode_ip'),
                'geocodeAddress'    => 'true' === $this->getParameter('geocode_address') && '' !== $this->getParameter('address_columns'),
                'ipColumnConstant'  => 'true' === $this->getParameter('geocode_ip') ? $this->getColumnConstant('ip_column', $builder) : '',
                'ipColumnGetter'    => 'true' === $this->getParameter('geocode_ip') ? $this->getColumnGetter('ip_column') : '',
                'geocoderProvider'  => $this->getParameter('geocoder_provider'),
                'geocoderAdapter'   => $this->getParameter('geocoder_adapter'),
                'latitudeSetter'    => $this->getColumnSetter('latitude_column'),
                'longitudeSetter'   => $this->getColumnSetter('longitude_column'),
                'longitudeColumnConstant'   => $this->getColumnConstant('longitude_column', $builder),
                'latitudeColumnConstant'    => $this->getColumnConstant('latitude_column', $builder),
            );

            $script .= $this->renderTemplate('objectGetGeocoder', $templateOptions);
            $script .= $this->renderTemplate('objectGeocode', $templateOptions);
            if ($templateOptions['geocodeAddress']) {
                $script .= $this->renderTemplate('objectGetAddressParts', $templateOptions);
                $script .= $this->renderTemplate('objectHasAddressChanged', $templateOptions);
            }
            $script .= $this->renderTemplate('objectIsGeocodingNecessary', $templateOptions);
        } else {
            $script .= $this->renderTemplate('objectGeocodeEmpty');
        }

        return $script;
    }

    /**
     * {@inheritdoc}
     */
    public function getQueryBuilderModifier()
    {
        if (null === $this->queryBuilderModifier) {
            $this->queryBuilderModifier = new GeocodableBehaviorQueryBuilderModifier($this);
        }

        return $this->queryBuilderModifier;
    }

    /**
     * Get the setter of one of the columns of the behavior
     *
     * @param  string $column One of the behavior colums, 'latitude_column', 'longitude_column', or 'ip_column'
     * @return string The related setter, 'setLatitude', 'setLongitude', 'setIpAddress'
     */
    protected function getColumnSetter($column)
    {
        return 'set' . $this->getColumnForParameter($column)->getPhpName();
    }

    /**
     * Get the getter of one of the columns of the behavior
     *
     * @param  string $column One of the behavior colums, 'latitude_column', 'longitude_column', or 'ip_column'
     * @return string The related getter, 'getLatitude', 'getLongitude', 'getIpAddress'
     */
    public function getColumnGetter($column)
    {
        return 'get' . $this->getColumnForParameter($column)->getPhpName();
    }

    public function getColumnConstant($columnName, $builder)
    {
        return $builder->getColumnConstant($this->getColumnForParameter($columnName));
    }
}
