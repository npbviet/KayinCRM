<?php

namespace Webkul\Task\Repositories;

use Illuminate\Container\Container;
use Webkul\Attribute\Repositories\AttributeRepository;
use Webkul\Attribute\Repositories\AttributeValueRepository;
use Webkul\Core\Eloquent\Repository;
use Webkul\Task\Models\Task;

class TaskRepository extends Repository
{
    /**
     * Searchable fields.
     *
     * @var array
     */
    protected $fieldSearchable = [
        'title',
        'description',
        'status',
        'priority',
        'user_id',
        'assigned_to',
        'due_date',
        'created_at',
    ];

    /**
     * Create a new repository instance.
     *
     * @return void
     */
    public function __construct(
        protected AttributeRepository $attributeRepository,
        protected AttributeValueRepository $attributeValueRepository,
        Container $container
    ) {
        parent::__construct($container);
    }

    /**
     * Specify model class name.
     *
     * @return mixed
     */
    public function model()
    {
        return Task::class;
    }

    /**
     * Create.
     *
     * @return \Webkul\Task\Contracts\Task
     */
    public function create(array $data)
    {
        $task = parent::create($data);

        $this->attributeValueRepository->save(array_merge($data, [
            'entity_id'   => $task->id,
            'entity_type' => 'tasks',
        ]));

        return $task;
    }

    /**
     * Update.
     *
     * @return \Webkul\Task\Contracts\Task
     */
    public function update(array $data, $id, $attributes = [])
    {
        $task = parent::update($data, $id);

        if (! empty($attributes)) {
            $conditions = ['entity_type' => 'tasks'];

            $attributes = $this->attributeRepository->where($conditions)
                ->whereIn('code', $attributes)
                ->get();

            $this->attributeValueRepository->save(array_merge($data, [
                'entity_id'   => $task->id,
                'entity_type' => 'tasks',
            ]), $attributes);
        } else {
            $this->attributeValueRepository->save(array_merge($data, [
                'entity_id'   => $task->id,
                'entity_type' => 'tasks',
            ]));
        }

        return $task;
    }
}
