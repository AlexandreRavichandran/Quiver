<?php

namespace App\Service;

use Vich\UploaderBundle\Naming\NamerInterface;
use Vich\UploaderBundle\Mapping\PropertyMapping;

class ImageNamer implements NamerInterface
{

    /**
     * Creates a name for the file being uploaded.
     *
     * @param  object          $obj     The object the upload is attached to.
     * @param  PropertyMapping $mapping The mapping to use to manipulate the given object.
     * @return string          The file name.
     */
    public function name($object, PropertyMapping $mapping): string
    {
        if (is_a($object, 'User')) {
            $objectName = $object->getPseudonym();
        } else {
            $objectName = $object->getName();
        }

        $name = 'picture_' . $objectName . '.' . $object->getImageFile()->getExtension();
        return $name;
    }
}
