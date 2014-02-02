<?php

/**
 * This file is part of the GeocodableBehavior package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class GeocodableBehaviorQueryBuilderModifier
{
    /**
     * @var GeocodableBehavior
     */
    private $behavior;

    public function __construct(Behavior $behavior)
    {
        $this->behavior = $behavior;
    }

    /**
     * {@inheritdoc}
     */
    public function queryMethods($builder)
    {
        $script  = '';
        $script .= $this->addWithDistance($builder);
        $script .= $this->addFilterByDistanceFrom($builder);
        $script .= $this->addFilterNear($builder);

        return $script;
    }

    public function addWithDistance($builder)
    {
        $builder->declareClass('Criteria', 'PDO');

        $queryClassName = $builder->getStubQueryBuilder()->getClassname();
        $peerClassName  = $builder->getStubPeerBuilder()->getClassname();

        return $this->behavior->renderTemplate('queryWithDistance', array(
            'queryClassName'            => $queryClassName,
            'defaultUnit'               => $this->getDefaultUnit($builder),
            'peerClassName'             => $peerClassName,
            'longitudeColumnConstant'   => $this->behavior->getColumnConstant('longitude_column', $builder),
            'latitudeColumnConstant'    => $this->behavior->getColumnConstant('latitude_column', $builder),
        ));
    }

    public function addFilterByDistanceFrom($builder)
    {
        $builder->declareClass('Criteria', 'PDO');

        $queryClassName = $builder->getStubQueryBuilder()->getClassname();
        $peerClassName  = $builder->getStubPeerBuilder()->getClassname();

        return $this->behavior->renderTemplate('queryFilterByDistanceFrom', array(
            'queryClassName'            => $queryClassName,
            'defaultUnit'               => $this->getDefaultUnit($builder),
            'peerClassName'             => $peerClassName,
            'longitudeColumnConstant'   => $this->behavior->getColumnConstant('longitude_column', $builder),
            'latitudeColumnConstant'    => $this->behavior->getColumnConstant('latitude_column', $builder),
        ));
    }

    public function addFilterNear($builder)
    {
        $builder->declareClassFromBuilder($builder->getStubObjectBuilder());

        $objectClassName = $builder->getStubObjectBuilder()->getClassname();
        $variableName    = strtolower($objectClassName);
        $queryClassName  = $builder->getStubQueryBuilder()->getClassname();

        return $this->behavior->renderTemplate('queryFilterNear', array(
            'objectClassName'       => $objectClassName,
            'variableName'          => $variableName,
            'queryClassName'        => $queryClassName,
            'latitudeColumnGetter'  => $this->behavior->getColumnGetter('latitude_column'),
            'longitudeColumnGetter' => $this->behavior->getColumnGetter('longitude_column'),
            'defaultUnit'           => $this->getDefaultUnit($builder),
        ));
    }

    protected function getDefaultUnit($builder)
    {
        return sprintf('%s::KILOMETERS_UNIT', $builder->getStubPeerBuilder()->getClassname());
    }
}
