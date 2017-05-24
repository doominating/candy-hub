<?php

namespace GetCandy\Api\Services;

use GetCandy\Api\Contracts\ServiceContract;
use GetCandy\Api\Models\Attribute;
use GetCandy\Exceptions\DuplicateValueException;

class AttributeService extends BaseService
{
    /**
     * @var AttributeGroup
     */
    protected $model;

    public function __construct()
    {
        $this->model = new Attribute();
    }

    /**
     * Creates a resource from the given data
     *
     * @param  array  $data
     *
     * @return GetCandy\Api\Models\Attribute
     */
    public function create(array $data)
    {
        $attributeGroup = app('api')->attributeGroups()->getByHashedId($data['group_id']);

        if (!$attributeGroup) {
            abort(400, 'Attribute group with ID "' . $data['group_id'] . '" doesn\'t exist');
        }

        $result = $attributeGroup->attributes()->create([
            'name' => $data['name'],
            'handle' => str_slug($data['name']),
            'position' => $this->getNewPositionForGroup($attributeGroup->id),
            'variant' => !empty($data['variant']) ? $data['variant'] : null,
            'searchable' => !empty($data['searchable']) ? $data['searchable'] : null,
            'filterable' => !empty($data['filterable']) ? $data['filterable'] : null
        ]);

        return $result;
    }

    protected function getNewPositionForGroup($groupId)
    {
        $attribute = $this->getLastItem($groupId);
        return $attribute->position + 1;
    }

    /**
     * Updates the positions of attributes
     * @param  array  $data
     *
     * @throws Symfony\Component\HttpKernel\Exception\HttpException
     * @throws GetCandy\Api\Exceptions\DuplicateValueException
     *
     * @return Boolean
     */
    public function updateAttributePositions(array $data)
    {

        // Test for duplicates without hitting the database
        if (count($data['attributes']) > count(array_unique($data['attributes']))) {
            throw new DuplicateValueException(trans('getcandy_api::validation.attributes.groups.dupe_position'), 1);
        }

        $parsedAttributes = [];

        foreach ($data['attributes'] as $attributeId => $position) {
            $decodedId = (new Attribute)->decodeId($attributeId);
            if (!$decodedId) {
                abort(422, trans('getcandy_api::validation.attributes.groups.invalid_id', ['id' => $attributeId]));
            }
            $parsedAttributes[$decodedId] = $position;
        }

        $attributes = $this->getByHashedIds(array_keys($data['attributes']));

        foreach ($attributes as $attribute) {
            $attribute->position = $parsedAttributes[$attribute->id];
            $attribute->save();
        }

        return true;
    }

    /**
     * Updates a resource from the given data
     *
     * @param  string $id
     * @param  array  $data
     *
     * @throws Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return GetCandy\Api\Models\Attribute
     */
    public function update($hashedId, array $data)
    {
        $attribute = $this->getByHashedId($hashedId);

        if (!$attribute) {
            abort(404);
        }

        $attribute->fill($data);
        $attribute->save();

        return $attribute;
    }

    /**
     * Deletes a resource by its given hashed ID
     *
     * @param  string $id
     *
     * @throws Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return Boolean
     */
    public function delete($id)
    {
        $attribute = $this->getByHashedId($id);


        if (!$attribute) {
            abort(404);
        }

        return $attribute->delete();
    }

    public function getAttributesForGroup($groupId)
    {
        return $this->model->where('group_id', '=', $groupId)->get();
    }

    public function getLastItem($groupId)
    {
        return $this->model->orderBy('position', 'desc')->where('group_id', '=', $groupId)->first();
    }

    public function nameExistsInGroup($value, $groupId, $attributeId = null)
    {
        $result = $this->model->where('name', '=', $value)
                        ->where('group_id', '=', $groupId);

        if ($attributeId) {
            $result->where('id', '!=', $attributeId);
        }

        return !$result->exists();
    }
}
