GeocodableBehavior
==================

The **GeocodableBehavior** helps you build geo-aware applications. It automatically geocodes your models when they are saved, giving you the ability to search by location and calculate distances between records.

This behavior uses two external APIs:

* [IpInfoDB](http://www.ipinfodb.com/) for the IP-Based geocoding part;
* [Yahoo! PlaceFinder](http://developer.yahoo.com/geo/placefinder/) for the Address-Based geocoding part.


Installation
------------

Pick the `GeocodableBehavior.php` file is `src/`, put it somewhere,
then add the following line to your `propel.ini` or `build.properties` configuration file:

``` ini
propel.behavior.geocodable.class = path.to.GeocodableBehavior
```

Usage
-----

Just add the following XML tag in your `schema.xml` file:

``` xml
<behavior name="geocodable" />
```

Basically, the behavior will add:

* two new columns to your model (`latitude` and `longitude`);
* three new methods to the _ActiveRecord_ API (`getDistanceTo()`, `getCoordinates()`, and `setCoordinates()`);
* a new method to the _ActiveQuery_ API (`filterByDistanceFrom()`).


### ActiveRecord API ###

`getDistanceTo()` returns the distance between the current object and a given one.
The method takes two arguments:

* a geocoded object;
* a measure unit (`KILOMETERS_UNIT`, `MILES_UNIT`, or `NAUTICAL_MILES_UNIT` defined in the `Peer` class of the geocoded model).

`getCoordinates()`, `setCoordinates()` allows to quickly set/get latitude and longitude values.


### ActiveQuery API ###

** /!\ Not safe, not fully working at the moment /!\ **

`filterByDistanceFrom()` takes five arguments:

* a latitude value;
* a longitude value;
* a distance value;
* a comparison sign (`Criteria::LESS_THAN` is the default value);
* a measure unit (`KILOMETERS_UNIT`, `MILES_UNIT`, or `NAUTICAL_MILES_UNIT` defined in the `Peer` class of the geocoded model).

It will add a filter by distance on your current query and returns itself for fluid interface.


Automatic Geocoding
-------------------

At this step, you have to fill in the two columns (`latitude` and `longitude`) yourself.
It's not really useful, right ?

Automatic geocoding to the rescue! There are two automatic ways to get geocoded information:

* using IP addresses;
* using street addresses.

Note: You can use both at the same time.


### IP-Based Geocoding ###

To enable the IP-Based geocoding, add the following configuration in your `schema.xml` file:

``` xml
<behavior name="geocodable">
    <parameter name="geocode_ip" value="true" />
    <parameter name="ipinfodb_api_key" value="<IPINFODB_API_KEY>" />
</behavior>
```

This configuration will add a new column to your model: `ip_address`. You can change the name of this column using the following parameter:

``` xml
<parameter name="ip_column" value="ip" />
```

The behavior will now use the `ip_address` value to populate the `latitude` and `longitude` columns thanks to the IpInfoDB API.


### Address-Based Geocoding ###

To enable the Address-Based geocoding, add the following configuration:

``` xml
<behavior name="geocodable">
    <parameter name="geocode_address" value="true" />
    <parameter name="yahoo_api_key" value="<YAHOO_API_KEY>" />
</behavior>
```

Basically, the behavior looks for attributes called street, locality, region, postal_code, and country. It tries to make a complete address with them. As usual, you can tweak this parameter to add your own list of attributes that represents a complete street address:

``` xml
<parameter name="address_columns" value="street,locality,region,postal_code,country" />
```

These parameters will be concatened and separated by a comma to make a street address. This address will be used to get `latitude` and `longitude` values.

Now, each time you save your object, the two columns `latitude` and `longitude` are populated thanks to the Yahoo! PlaceFinder API.


Parameters
----------

``` xml
<behavior name="geocodable">
    <parameter name="latitude_column" value="latitude" />
    <parameter name="longitude_column" value="longitude" />

    <!-- IP-Based Geocoding -->
    <parameter name="geocode_ip" value="false" />
    <parameter name="ip_column" value="ip_address" />
    <parameter name="ipinfodb_api_key" value="<IPINFODB_API_KEY>" />

    <!-- Address-Based Geocoding -->
    <parameter name="geocode_address" value="false" />
    <parameter name="address_columns" value="street,locality,region,postal_code,country" />
    <parameter name="yahoo_api_key" value="<YAHOO_API_KEY>" />
</behavior>
```


Credits
-------

* William Durand


Links
-----

* [https://github.com/collectiveidea/acts_as_geocodable](https://github.com/collectiveidea/acts_as_geocodable)
