<?php
/**
 * ZF2-ExtendedFramework
 *
 * @link      https://github.com/brian978/ZF2-ExtendedFramework
 * @copyright Copyright (c) 2013
 * @license   Creative Commons Attribution-ShareAlike 3.0
 */

namespace Acamar\Model\Mapper;

use Acamar\Collection\AbstractCollection;

class MapCollection extends AbstractCollection
{
    /**
     * @param string $name
     * @return array|null
     */
    public function findMap($name)
    {
        if (isset($this->collection[$name])) {
            return $this->collection[$name];
        }

        return null;
    }

    /**
     * @param array $map
     * @return array
     */
    public function flip($map)
    {
        $flipped = array();

        if ($map !== null) {
            foreach ($map['specs'] as $fromField => $toField) {
                if (is_string($toField) || is_numeric($toField)) {
                    $flipped[$toField] = $fromField;
                }
            }
        }

        return $flipped;
    }
}
