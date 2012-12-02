GeocodableBehavior
==================

[![Build Status](https://secure.travis-ci.org/willdurand/GeocodableBehavior.png)](http://travis-ci.org/willdurand/GeocodableBehavior)

The **GeocodableBehavior** helps you build geo-aware applications. It automatically
geocodes your models when they are saved, giving you the ability to search by
location and calculate distances between records.

This behavior uses [Geocoder](https://github.com/willdurand/Geocoder), the
Geocoder PHP 5.3 library and requires [Propel](http://github.com/propelorm/Propel)
1.6.4-dev and above.

Installation
------------

Cherry-pick the `GeocodableBehavior.php` file is `src/`, put it somewhere,
then add the following line to your `propel.ini` or `build.properties`
configuration file:

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
* four new methods to the _ActiveRecord_ API (`getDistanceTo()`, `isGeocoded()`,
`getCoordinates()`, and `setCoordinates()`);
* two new methods to the _ActiveQuery_ API (`filterByDistanceFrom()`,
`filterNear()`).


### ActiveRecord API ###

`getDistanceTo()` returns the distance between the current object and a given one.
The method takes two arguments:

* a geocoded object;
* a measure unit (`KILOMETERS_UNIT`, `MILES_UNIT`, or `NAUTICAL_MILES_UNIT`
defined in the `Peer` class of the geocoded model).

`isGeocoded()` returns a boolean value whether the object has been geocoded,
or not.

`getCoordinates()`, `setCoordinates()` allows to quickly set/get latitude,
and longitude values.


### ActiveQuery API ###

`filterByDistanceFrom()` takes five arguments:

* a latitude value;
* a longitude value;
* a distance value;
* a measure unit (`KILOMETERS_UNIT`, `MILES_UNIT`, or `NAUTICAL_MILES_UNIT`
defined in the `Peer` class of the geocoded model);
* a comparison sign (`Criteria::LESS_THAN` is the default value).

It will add a filter by distance on your current query and returns itself for
fluid interface.

`filterNear` takes three arguments:

* a model object;
* a distance value;
* a measure unit (`KILOMETERS_UNIT`, `MILES_UNIT`, or `NAUTICAL_MILES_UNIT`
defined in the `Peer` class of the geocoded model).


Automatic Geocoding
-------------------

At this step, you have to fill in the two columns (`latitude` and `longitude`)
yourself. It's not really useful, right ?

Automatic geocoding to the rescue! There are two automatic ways to get geocoded
information:

* using IP addresses;
* using street addresses.

It provides a `geocode()` method that autoupdate the location values.
To prevent autofill when modified, just set `auto_update` attribute to false.

This method returns a [`ResultInterface`](https://github.com/willdurand/Geocoder/blob/master/src/Geocoder/Result/ResultInterface.php)
object, so you can override this method to fill in more fields depending on your
model:

``` php
<?php

class MyObject extends BaseMyObject
{
    // ...

    /**
     * {@inheritdoc}
     */
    public function geocode()
    {
        if (null !== $result = parent::geocode()) {
            if ($city = $result->getCity()) {
                $this->setCity($city);
            }
        }

        return $result;
    }
}
```

Note: You can use both at the same time.

### IP-Based Geocoding ###

To enable the IP-Based geocoding, add the following configuration in your
`schema.xml` file:

``` xml
<behavior name="geocodable">
    <parameter name="geocode_ip" value="true" />
    <parameter name="geocoder_api_key" value="<API_KEY>" />
    <parameter name="geocoder_api_key_provider" value="<API_KEY_PROVIDER>" />
</behavior>
```

The `geocoder_api_key_provider` can be either a static method returning the api
key. A class method in the format `class()->method()` or
`class()->method()->subMethod()`, or a class implementing `getGoogleMapsKey`
which must return the key.

By default, the default Geocoder `provider` is `YahooProvider` so you'll need to
fill in an API key.

If you want to use another provider, you'll need to set a new parameter:

``` xml
<parameter name="geocoder_provider" value="\Geocoder\Provider\HostIpProvider" />
```

Read the **Geocoder** documentation to know more about providers.

This configuration will add a new column to your model: `ip_address`.
You can change the name of this column using the following parameter:

``` xml
<parameter name="ip_column" value="ip" />
```

The behavior will now use the `ip_address` value to populate the `latitude`,and
`longitude` columns thanks to **Geocoder**.


### Address-Based Geocoding ###

To enable the Address-Based geocoding, add the following configuration:

``` xml
<behavior name="geocodable">
    <parameter name="geocode_address" value="true" />
    <parameter name="geocoder_api_key" value="<API_KEY>" />
</behavior>
```

By default, the default Geocoder `provider` is `YahooProvider` so you'll need to
fill in an API key but keep in mind it's an optional parameter depending on the
provider you choose.

If you want to use another provider, you'll need to set a new parameter:

``` xml
<parameter name="geocoder_provider" value="\Geocoder\Provider\GoogleMapsProvider" />
```

Read the **Geocoder** documentation to know more about providers.

Basically, the behavior looks for attributes called street, locality, region,
fpostal_code, and country. It tries to make a complete address with them.
As usual, you can tweak this parameter to add your own list of attributes that
represents a complete street address:

``` xml
<parameter name="address_columns" value="street,locality,region,postal_code,country" />
```

These parameters will be concatenated and separated by a comma to make a street
address. This address will be used to get `latitude` and `longitude` values.

Now, each time you save your object, the two columns `latitude`, and `longitude`
are populated thanks to **Geocoder**.


HTTP Adapters
-------------

**Geocoder** provides HTTP adapters which can be configured through the behavior.
By default, this behavior uses the `CurlHttpAdapter`.

If you want to use another `adapter`, you'll need to use the following parameter:

``` xml
<parameter name="geocoder_adapter" value="\Geocoder\HttpAdapter\BuzzHttpAdapter" />
```

Read the **Geocoder** documentation to know more about adapters.


Parameters
----------

```xml
<behavior name="geocodable">
    <parameter name="auto_update" value="true" />

    <parameter name="latitude_column" value="latitude" />
    <parameter name="longitude_column" value="longitude" />

    <!-- IP-Based Geocoding -->
    <parameter name="geocode_ip" value="false" />
    <parameter name="ip_column" value="ip_address" />

    <!-- Address-Based Geocoding -->
    <parameter name="geocode_address" value="false" />
    <parameter name="address_columns" value="street,locality,region,postal_code,country" />

    <!-- Geocoder -->
    <parameter name="geocoder_provider" value="\Geocoder\Provider\YahooProvider" />
    <parameter name="geocoder_adapter" value="\Geocoder\HttpAdapter\CurlHttpAdapter" />
    <parameter name="geocoder_api_key" value="false" />
    <parameter name="geocoder_api_key_provider" value="false" />
</behavior>
```

This is the default configuration.


Credits
-------

William Durand <william.durand1@gmail.com>


Links
-----

[https://github.com/collectiveidea/acts_as_geocodable](https://github.com/collectiveidea/acts_as_geocodable)
