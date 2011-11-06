<?php

/*
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

require_once dirname(__FILE__) . '/../../../tools/helpers/bookstore/BookstoreTestBase.php';

/**
 * Tests for GeocodableBehavior class
 *
 * @author     William Durand <william.durand1@gmail.com>
 * @package    generator.behavior
 */
class GeocodableBehaviorTest extends BookstoreTestBase
{
    public function testGetDistanceFromInKilometers()
    {
        $geo1 = new GeolocatedTable();
        $geo1->setLatitude(45.795463);
        $geo1->setLongitude(3.163237);

        $geo2 = new GeolocatedTable();
        $geo2->setLatitude(45.77722154971201);
        $geo2->setLongitude(3.086986541748047);

        $dist = round($geo1->getDistanceTo($geo2), 2);
        $this->assertEquals(6.25, $dist);
    }

    public function testGetDistanceFromInMiles()
    {
        $geo1 = new GeolocatedTable();
        $geo1->setLatitude(45.795463);
        $geo1->setLongitude(3.163237);

        $geo2 = new GeolocatedTable();
        $geo2->setLatitude(45.77722154971201);
        $geo2->setLongitude(3.086986541748047);

        $dist = round($geo1->getDistanceTo($geo2, GeolocatedTablePeer::MILES_UNIT), 2);
        $this->assertEquals(3.88, $dist);
    }

    public function testGetDistanceFromInNauticalMiles()
    {
        $geo1 = new GeolocatedTable();
        $geo1->setLatitude(45.795463);
        $geo1->setLongitude(3.163237);

        $geo2 = new GeolocatedTable();
        $geo2->setLatitude(45.77722154971201);
        $geo2->setLongitude(3.086986541748047);

        $dist = round($geo1->getDistanceTo($geo2, GeolocatedTablePeer::NAUTICAL_MILES_UNIT), 2);
        $this->assertEquals(3.37, $dist);
    }

    public function testSetCoordinates()
    {
        $geo = new GeolocatedTable();
        $geo->setCoordinates(1, 2);
        $this->assertEquals(1, $geo->getLatitude());
        $this->assertEquals(2, $geo->getLongitude());
    }

    public function testFilterByDistanceFromReturnsNoObjects()
    {
        GeolocatedTablePeer::doDeleteAll();

        $geo1 = new GeolocatedTable();
        $geo1->setName('Aulnat');
        $geo1->setCoordinates(45.795463, 3.163237);
        $geo1->save();

        $objects = GeolocatedTableQuery::create()
            ->filterByDistanceFrom(45.77722154971201, 3.086986541748047, 5)
            ->find()
            ;
        $this->assertEquals(0, count($objects));
    }

    public function testFilterByDistanceFromReturnsObjects()
    {
        GeolocatedTablePeer::doDeleteAll();

        $geo1 = new GeolocatedTable();
        $geo1->setName('Aulnat');
        $geo1->setCoordinates(45.795463, 3.163237);
        $geo1->save();

        $objects = GeolocatedTableQuery::create()
            ->filterByDistanceFrom(45.77722154971201, 3.086986541748047, 10)
            ->find()
            ;
        $this->assertEquals(1, count($objects));
    }

    public function testGeolocateIp()
    {
        $geo = new GeolocatedTable();
        $geo->setIpAddress('81.22.10.60');
        $geo->save();

        $this->assertEquals(55.75, $geo->getLatitude());
        $this->assertEquals(37.583, $geo->getLongitude());
    }

    public function testGeolocateAddress()
    {
        $geo = new GeolocatedTable();
        $geo->setStreet('8 rue du Nord');
        $geo->setCity('Clermont-Ferrand');
        $geo->setCountry('France');
        $geo->save();

        $this->assertEquals(45.776665, $geo->getLatitude());
        $this->assertEquals(3.07723, $geo->getLongitude());
    }

    public function testGeolocateAddressWithUpdate()
    {
        $geo = new GeolocatedTable();
        $geo->setStreet('8 rue du Nord');
        $geo->setCity('Clermont-Ferrand');
        $geo->setCountry('France');
        $geo->save();

        $this->assertEquals(45.776665, $geo->getLatitude());
        $this->assertEquals(3.07723, $geo->getLongitude());

        $geo->setStreet('1 avenue Léon Maniez');
        $geo->save();

        $this->assertEquals(45.776665, $geo->getLatitude());
        $this->assertEquals(3.07723, $geo->getLongitude());
    }
}
