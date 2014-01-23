<?php

/*
 * This file is part of the GeocodableBehavior package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

/**
 * Tests for GeocodableBehavior class
 *
 * @author William Durand <william.durand1@gmail.com>
 */
class GeocodableBehaviorTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        if (!class_exists('GeocodedObject')) {
            $schema = <<<EOF
<database name="bookstore" defaultIdMethod="native">
    <table name="simple_geocoded_object">
        <column name="id" required="true" primaryKey="true" autoIncrement="true" type="INTEGER" />
        <behavior name="geocodable" />
    </table>

    <table name="geocoded_object">
        <column name="id" required="true" primaryKey="true" autoIncrement="true" type="INTEGER" />
        <column name="name" type="VARCHAR" size="100" primaryString="true" />
        <column name="street" type="VARCHAR" size="100" primaryString="true" />
        <column name="city" type="VARCHAR" size="100" primaryString="true" />
        <column name="country" type="VARCHAR" size="100" primaryString="true" />

        <behavior name="geocodable">
            <!-- IP -->
            <parameter name="geocode_ip" value="true" />
            <!-- Address -->
            <parameter name="geocode_address" value="true" />
            <parameter name="address_columns" value="street, city, country" />
            <!-- Geocoder -->
            <parameter name="geocoder_api_key" value="YOUR_API_KEY" />
        </behavior>
    </table>

    <table name="geocoded_object_no_autoupdate">
        <column name="id" required="true" primaryKey="true" autoIncrement="true" type="INTEGER" />
        <column name="name" type="VARCHAR" size="100" primaryString="true" />
        <column name="street" type="VARCHAR" size="100" primaryString="true" />
        <column name="city" type="VARCHAR" size="100" primaryString="true" />
        <column name="country" type="VARCHAR" size="100" primaryString="true" />

        <behavior name="geocodable">
            <parameter name="auto_update" value="false" />
            <!-- IP -->
            <parameter name="geocode_ip" value="true" />
            <!-- Address -->
            <parameter name="geocode_address" value="true" />
            <parameter name="address_columns" value="street, city, country" />
            <!-- Geocoder -->
            <parameter name="geocoder_api_key" value="YOUR_API_KEY" />
        </behavior>
    </table>

    <table name="geocoded_object_key_provider">
        <column name="id" required="true" primaryKey="true" autoIncrement="true" type="INTEGER" />
        <column name="name" type="VARCHAR" size="100" primaryString="true" />
        <column name="street" type="VARCHAR" size="100" primaryString="true" />
        <column name="city" type="VARCHAR" size="100" primaryString="true" />
        <column name="country" type="VARCHAR" size="100" primaryString="true" />

        <behavior name="geocodable">
            <!-- IP -->
            <parameter name="geocode_ip" value="true" />
            <!-- Address -->
            <parameter name="geocode_address" value="true" />
            <parameter name="address_columns" value="street, city, country" />
            <!-- Geocoder -->
            <parameter name="geocoder_api_key_provider" value="GeoApikeyProvider" />
        </behavior>
    </table>

    <table name="geocoded_object_key_provider_static">
        <column name="id" required="true" primaryKey="true" autoIncrement="true" type="INTEGER" />
        <column name="name" type="VARCHAR" size="100" primaryString="true" />
        <column name="street" type="VARCHAR" size="100" primaryString="true" />
        <column name="city" type="VARCHAR" size="100" primaryString="true" />
        <column name="country" type="VARCHAR" size="100" primaryString="true" />

        <behavior name="geocodable">
            <!-- IP -->
            <parameter name="geocode_ip" value="true" />
            <!-- Address -->
            <parameter name="geocode_address" value="true" />
            <parameter name="address_columns" value="street, city, country" />
            <!-- Geocoder -->
            <parameter name="geocoder_api_key_provider" value="GeoApikeyProvider::getKey()" />
        </behavior>
    </table>

    <table name="geocoded_object_key_provider_method">
        <column name="id" required="true" primaryKey="true" autoIncrement="true" type="INTEGER" />
        <column name="name" type="VARCHAR" size="100" primaryString="true" />
        <column name="street" type="VARCHAR" size="100" primaryString="true" />
        <column name="city" type="VARCHAR" size="100" primaryString="true" />
        <column name="country" type="VARCHAR" size="100" primaryString="true" />

        <behavior name="geocodable">
            <!-- IP -->
            <parameter name="geocode_ip" value="true" />
            <!-- Address -->
            <parameter name="geocode_address" value="true" />
            <parameter name="address_columns" value="street, city, country" />
            <!-- Geocoder -->
            <parameter name="geocoder_api_key_provider" value="GeoApikeyProvider->getApiKeyMethod()" />
        </behavior>
    </table>
</database>
EOF;
            $builder = new PropelQuickBuilder();
            $config  = $builder->getConfig();
            $config->setBuildProperty('behavior.geocodable.class', '../src/GeocodableBehavior');
            $builder->setConfig($config);
            $builder->setSchema($schema);

