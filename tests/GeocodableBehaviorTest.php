<?php

/*
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

require_once dirname(__FILE__) . '/../../../tools/helpers/bookstore/BookstoreTestBase.php';

set_include_path(implode(PATH_SEPARATOR, array(
    get_include_path(),
    __DIR__.'/../../../vendor/',
    __DIR__.'/../../../vendor/Buzz/lib/',
    __DIR__.'/../../../vendor/Geocoder/src/'
)));

/**
 * Tests for GeocodableBehavior class
 *
 * @author     William Durand <william.durand1@gmail.com>
 * @package    generator.behavior
 */
class GeocodableBehaviorTest extends BookstoreTestBase
{
    public function setUp()
    {
        require_once __DIR__.'/../../../vendor/Geocoder/src/autoload.php';

        parent::setUp();
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
        $this->assertEquals(0, count($objects));
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
        $this->assertEquals(1, count($objects));
    }

    public function testGeocodeIp()
    {
        $geo = new GeocodedObject();
        $geo->setIpAddress('74.200.247.59');
        $geo->save();

        $this->assertEquals(33.036711, $geo->getLatitude());
        $this->assertEquals(-96.813541, $geo->getLongitude());
    }

    public function testGeocodeAddress()
    {
        $geo = new GeocodedObject();
        $geo->setStreet('10 avenue Gambetta');
        $geo->setCity('Paris');
        $geo->setCountry('France');
        $geo->save();

        $this->assertEquals(48.863217, $geo->getLatitude());
        $this->assertEquals(2.388821, $geo->getLongitude());
    }

    public function testGeocodeAddressWithUpdate()
    {
        $geo = new GeocodedObject();
        $geo->setStreet('10 avenue Gambetta');
        $geo->setCity('Paris');
        $geo->setCountry('France');
        $geo->save();

        $this->assertEquals(48.863217, $geo->getLatitude());
        $this->assertEquals(2.388821, $geo->getLongitude());

        $geo->setStreet('1 avenue Léon Maniez');
        $geo->save();

        $this->assertEquals(48.85693, $geo->getLatitude());
        $this->assertEquals(2.3412, $geo->getLongitude());
    }

    public function testGeocodeAddressForceCoordinates()
    {
        $geo = new GeocodedObject();
        $geo->setStreet('10 avenue Gambetta');
        $geo->setCity('Paris');
        $geo->setCountry('France');
        $geo->save();

        $this->assertEquals(48.863217, $geo->getLatitude());
        $this->assertEquals(2.388821, $geo->getLongitude());

        // If we force the values, we should by pass the geocoding process
        $geo->setLatitude(48.85693);
        $geo->setLongitude(2.3412);
        $geo->save();

        $this->assertEquals(48.85693, $geo->getLatitude());
        $this->assertEquals(2.3412, $geo->getLongitude());
    }
}