            $con = $builder->build();
            $con->sqliteCreateFunction('ACOS', 'acos', 1);
            $con->sqliteCreateFunction('COS', 'cos', 1);
            $con->sqliteCreateFunction('RADIANS', 'deg2rad', 1);
            $con->sqliteCreateFunction('SIN', 'sin', 1);
        }
    }

    public function testObjectMethods()
    {
        $this->assertTrue(method_exists('GeocodedObject', 'getLatitude'));
        $this->assertTrue(method_exists('GeocodedObject', 'getLongitude'));
        $this->assertTrue(method_exists('GeocodedObject', 'geocode'));
        $this->assertTrue(method_exists('GeocodedObject', 'isGeocoded'));
        $this->assertTrue(method_exists('GeocodedObject', 'isGeocodingNecessary'));
        $this->assertTrue(method_exists('GeocodedObject', 'getDistanceTo'));
        $this->assertTrue(method_exists('GeocodedObject', 'getCoordinates'));
        $this->assertTrue(method_exists('GeocodedObject', 'setCoordinates'));
    }

    public function testQueryMethods()
    {
        $this->assertTrue(method_exists('GeocodedObjectQuery', 'filterByDistanceFrom'));
        $this->assertTrue(method_exists('GeocodedObjectQuery', 'filterNear'));
    }

    public function testPeerConstants()
    {
        $this->assertTrue(defined('GeocodedObjectPeer::KILOMETERS_UNIT'));
        $this->assertTrue(defined('GeocodedObjectPeer::MILES_UNIT'));
        $this->assertTrue(defined('GeocodedObjectPeer::NAUTICAL_MILES_UNIT'));
    }

    public function testGetDistanceToInKilometers()
    {
        $geo1 = new GeocodedObject();
        $geo1->setLatitude(45.795463);
        $geo1->setLongitude(3.163237);

        $geo2 = new GeocodedObject();
        $geo2->setLatitude(45.77722154971201);
        $geo2->setLongitude(3.086986541748047);

        $dist = round($geo1->getDistanceTo($geo2), 2);
        $this->assertEquals(6.25, $dist);
    }

    public function testGetDistanceToInMiles()
    {
        $geo1 = new GeocodedObject();
        $geo1->setLatitude(45.795463);
        $geo1->setLongitude(3.163237);

        $geo2 = new GeocodedObject();
        $geo2->setLatitude(45.77722154971201);
        $geo2->setLongitude(3.086986541748047);

        $dist = round($geo1->getDistanceTo($geo2, GeocodedObjectPeer::MILES_UNIT), 2);
        $this->assertEquals(3.88, $dist);
    }

    public function testGetDistanceToInNauticalMiles()
    {
        $geo1 = new GeocodedObject();
        $geo1->setLatitude(45.795463);
        $geo1->setLongitude(3.163237);

        $geo2 = new GeocodedObject();
        $geo2->setLatitude(45.77722154971201);
        $geo2->setLongitude(3.086986541748047);

        $dist = round($geo1->getDistanceTo($geo2, GeocodedObjectPeer::NAUTICAL_MILES_UNIT), 2);
        $this->assertEquals(3.37, $dist);
    }

    public function testSetCoordinates()
    {
        $geo = new GeocodedObject();
        $geo->setCoordinates(1, 2);

        $this->assertEquals(1, $geo->getLatitude());
        $this->assertEquals(2, $geo->getLongitude());
    }

    public function testGetCoordinates()
    {
        $obj = new GeocodedObject();
        $obj->setCoordinates(1, 2);

        $this->assertEquals(array('latitude' => 1, 'longitude' => 2), $obj->getCoordinates());
    }

    public function testIsGeocoded()
    {
        $obj = new GeocodedObject();
        $this->assertFalse($obj->isGeocoded());

        $obj->setCoordinates(1, 2);
        $this->assertTrue($obj->isGeocoded());
    }

    public function testFilterByDistanceFromReturnsNoObjects()
    {
        GeocodedObjectPeer::doDeleteAll();

        $geo1 = new GeocodedObject();
        $geo1->setName('Aulnat Area');
        $geo1->setCity('Aulnat');
        $geo1->setCountry('France');
        $geo1->save();

        $geo2 = new GeocodedObject();
        $geo2->setName('Lyon Area');
        $geo2->setCity('Lyon');
        $geo2->setCountry('France');
        $geo2->save();

        $objects = GeocodedObjectQuery::create()
            ->filterByDistanceFrom($geo1->getLatitude(), $geo1->getLongitude(), 5)
            ->find()
            ;
        $this->assertEquals(1, count($objects));
    }

    public function testFilterByDistanceFromReturnsObjects()
    {
        GeocodedObjectPeer::doDeleteAll();

        $geo1 = new GeocodedObject();
        $geo1->setName('Aulnat Area');
        $geo1->setCity('Aulnat');
        $geo1->setCountry('France');
        $geo1->save();

        $geo2 = new GeocodedObject();
        $geo2->setName('Lyon Area');
        $geo2->setCity('Lyon');
        $geo2->setCountry('France');
        $geo2->save();

        $geo3 = new GeocodedObject();
        $geo3->setName('Lempdes Area');
        $geo3->setCity('Lempdes');
        $geo3->setCountry('France');
        $geo3->save();

        $objects = GeocodedObjectQuery::create()
            ->filterByDistanceFrom($geo1->getLatitude(), $geo1->getLongitude(), 20)
            ->find()
            ;
        $this->assertEquals(2, count($objects));
    }

    public function testGeocodeAddress()
    {
        $geo = new GeocodedObject();
        $geo->setStreet('10 avenue Gambetta');
        $geo->setCity('Paris');
        $geo->setCountry('France');
        $geo->save();

        $this->assertEquals(48.86321, $geo->getLatitude(), '', 0.001);
        $this->assertEquals(2.38882, $geo->getLongitude(), '', 0.001);
    }

    public function testGeocodeAddressWithUpdate()
    {
        $geo = new GeocodedObject();
        $geo->setStreet('10 avenue Gambetta');
        $geo->setCity('Paris');
        $geo->setCountry('France');
        $geo->save();

        $this->assertEquals(48.863217, $geo->getLatitude(), '', 0.001);
        $this->assertEquals(2.388821, $geo->getLongitude(), '', 0.001);

        $geo->setCity('Lyon');
        $geo->save();

        $this->assertEquals(45.8134, $geo->getLatitude(), '', 0.001);
        $this->assertEquals(4.8157, $geo->getLongitude(), '', 0.001);
    }

    public function testGeocodeAddressForceCoordinates()
    {
        $geo = new GeocodedObject();
        $geo->setStreet('10 avenue Gambetta');
        $geo->setCity('Paris');
        $geo->setCountry('France');
        $geo->save();

        $this->assertEquals(48.863217, $geo->getLatitude(), '', 0.001);
        $this->assertEquals(2.388821, $geo->getLongitude(), '', 0.001);

        // If we force the values, we should bypass the geocoding process
        $geo->setLatitude(48.85693);
        $geo->setLongitude(2.3412);
        $geo->save();

        $this->assertEquals(48.85693, $geo->getLatitude());
        $this->assertEquals(2.3412, $geo->getLongitude());
    }

    public function testNoFieldUpdate()
    {
        $geo = new GeocodedObject();
        $geo->setStreet('10 avenue Gambetta');
        $geo->setCity('Paris');
        $geo->setCountry('France');
        $geo->setLatitude(48.123456);
        $geo->setLongitude(2.3412);
        $geo->save();

        $this->assertEquals(48.123456, $geo->getLatitude());
        $this->assertEquals(2.3412, $geo->getLongitude());

        // If fields are unchanged, we should bypass the geocoding process
        $geo->setLongitude(2.123456);
        $geo->save();

        $this->assertEquals(48.123456, $geo->getLatitude());
        $this->assertEquals(2.123456, $geo->getLongitude());
    }

    public function testNoAutoUpdate()
    {
        $geo = new GeocodedObjectNoAutoUpdate();
        $geo->setStreet('10 avenue Gambetta');
        $geo->setCity('Paris');
        $geo->setCountry('France');
        $geo->save();
        $this->assertEquals(null, $geo->getLatitude());
        $this->assertEquals(null, $geo->getLongitude());
    }

    public function testKeyProvider()
    {
        $geo = new GeocodedObjectKeyProvider();
        $geo->setStreet('10 avenue Gambetta');
        $geo->setCity('Paris');
        $geo->setCountry('France');
        $geo->save();
        $this->assertEquals(48.863217, $geo->getLatitude(), '', 0.001);
        $this->assertEquals(2.388821, $geo->getLongitude(), '', 0.001);
    }

    public function testKeyProviderStatic()
    {
        $geo = new GeocodedObjectKeyProviderStatic();
        $geo->setStreet('10 avenue Gambetta');
        $geo->setCity('Paris');
        $geo->setCountry('France');
        $geo->save();
        $this->assertEquals(48.863217, $geo->getLatitude(), '', 0.001);
        $this->assertEquals(2.388821, $geo->getLongitude(), '', 0.001);
    }

    public function testKeyProviderMethod()
    {
        $geo = new GeocodedObjectKeyProviderMethod();
        $geo->setStreet('10 avenue Gambetta');
        $geo->setCity('Paris');
        $geo->setCountry('France');
        $geo->save();
        $this->assertEquals(48.863217, $geo->getLatitude(), '', 0.001);
        $this->assertEquals(2.388821, $geo->getLongitude(), '', 0.001);
    }

    public function testGeocode()
    {
        $geo = new GeocodedObject();
        $this->assertNull($geo->geocode(), 'The method returns null as there is nothing to geocode');
        $this->assertFalse($geo->isModified());

        $geo->setCity('Paris');
        $result = $geo->geocode();

        $this->assertTrue($geo->isModified());
        $this->assertInstanceOf('Geocoder\Result\ResultInterface', $result);
        $this->assertEquals('Paris', $result->getCity());
    }

    public function testGeocodeIsEffectLessIfGeocodingDisabled()
    {
        $geo = new SimpleGeocodedObject();

        $geo->geocode();
        $this->assertFalse($geo->isModified());
    }
}
